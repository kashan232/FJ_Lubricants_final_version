<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Sale;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Salesman;
use App\Models\LocalSale;
use App\Models\Distributor;
use Illuminate\Http\Request;
use App\Models\CustomerRecovery;
use App\Models\DistributorProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function Distributor_Ledger_Record()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $user = Auth::user();

        // ✅ ADMIN → ALL DISTRIBUTORS
        if ($user->usertype === 'admin') {

            $Distributors = Distributor::where('admin_or_user_id', $user->id)->get();
        }
        // ✅ DISTRIBUTOR → ONLY HIS OWN RECORD
        else {

            $Distributors = Distributor::where('id', $user->user_id)->get();
        }

        return view('admin_panel.reports.distributor_ledger_record', [
            'Distributors' => $Distributors,
        ]);
    }


    public function fetchDistributorLedger(Request $request)
    {
        $distributorId = $request->input('distributor_id');
        $startDate = $request->input('start_date') . ' 00:00:00';
        $endDate   = $request->input('end_date') . ' 23:59:59';

        // ---- Get Ledger Base Opening ----
        $ledger = DB::table('distributor_ledgers')
            ->where('distributor_id', $distributorId)
            ->select('opening_balance')
            ->first();

        $baseOpening = $ledger->opening_balance ?? 0;

        // ---- Transactions Before Start Date ----
        $previousSales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->where('Date', '<', $startDate)
            ->sum('net_amount');

        $previousRecoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->where('date', '<', $startDate)
            ->sum('amount_paid');

        $previousReturns = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->where('created_at', '<', $startDate)
            ->sum('total_return_amount');

        $openingBalance = $baseOpening + $previousSales - ($previousRecoveries + $previousReturns);

        // ---- Current Period Transactions ----
        $recoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'salesman', 'date', 'remarks')
            ->get();

        // SALES (we assume sales table has the columns; if not, similar checks can be added)
        $sales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->select('invoice_number', 'Date', 'Booker', 'Saleman', 'grand_total', 'discount_value', 'scheme_value', 'net_amount', 'item', 'rate', 'carton_qty', 'pcs', 'liter')
            ->get()
            ->map(function ($sale) {
                $itemsArray   = json_decode($sale->item, true) ?? [];
                $ratesArray   = json_decode($sale->rate, true) ?? [];
                $cartonsArray = json_decode($sale->carton_qty, true) ?? [];
                $pcsArray     = json_decode($sale->pcs, true) ?? [];
                $litersArray  = json_decode($sale->liter, true) ?? [];

                $itemsStr   = is_array($itemsArray) ? implode(', ', $itemsArray) : ($itemsArray ?: '-');
                $ratesStr   = is_array($ratesArray) ? implode(', ', $ratesArray) : ($ratesArray ?: '-');
                $cartonsStr = is_array($cartonsArray) ? implode(', ', $cartonsArray) : ($cartonsArray ?: '-');
                $pcsStr     = is_array($pcsArray) ? implode(', ', $pcsArray) : ($pcsArray ?: '-');
                $litersStr  = is_array($litersArray) ? implode(', ', $litersArray) : ($litersArray ?: '-');

                return [
                    'invoice_number' => $sale->invoice_number,
                    'Date'           => $sale->Date,
                    'Booker'         => $sale->Booker,
                    'Saleman'        => $sale->Saleman,
                    'grand_total'    => $sale->grand_total,
                    'net_amount'     => $sale->net_amount,
                    'items'          => $itemsStr,
                    'rates'          => $ratesStr,
                    'cartons'        => $cartonsStr,
                    'pcs'            => $pcsStr,
                    'liters'         => $litersStr,
                ];
            });

        // ---- SALE RETURNS: build select list only with existing columns ----
        $srSelect = ['invoice_number', 'created_at', 'total_return_amount'];
        $maybeCols = ['item', 'carton_qty', 'pcs', 'liter', 'rate'];

        foreach ($maybeCols as $col) {
            if (Schema::hasColumn('sale_returns', $col)) {
                $srSelect[] = $col;
            }
        }

        $saleReturnsRaw = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select($srSelect)
            ->get();

        // map returns with safe defaults for missing fields
        $saleReturns = $saleReturnsRaw->map(function ($r) use ($maybeCols) {
            // decode if exists, else fallback to '-'
            $mapVal = function ($obj, $col) {
                if (!isset($obj->$col)) return '-';
                $val = $obj->$col;
                $arr = json_decode($val, true);
                if (is_array($arr)) return implode(', ', $arr);
                return ($val === null || $val === '') ? '-' : $val;
            };

            return [
                'invoice_number' => $r->invoice_number,
                'created_at'     => $r->created_at,
                'total_return_amount' => $r->total_return_amount,
                'items'          => $mapVal($r, 'item'),
                'cartons'        => $mapVal($r, 'carton_qty'),
                'pcs'            => $mapVal($r, 'pcs'),
                'liters'         => $mapVal($r, 'liter'),
                'rates'          => $mapVal($r, 'rate'),
            ];
        });

        $closingBalance = $openingBalance
            + $sales->sum('net_amount')
            - ($recoveries->sum('amount_paid') + collect($saleReturns)->sum('total_return_amount'));

        $transfers = DB::table('distributor_balance_transfers')
            ->where('to_distributor', $distributorId)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->select('id', 'from_distributor', 'to_distributor', 'amount', 'transfer_date', 'reason')
            ->get();

        return response()->json([
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'recoveries'      => $recoveries,
            'sales'           => $sales,
            'sale_returns'    => $saleReturns,
            'transfers'       => $transfers,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
        ]);
    }

    public function DistributorLedgerPdf(Request $request)
    {
        $distributorId = $request->distributor_id;
        $startDate = $request->start_date . ' 00:00:00';
        $endDate   = $request->end_date . ' 23:59:59';

        $distributor = Distributor::findOrFail($distributorId);

        /* ================= OPENING BALANCE ================= */

        $ledger = DB::table('distributor_ledgers')
            ->where('distributor_id', $distributorId)
            ->first();

        $baseOpening = $ledger->opening_balance ?? 0;

        $previousSales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->where('Date', '<', $startDate)
            ->sum('net_amount');

        $previousRecoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->where('date', '<', $startDate)
            ->sum('amount_paid');

        $previousReturns = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->where('created_at', '<', $startDate)
            ->sum('total_return_amount');

        $openingBalance = $baseOpening + $previousSales - ($previousRecoveries + $previousReturns);

        /* ================= FETCH DATA ================= */

        $sales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->get();

        $recoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $saleReturns = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $transfers = DB::table('distributor_balance_transfers')
            ->where('to_distributor', $distributorId)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();

        /* ================= MERGE + SORT (JS SAME) ================= */

        $entries = collect();

        foreach ($sales as $s) {
            $entries->push([
                'type' => 'sale',
                'date' => $s->Date,
                'invoice' => $s->invoice_number,
                'booker' => $s->Booker,
                'items' => json_decode($s->item, true) ?? [],
                'cartons' => json_decode($s->carton_qty, true) ?? [],
                'pcs' => json_decode($s->pcs, true) ?? [],
                'liters' => json_decode($s->liter, true) ?? [],
                'rates' => json_decode($s->rate, true) ?? [],
            ]);
        }

        foreach ($recoveries as $r) {
            $entries->push([
                'type' => 'recovery',
                'date' => $r->date,
                'amount' => $r->amount_paid,
                'remarks' => $r->remarks ?? $r->salesman,
            ]);
        }

        foreach ($saleReturns as $sr) {
            $entries->push([
                'type' => 'sale_return',
                'date' => $sr->created_at,
                'invoice' => $sr->invoice_number,
                'amount' => $sr->total_return_amount,
                'items' => json_decode($sr->item ?? '[]', true),
                'cartons' => json_decode($sr->carton_qty ?? '[]', true),
                'pcs' => json_decode($sr->pcs ?? '[]', true),
                'liters' => json_decode($sr->liter ?? '[]', true),
                'rates' => json_decode($sr->rate ?? '[]', true),
            ]);
        }

        foreach ($transfers as $t) {
            $entries->push([
                'type' => 'transfer',
                'date' => $t->transfer_date,
                'amount' => $t->amount,
                'from' => $t->from_distributor,
                'reason' => $t->reason,
            ]);
        }

        $entries = $entries->sortBy('date')->values();

        /* ================= SEND TO PDF ================= */

        $pdf = Pdf::loadView('admin_panel.reports.pdfs.distributor_ledger', [
            'distributor' => $distributor,
            'entries' => $entries,
            'openingBalance' => $openingBalance,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Distributor-Detailed-Ledger.pdf');
    }



    public function vendor_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            return view('admin_panel.reports.vendor_ledger_record', [
                'Vendors' => $Vendors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchVendorLedger(Request $request)
    {
        $vendorId  = $request->input('Vendor_id');
        $startDate = $request->input('start_date') . ' 00:00:00';
        $endDate   = $request->input('end_date') . ' 23:59:59';

        // ---- Get Base Opening from Vendor Ledger ----
        $ledger = DB::table('vendor_ledgers')
            ->where('vendor_id', $vendorId)
            ->select('opening_balance')
            ->first();

        $baseOpening = $ledger->opening_balance ?? 0;

        // ---- Transactions Before Start Date ----
        $previousPurchases = DB::table('purchases')
            ->where('party_name', $vendorId)
            ->where('purchase_date', '<', $startDate)
            ->sum('grand_total');

        $previousPayments = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->where('payment_date', '<', $startDate)
            ->sum('amount_paid');

        $previousReturnsRaw = DB::table('purchase_returns')
            ->where('party_name', $vendorId)
            ->where('return_date', '<', $startDate)
            ->get();

        $previousReturns = 0;
        foreach ($previousReturnsRaw as $return) {
            $amountArray = json_decode($return->return_amount, true);
            $previousReturns += collect($amountArray)->sum();
        }

        $previousBuilties = DB::table('vendor_builties')
            ->where('vendor_id', $vendorId)
            ->where('date', '<', $startDate)
            ->sum('amount');

        // ✅ Opening Balance = BaseOpening + Purchases + Builties − (Payments + Returns)
        $openingBalance = $baseOpening + ($previousPurchases + $previousBuilties) - ($previousPayments + $previousReturns);

        // ---- Current Period Transactions ----
        $recoveries = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'description', 'payment_date')
            ->get();

        $purchases = DB::table('purchases')
            ->where('party_name', $vendorId)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->select('id', 'invoice_number', 'purchase_date', 'grand_total', 'item', 'rate', 'carton_qty', 'pcs', 'liter')
            ->get()
            ->map(function ($purchase) {
                // decode JSON columns - if your DB stores as JSON strings/arrays
                $itemsArray   = json_decode($purchase->item, true) ?? [];
                $ratesArray   = json_decode($purchase->rate, true) ?? [];
                $cartonsArray = json_decode($purchase->carton_qty, true) ?? [];
                $pcsArray     = json_decode($purchase->pcs, true) ?? [];
                $litersArray  = json_decode($purchase->liter, true) ?? [];

                // make readable comma-separated strings
                $itemsStr   = is_array($itemsArray) ? implode(', ', $itemsArray) : ($itemsArray ?: '');
                $ratesStr   = is_array($ratesArray) ? implode(', ', $ratesArray) : ($ratesArray ?: '');
                $cartonsStr = is_array($cartonsArray) ? implode(', ', $cartonsArray) : ($cartonsArray ?: '');
                $pcsStr     = is_array($pcsArray) ? implode(', ', $pcsArray) : ($pcsArray ?: '');
                $litersStr  = is_array($litersArray) ? implode(', ', $litersArray) : ($litersArray ?: '');

                return [
                    'invoice_number' => $purchase->invoice_number,
                    'date'           => $purchase->purchase_date,
                    'grand_total'    => $purchase->grand_total,
                    'net_amount'     => $purchase->grand_total,
                    'items'          => $itemsStr,
                    'rates'          => $ratesStr,
                    'cartons'        => $cartonsStr,
                    'pcs'            => $pcsStr,
                    'liters'         => $litersStr,
                ];
            });


        $returnsRaw = DB::table('purchase_returns')
            ->where('party_name', $vendorId)
            ->whereBetween('return_date', [$startDate, $endDate])
            ->get();

        $returns = [];
        $currentReturns = 0;
        foreach ($returnsRaw as $return) {
            $amountArray = json_decode($return->return_amount, true);
            $amountSum   = collect($amountArray)->sum();
            $currentReturns += $amountSum;

            $returns[] = [
                'id'            => $return->id,
                'invoice_number' => $return->invoice_number,
                'date'          => $return->return_date,
                'net_amount'    => $amountSum,
            ];
        }

        $builties = DB::table('vendor_builties')
            ->where('vendor_id', $vendorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'date', 'amount', 'description')
            ->get();

        // ✅ Closing Balance = Opening + Purchases + Builties − (Payments + Returns)
        $closingBalance = $openingBalance
            + $purchases->sum('grand_total')
            + $builties->sum('amount')
            - ($recoveries->sum('amount_paid') + $currentReturns);

        return response()->json([
            'opening_balance'  => $openingBalance,
            'closing_balance'  => $closingBalance,
            'purchases'        => $purchases,
            'recoveries'       => $recoveries,
            'returns'          => $returns,
            'builties'         => $builties,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
        ]);
    }

    public function vendorLedgerPdf(Request $request)
    {
        $vendorId  = $request->vendor_id;
        $startDate = $request->start_date . ' 00:00:00';
        $endDate   = $request->end_date . ' 23:59:59';

        $vendor = Vendor::findOrFail($vendorId);

        /* ===== OPENING BALANCE (same as screen) ===== */
        $ledger = DB::table('vendor_ledgers')
            ->where('vendor_id', $vendorId)
            ->first();

        $baseOpening = $ledger->opening_balance ?? 0;

        $previousPurchases = DB::table('purchases')
            ->where('party_name', $vendorId)
            ->where('purchase_date', '<', $startDate)
            ->sum('grand_total');

        $previousPayments = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->where('payment_date', '<', $startDate)
            ->sum('amount_paid');

        $previousReturns = DB::table('purchase_returns')
            ->where('party_name', $vendorId)
            ->where('return_date', '<', $startDate)
            ->get()
            ->sum(fn($r) => collect(json_decode($r->return_amount, true))->sum());

        $previousBuilties = DB::table('vendor_builties')
            ->where('vendor_id', $vendorId)
            ->where('date', '<', $startDate)
            ->sum('amount');

        $openingBalance = $baseOpening
            + ($previousPurchases + $previousBuilties)
            - ($previousPayments + $previousReturns);

        /* ===== MERGE ENTRIES (same as ledger JS) ===== */
        $entries = collect();

        foreach (DB::table('purchases')->where('party_name', $vendorId)->whereBetween('purchase_date', [$startDate, $endDate])->get() as $p) {
            $entries->push([
                'type' => 'purchase',
                'date' => $p->purchase_date,
                'invoice' => $p->invoice_number,
                'items' => json_decode($p->item, true) ?? [],
                'cartons' => json_decode($p->carton_qty, true) ?? [],
                'pcs' => json_decode($p->pcs, true) ?? [],
                'liters' => json_decode($p->liter, true) ?? [],
                'rates' => json_decode($p->rate, true) ?? [],
            ]);
        }

        foreach (DB::table('vendor_payments')->where('vendor_id', $vendorId)->whereBetween('payment_date', [$startDate, $endDate])->get() as $r) {
            $entries->push([
                'type' => 'recovery',
                'date' => $r->payment_date,
                'amount' => $r->amount_paid,
                'remarks' => $r->description,
            ]);
        }

        foreach (DB::table('purchase_returns')->where('party_name', $vendorId)->whereBetween('return_date', [$startDate, $endDate])->get() as $r) {
            $entries->push([
                'type' => 'return',
                'date' => $r->return_date,
                'invoice' => $r->invoice_number,
                'amount' => collect(json_decode($r->return_amount, true))->sum(),
            ]);
        }

        foreach (DB::table('vendor_builties')->where('vendor_id', $vendorId)->whereBetween('date', [$startDate, $endDate])->get() as $b) {
            $entries->push([
                'type' => 'builty',
                'date' => $b->date,
                'amount' => $b->amount,
                'description' => $b->description,
            ]);
        }

        $entries = $entries->sortBy('date')->values();

        $pdf = Pdf::loadView('admin_panel.reports.pdfs.vendor_ledger', [
            'vendor' => $vendor,
            'entries' => $entries,
            'openingBalance' => $openingBalance,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Vendor-Ledger.pdf');
    }



    public function Customer_Ledger_Record()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();

        // Step 1: Determine owner/admin ID
        if ($authUser->usertype === 'salesman') {
            $salesman = Salesman::where('name', $authUser->name)->first();

            if (!$salesman) {
                return redirect()->back()->with('error', 'Salesman not found.');
            }

            $ownerId = $salesman->admin_or_user_id;
        } else {
            // If admin/owner
            $ownerId = $authUser->id;
        }

        // Step 2: Fetch all customers under this owner
        $Customers = Customer::where('admin_or_user_id', $ownerId)
            ->get();

        return view('admin_panel.reports.customer_ledger_record', compact('Customers'));
    }



    public function fetchCustomerLedger(Request $request)
    {
        $CustomerId = $request->input('Customer_id');
        $startDate  = $request->input('start_date') . ' 00:00:00';
        $endDate    = $request->input('end_date') . ' 23:59:59';

        // ---- Ledger Opening Balance from DB (first time user set) ----
        $ledger = DB::table('customer_ledgers')
            ->where('customer_id', $CustomerId)
            ->select('opening_balance')
            ->first();

        $baseOpening = $ledger->opening_balance ?? 0;

        // ---- Transactions Before Start Date ----
        $previousSales = DB::table('local_sales')
            ->where('customer_id', $CustomerId)
            ->where('Date', '<', $startDate)
            ->sum('net_amount');

        $previousRecoveries = DB::table('customer_recoveries')
            ->where('customer_ledger_id', $CustomerId)
            ->where('date', '<', $startDate)
            ->sum('amount_paid');

        $previousReturns = DB::table('sale_returns')
            ->where('sale_type', 'customer')
            ->where('party_id', $CustomerId)
            ->where('created_at', '<', $startDate)
            ->sum('total_return_amount');

        // ✅ Opening Balance
        $openingBalance = $baseOpening + $previousSales - ($previousRecoveries + $previousReturns);

        // ---- Current Period Transactions ----
        $recoveries = DB::table('customer_recoveries')
            ->where('customer_ledger_id', $CustomerId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'salesman', 'date', 'remarks')
            ->get();

        // local_sales with items / cartons / pcs / liters / rate decoding
        $localSales = DB::table('local_sales')
            ->where('customer_id', $CustomerId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->select('invoice_number', 'Date', 'customer_shopname', 'grand_total', 'discount_value', 'scheme_value', 'net_amount', 'Saleman', 'item', 'rate', 'carton_qty', 'pcs', 'liter')
            ->get()
            ->map(function ($sale) {
                $itemsArray   = json_decode($sale->item, true) ?? [];
                $ratesArray   = json_decode($sale->rate, true) ?? [];
                $cartonsArray = json_decode($sale->carton_qty, true) ?? [];
                $pcsArray     = json_decode($sale->pcs, true) ?? [];
                $litersArray  = json_decode($sale->liter, true) ?? [];

                $itemsStr   = is_array($itemsArray) ? implode(', ', $itemsArray) : ($itemsArray ?: '-');
                $ratesStr   = is_array($ratesArray) ? implode(', ', $ratesArray) : ($ratesArray ?: '-');
                $cartonsStr = is_array($cartonsArray) ? implode(', ', $cartonsArray) : ($cartonsArray ?: '-');
                $pcsStr     = is_array($pcsArray) ? implode(', ', $pcsArray) : ($pcsArray ?: '-');
                $litersStr  = is_array($litersArray) ? implode(', ', $litersArray) : ($litersArray ?: '-');

                return [
                    'invoice_number' => $sale->invoice_number,
                    'Date'           => $sale->Date,
                    'customer_shopname' => $sale->customer_shopname,
                    'Saleman'        => $sale->Saleman,
                    'grand_total'    => $sale->grand_total,
                    'net_amount'     => $sale->net_amount,
                    'items'          => $itemsStr,
                    'rates'          => $ratesStr,
                    'cartons'        => $cartonsStr,
                    'pcs'            => $pcsStr,
                    'liters'         => $litersStr,
                ];
            });

        // SALE RETURNS: select only columns that exist to avoid SQL errors
        $srSelect = ['invoice_number', 'created_at', 'total_return_amount'];
        $maybeCols = ['item', 'carton_qty', 'pcs', 'liter', 'rate'];

        foreach ($maybeCols as $col) {
            if (Schema::hasColumn('sale_returns', $col)) {
                $srSelect[] = $col;
            }
        }

        $saleReturnsRaw = DB::table('sale_returns')
            ->where('sale_type', 'customer')
            ->where('party_id', $CustomerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select($srSelect)
            ->get();

        // map returns safely (missing cols => '-')
        $saleReturns = $saleReturnsRaw->map(function ($r) {
            $mapVal = function ($obj, $col) {
                if (!isset($obj->$col)) return '-';
                $val = $obj->$col;
                $arr = json_decode($val, true);
                if (is_array($arr)) return implode(', ', $arr);
                return ($val === null || $val === '') ? '-' : $val;
            };

            return [
                'invoice_number' => $r->invoice_number,
                'created_at'     => $r->created_at,
                'total_return_amount' => $r->total_return_amount,
                'items'          => $mapVal($r, 'item'),
                'cartons'        => $mapVal($r, 'carton_qty'),
                'pcs'            => $mapVal($r, 'pcs'),
                'liters'         => $mapVal($r, 'liter'),
                'rates'          => $mapVal($r, 'rate'),
            ];
        });

        // ✅ Closing Balance
        $closingBalance = $openingBalance
            + $localSales->sum('net_amount')
            - ($recoveries->sum('amount_paid') + collect($saleReturns)->sum('total_return_amount'));

        return response()->json([
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'recoveries'      => $recoveries,
            'local_sales'     => $localSales,
            'sale_returns'    => $saleReturns,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
        ]);
    }

    public function customerLedgerPdf(Request $request)
    {
        $CustomerId = $request->customer_id;
        $startDate  = $request->start_date . ' 00:00:00';
        $endDate    = $request->end_date . ' 23:59:59';

        $customer = Customer::findOrFail($CustomerId);

        /* ================= OPENING BALANCE (SAME AS SCREEN) ================= */
        $ledger = DB::table('customer_ledgers')
            ->where('customer_id', $CustomerId)
            ->select('opening_balance')
            ->first();

        $baseOpening = $ledger->opening_balance ?? 0;

        $previousSales = DB::table('local_sales')
            ->where('customer_id', $CustomerId)
            ->where('Date', '<', $startDate)
            ->sum('net_amount');

        $previousRecoveries = DB::table('customer_recoveries')
            ->where('customer_ledger_id', $CustomerId)
            ->where('date', '<', $startDate)
            ->sum('amount_paid');

        $previousReturns = DB::table('sale_returns')
            ->where('sale_type', 'customer')
            ->where('party_id', $CustomerId)
            ->where('created_at', '<', $startDate)
            ->sum('total_return_amount');

        $openingBalance = $baseOpening
            + $previousSales
            - ($previousRecoveries + $previousReturns);

        /* ================= ENTRIES (SAME FLOW AS AJAX) ================= */
        $entries = collect();

        // SALES (invoice based – net_amount ONLY)
        $localSales = DB::table('local_sales')
            ->where('customer_id', $CustomerId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->select(
                'invoice_number',
                'Date',
                'Saleman',
                'net_amount',
                'item',
                'carton_qty',
                'pcs',
                'liter',
                'rate'
            )
            ->get();

        foreach ($localSales as $s) {
            $entries->push([
                'type'    => 'sale',
                'date'    => $s->Date,
                'invoice' => $s->invoice_number,
                'saleman' => $s->Saleman,
                'amount'  => $s->net_amount, // ⭐ EXACT SAME AS SCREEN
                'items'   => json_decode($s->item, true) ?? [],
                'cartons' => json_decode($s->carton_qty, true) ?? [],
                'pcs'     => json_decode($s->pcs, true) ?? [],
                'liters'  => json_decode($s->liter, true) ?? [],
                'rates'   => json_decode($s->rate, true) ?? [],
            ]);
        }

        // RECOVERIES
        $recoveries = DB::table('customer_recoveries')
            ->where('customer_ledger_id', $CustomerId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('amount_paid', 'date', 'remarks', 'salesman')
            ->get();

        foreach ($recoveries as $r) {
            $entries->push([
                'type'   => 'recovery',
                'date'   => $r->date,
                'amount' => $r->amount_paid,
                'desc'   => $r->remarks ?? $r->salesman,
            ]);
        }

        $srSelect = ['invoice_number', 'created_at', 'total_return_amount'];
        $maybeCols = ['item', 'carton_qty', 'pcs', 'liter', 'rate'];

        foreach ($maybeCols as $col) {
            if (Schema::hasColumn('sale_returns', $col)) {
                $srSelect[] = $col;
            }
        }

        $saleReturnsRaw = DB::table('sale_returns')
            ->where('sale_type', 'customer')
            ->where('party_id', $CustomerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select($srSelect)
            ->get();

        $saleReturns = $saleReturnsRaw->map(function ($r) {
            $mapVal = function ($obj, $col) {
                if (!isset($obj->$col)) return [];
                $arr = json_decode($obj->$col, true);
                return is_array($arr) ? $arr : [];
            };

            return [
                'type'    => 'sale_return',
                'date'    => $r->created_at,
                'invoice' => $r->invoice_number,
                'amount'  => $r->total_return_amount,
                'items'   => $mapVal($r, 'item'),
                'cartons' => $mapVal($r, 'carton_qty'),
                'pcs'     => $mapVal($r, 'pcs'),
                'liters'  => $mapVal($r, 'liter'),
                'rates'   => $mapVal($r, 'rate'),
            ];
        });

        foreach ($saleReturns as $sr) {
            $entries->push($sr);
        }

        // SAME SORTING AS JS
        $entries = $entries->sortBy('date')->values();

        /* ================= PDF ================= */
        $pdf = Pdf::loadView('admin_panel.reports.pdfs.customer_ledger', [
            'customer'       => $customer,
            'entries'        => $entries,
            'openingBalance' => $openingBalance,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Customer-Ledger.pdf');
    }


    public function stock_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $categories = Category::where('admin_or_user_id', $userId)->get();
            return view('admin_panel.reports.stock_Record', [
                'categories' => $categories,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getItems($subcategory)
    {
        $items = Product::where('sub_category', $subcategory)->select('item_code', 'item_name')->get();
        return response()->json($items);
    }

    public function getItemDetails(Request $request)
    {
        $user = Auth::user();

        if ($user->usertype === 'admin') {
            return $this->adminStockReport($request);
        }

        if ($user->usertype === 'distributor') {
            return $this->distributorStockReport(
                $request,
                $user->user_id,   // distributor_id (19)
                $user->id         // user_id (41)
            );
        }

        return response()->json([]);
    }

    private function adminStockReport(Request $request)
    {
        $query = Product::query();

        if ($request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->subcategory !== 'all') {
            $query->where('sub_category', $request->subcategory);
        }

        if ($request->itemCode !== 'all') {
            $query->where('item_code', $request->itemCode);
        }

        $items = $query->get();

        foreach ($items as $item) {

            /* ================= PURCHASE ================= */
            $totalPurchasedQty = 0;
            $purchaseData = Purchase::whereJsonContains('item', $item->item_name)->get();

            foreach ($purchaseData as $purchase) {
                $itemsArr  = json_decode($purchase->item, true);
                $qtyArr    = json_decode($purchase->carton_qty, true);

                foreach ($itemsArr ?? [] as $i => $name) {
                    if ($name === $item->item_name) {
                        $totalPurchasedQty += (int)($qtyArr[$i] ?? 0);
                    }
                }
            }

            /* ================= DISTRIBUTOR SALE ================= */
            $totalDistributorSoldQty = 0;
            $salesData = Sale::whereJsonContains('item', $item->item_name)->get();

            foreach ($salesData as $sale) {
                $itemsArr = json_decode($sale->item, true);
                $qtyArr   = json_decode($sale->carton_qty, true);

                foreach ($itemsArr ?? [] as $i => $name) {
                    if ($name === $item->item_name) {
                        $totalDistributorSoldQty += (int)($qtyArr[$i] ?? 0);
                    }
                }
            }

            /* ================= LOCAL SALE ================= */
            $totalLocalSoldQty = 0;
            $localSales = LocalSale::whereJsonContains('item', $item->item_name)->get();

            foreach ($localSales as $sale) {
                $itemsArr = json_decode($sale->item, true);
                $qtyArr   = json_decode($sale->carton_qty, true);

                foreach ($itemsArr ?? [] as $i => $name) {
                    if ($name === $item->item_name) {
                        $totalLocalSoldQty += (int)($qtyArr[$i] ?? 0);
                    }
                }
            }

            /* ================= PURCHASE RETURN ================= */
            $totalPurchaseReturnQty = 0;
            $purchaseReturns = DB::table('purchase_returns')
                ->whereJsonContains('item', $item->item_name)
                ->get();

            foreach ($purchaseReturns as $ret) {
                $itemsArr = json_decode($ret->item, true);
                $qtyArr   = json_decode($ret->return_qty, true);

                foreach ($itemsArr ?? [] as $i => $name) {
                    if ($name === $item->item_name) {
                        $totalPurchaseReturnQty += (int)($qtyArr[$i] ?? 0);
                    }
                }
            }

            /* ================= DISTRIBUTOR RETURN ================= */
            $totalDistributorReturnQty = 0;
            $distReturns = DB::table('sale_returns')
                ->where('sale_type', 'distributor')
                ->whereJsonContains('item_names', $item->item_name)
                ->get();

            foreach ($distReturns as $ret) {
                $itemsArr = json_decode($ret->item_names, true);
                $qtyArr   = json_decode($ret->carton_qty, true);

                foreach ($itemsArr ?? [] as $i => $name) {
                    if ($name === $item->item_name) {
                        $totalDistributorReturnQty += (int)($qtyArr[$i] ?? 0);
                    }
                }
            }

            /* ================= LOCAL RETURN ================= */
            $totalLocalReturnQty = 0;
            $localReturns = DB::table('sale_returns')
                ->where('sale_type', 'customer')
                ->whereJsonContains('item_names', $item->item_name)
                ->get();

            foreach ($localReturns as $ret) {
                $itemsArr = json_decode($ret->item_names, true);
                $qtyArr   = json_decode($ret->carton_qty, true);

                foreach ($itemsArr ?? [] as $i => $name) {
                    if ($name === $item->item_name) {
                        $totalLocalReturnQty += (int)($qtyArr[$i] ?? 0);
                    }
                }
            }

            /* ================= ASSIGN ================= */
            $item->total_purchased           = $totalPurchasedQty;
            $item->total_purchase_return     = $totalPurchaseReturnQty;
            $item->total_distributor_sold    = $totalDistributorSoldQty;
            $item->total_distributor_return  = $totalDistributorReturnQty;
            $item->total_local_sold          = $totalLocalSoldQty;
            $item->total_local_return        = $totalLocalReturnQty;
        }

        return response()->json($items);
    }

    // private function distributorStockReport(Request $request, $distributorId, $userId)
    // {
    //     // ✅ SINGLE SOURCE OF TRUTH
    //     $products = DistributorProduct::where('distributor_id', $distributorId)->get();

    //     foreach ($products as $product) {

    //         /* ================= PURCHASED (ADMIN SALE) =================
    //        🔥 DIRECT FROM distributor_products
    //     */
    //         $purchasedCarton = (int) $product->carton_quantity;
    //         $purchasedPcs    = (int) ($product->pcs ?? 0);

    //         /* ================= DISTRIBUTOR → LOCAL SALE ================= */
    //         $soldCarton = 0;
    //         $soldPcs    = 0;

    //         $localSales = LocalSale::where('admin_or_user_id', $userId)
    //             ->where('identify', 'distributor')
    //             ->whereJsonContains('item', $product->item)
    //             ->get();

    //         foreach ($localSales as $sale) {
    //             $itemsArr = json_decode($sale->item, true);
    //             $ctnArr   = json_decode($sale->carton_qty, true);
    //             $pcsArr   = json_decode($sale->pcs, true);

    //             foreach ($itemsArr ?? [] as $i => $name) {
    //                 if ($name === $product->item) {
    //                     $soldCarton += (int)($ctnArr[$i] ?? 0);
    //                     $soldPcs    += (int)($pcsArr[$i] ?? 0);
    //                 }
    //             }
    //         }
    //         /* ================= RETURNS ================= */
    //         $returnCarton = 0;
    //         $returnPcs    = 0;

    //         $returns = DB::table('sale_returns')
    //             ->where('sale_type', 'customer')
    //             ->where('admin_or_user_id', $userId)
    //             ->whereJsonContains('item_names', $product->item)
    //             ->get();

    //         foreach ($returns as $ret) {
    //             $itemsArr = json_decode($ret->item_names, true);
    //             $ctnArr   = json_decode($ret->carton_qty, true);
    //             $pcsArr   = json_decode($ret->pcs, true);

    //             foreach ($itemsArr ?? [] as $i => $name) {
    //                 if ($name === $product->item) {
    //                     $returnCarton += (int)($ctnArr[$i] ?? 0);
    //                     $returnPcs    += (int)($pcsArr[$i] ?? 0);
    //                 }
    //             }
    //         }

    //         /* ================= FINAL BALANCE ================= */
    //         $balanceCarton = $product->initial_stock
    //             + $purchasedCarton
    //             - $soldCarton
    //             + $returnCarton;

    //         $balancePcs = $purchasedPcs - $soldPcs + $returnPcs;

    //         /* ================= NORMALIZE FOR BLADE ================= */
    //         $product->item_code     = $product->code;
    //         $product->item_name     = $product->item;
    //         $product->size          = $product->size;
    //         $product->pcs_in_carton = $product->pcs_carton;

    //         // Purchased
    //         $product->purchased_carton = $purchasedCarton;
    //         $product->purchased_pcs    = $purchasedPcs;

    //         // Sale
    //         $product->sold_carton = $soldCarton;
    //         $product->sold_pcs    = $soldPcs;

    //         // Balance
    //         $product->balance_carton = $balanceCarton;
    //         $product->balance_pcs    = $balancePcs;

    //         // Price
    //         $product->retail_price = $product->price;

    //         // Value
    //         $product->stock_value = $balanceCarton * $product->price;
    //     }

    //     return response()->json($products);
    // }
    private function distributorStockReport(Request $request, $distributorId, $userId)
    {
        // 🔹 Distributor ka current stock (snapshot)
        $products = DistributorProduct::where('distributor_id', $distributorId)->get();

        foreach ($products as $product) {

            /* ================= PURCHASED (ADMIN SALE SNAPSHOT) ================= */
            // Distributor ke paas jitna stock hai, wahi purchased maana jayega
            $purchasedCarton = (int) $product->carton_quantity;
            $purchasedPcs    = (int) ($product->pcs ?? 0);

            /* ================= DISTRIBUTOR → LOCAL SALE ================= */
            $soldCarton = 0;
            $soldPcs    = 0;

            $localSales = LocalSale::where('admin_or_user_id', $userId)
                ->where('identify', 'distributor')
                ->whereJsonContains('item', $product->item)
                ->get();

            foreach ($localSales as $sale) {
                $itemsArr = json_decode($sale->item, true);
                $ctnArr   = json_decode($sale->carton_qty, true);
                $pcsArr   = json_decode($sale->pcs, true);

                foreach ($itemsArr ?? [] as $i => $name) {
                    if ($name === $product->item) {
                        $soldCarton += (int) ($ctnArr[$i] ?? 0);
                        $soldPcs    += (int) ($pcsArr[$i] ?? 0);
                    }
                }
            }

            /* ================= RETURNS (INFO ONLY) ================= */
            $returnCarton = 0;
            $returnPcs    = 0;

            $returns = DB::table('sale_returns')
                ->where('sale_type', 'customer')
                ->where('admin_or_user_id', $userId)
                ->whereJsonContains('item_names', $product->item)
                ->get();

            foreach ($returns as $ret) {
                $itemsArr = json_decode($ret->item_names, true);
                $ctnArr   = json_decode($ret->carton_qty, true);
                $pcsArr   = json_decode($ret->pcs, true);

                foreach ($itemsArr ?? [] as $i => $name) {
                    if ($name === $product->item) {
                        $returnCarton += (int) ($ctnArr[$i] ?? 0);
                        $returnPcs    += (int) ($pcsArr[$i] ?? 0);
                    }
                }
            }

            /* ================= BALANCE (SINGLE SOURCE OF TRUTH) ================= */
            $balanceCarton = (int) $product->carton_quantity;
            $balancePcs    = (int) ($product->pcs ?? 0);

            /* ================= BALANCE LITER ================= */
            $sizeText = strtolower(trim($product->size));
            $sizeLiter = 0;

            if (str_contains($sizeText, 'ml')) {
                $sizeLiter = ((float) preg_replace('/[^0-9.]/', '', $sizeText)) / 1000;
            } elseif (str_contains($sizeText, 'liter') || str_contains($sizeText, 'l')) {
                $sizeLiter = (float) preg_replace('/[^0-9.]/', '', $sizeText);
            }

            $balanceLiter = $balanceCarton * $product->pcs_carton * $sizeLiter;
            /* ================= NORMALIZE FOR BLADE ================= */
            $product->item_code     = $product->code;
            $product->item_name     = $product->item;
            $product->size          = $product->size;
            $product->pcs_in_carton = $product->pcs_carton;

            // Purchased
            $product->purchased_carton = $purchasedCarton;
            $product->purchased_pcs    = $purchasedPcs;

            // Sale (display only)
            $product->sold_carton   = $soldCarton;
            $product->sold_pcs      = $soldPcs;

            // Return (display only)
            $product->return_carton = $returnCarton;
            $product->return_pcs    = $returnPcs;

            // Balance (snapshot)
            $product->balance_carton = $balanceCarton;
            $product->balance_pcs    = $balancePcs;
            $product->balance_liter  = round($balanceLiter, 2);

            // Price & Value
            $product->retail_price = $product->price;
            $product->stock_value  = $balanceCarton * $product->price;
        }

        return response()->json($products);
    }




    public function date_wise_recovery_report()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();

        // Step 1: Determine owner/admin/distributor ID
        if ($authUser->usertype === 'salesman') {
            $salesman = Salesman::where('name', $authUser->name)->first();

            if (!$salesman) {
                return redirect()->back()->with('error', 'Salesman not found.');
            }

            $ownerId = $salesman->admin_or_user_id;

            // Only the logged-in salesman visible
            $Salesmans = collect([$salesman]);
        } else {
            $ownerId = $authUser->id;

            // All salesmen created by this owner
            $Salesmans = Salesman::where('admin_or_user_id', $ownerId)
                ->where('designation', 'Saleman')
                ->get();
        }

        // Step 2: Fetch all customers under this owner
        $Customers = Customer::where('admin_or_user_id', $ownerId)
            ->get(['id', 'customer_name', 'shop_name', 'area']);

        return view('admin_panel.reports.date_wise_recovery_report', compact('Customers', 'Salesmans'));
    }


    public function getRecoveryReport(Request $request)
    {
        $salesman  = $request->salesman;
        $type      = $request->type;
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $user = Auth::user();
        $recoveries = [];

        /* =====================================================
       🔹 ADMIN
    ===================================================== */
        if ($user->usertype === 'admin') {

            /* ---------- DISTRIBUTOR RECOVERIES ---------- */
            if ($type === 'all' || $type === 'distributor') {

                $query = Recovery::whereBetween('date', [$startDate, $endDate]);

                if ($salesman !== 'All') {
                    $query->where('salesman', $salesman);
                }

                $rows = $query->get();

                foreach ($rows as $recovery) {
                    $distributor = Distributor::find($recovery->distributor_ledger_id);

                    $recoveries[] = [
                        'date'        => $recovery->date,
                        'shop_name'   => '-',
                        'party_name'  => $distributor->Customer ?? 'N/A',
                        'area'        => $distributor->Area ?? 'N/A',
                        'remarks'     => $recovery->remarks,
                        'amount_paid' => number_format($recovery->amount_paid),
                        'salesman'    => $recovery->salesman ?? '-'
                    ];
                }
            }

            /* ---------- ADMIN CUSTOMER RECOVERIES ---------- */
            if ($type === 'all' || $type === 'customer') {

                $query = CustomerRecovery::where('admin_or_user_id', $user->id)
                    ->whereBetween('date', [$startDate, $endDate]);

                if ($salesman !== 'All') {
                    $query->where('salesman', $salesman);
                }

                $rows = $query->get();

                $customers = Customer::where('identify', 'admin')
                    ->where('admin_or_user_id', $user->id)
                    ->get()
                    ->keyBy('id');

                foreach ($rows as $recovery) {
                    $c = $customers[$recovery->customer_ledger_id] ?? null;

                    $recoveries[] = [
                        'date'        => $recovery->date,
                        'shop_name'   => $c->shop_name ?? 'N/A',
                        'party_name'  => $c->customer_name ?? 'N/A',
                        'area'        => $c->area ?? 'N/A',
                        'remarks'     => $recovery->remarks,
                        'amount_paid' => number_format($recovery->amount_paid),
                        'salesman'    => $recovery->salesman ?? '-'
                    ];
                }
            }
        }

        /* =====================================================
       🔹 DISTRIBUTOR
    ===================================================== */
        if ($user->usertype === 'distributor') {

            // ❌ distributor recoveries nahi

            if ($type === 'all' || $type === 'customer') {

                $query = CustomerRecovery::where('admin_or_user_id', $user->id)
                    ->whereBetween('date', [$startDate, $endDate]);

                if ($salesman !== 'All') {
                    $query->where('salesman', $salesman);
                }

                $rows = $query->get();

                $customers = Customer::where('identify', 'distributor')
                    ->where('admin_or_user_id', $user->id)
                    ->get()
                    ->keyBy('id');

                foreach ($rows as $recovery) {
                    $c = $customers[$recovery->customer_ledger_id] ?? null;

                    $recoveries[] = [
                        'date'        => $recovery->date,
                        'shop_name'   => $c->shop_name ?? 'N/A',
                        'party_name'  => $c->customer_name ?? 'N/A',
                        'area'        => $c->area ?? 'N/A',
                        'remarks'     => $recovery->remarks,
                        'amount_paid' => number_format($recovery->amount_paid),
                        'salesman'    => $recovery->salesman ?? '-'
                    ];
                }
            }
        }

        return response()->json($recoveries);
    }



    public function date_wise_purcahse_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            return view('admin_panel.reports.date_wise_purcahse_report', [
                'Customers' => $Customers,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetch_purchase_report(Request $request)
    {
        $user = Auth::user();
        $start = $request->start_date;
        $end   = $request->end_date;

        $report = [];
        $totals = [
            'carton' => 0,
            'pcs' => 0,
            'liter' => 0,
            'net_amount' => 0,
        ];

        /* ================= ADMIN ================= */
        if ($user->usertype === 'admin') {

            $purchases = Purchase::where('admin_or_user_id', $user->id)
                ->whereBetween('purchase_date', [$start, $end])
                ->get();

            foreach ($purchases as $key => $purchase) {

                $items        = json_decode($purchase->item ?? '[]');
                $pcs_carton   = json_decode($purchase->pcs_carton ?? '[]');
                $carton_qty  = json_decode($purchase->carton_qty ?? '[]');
                $pcs          = json_decode($purchase->pcs ?? '[]');
                $liter        = json_decode($purchase->liter ?? '[]');
                $amounts      = json_decode($purchase->amount ?? '[]');

                foreach ($items as $i => $item) {

                    $netAmount = floatval($amounts[$i] ?? 0);

                    $report[] = [
                        'code' => count($report) + 1,
                        'date' => \Carbon\Carbon::parse($purchase->purchase_date)->format('d-M-Y'),
                        'item' => $item,
                        'carton_packing' => $pcs_carton[$i] ?? 0,
                        'carton_qty' => $carton_qty[$i] ?? 0,
                        'pcs' => $pcs[$i] ?? 0,
                        'liter' => $liter[$i] ?? 0,
                        'net_amount' => $netAmount,
                    ];

                    $totals['carton'] += floatval($carton_qty[$i] ?? 0);
                    $totals['pcs'] += floatval($pcs[$i] ?? 0);
                    $totals['liter'] += floatval($liter[$i] ?? 0);
                    $totals['net_amount'] += $netAmount;
                }
            }
        }

        /* ================= DISTRIBUTOR ================= */ else {

            $sales = Sale::where('distributor_id', $user->user_id)
                ->whereBetween('Date', [$start, $end])
                ->get();
            foreach ($sales as $sale) {

                $items        = json_decode($sale->item ?? '[]');
                $pcs_carton   = json_decode($sale->pcs_carton ?? '[]');
                $carton_qty  = json_decode($sale->carton_qty ?? '[]');
                $pcs          = json_decode($sale->pcs ?? '[]');
                $liter        = json_decode($sale->liter ?? '[]');
                $amounts      = json_decode($sale->amount ?? '[]');

                foreach ($items as $i => $item) {

                    $netAmount = floatval($amounts[$i] ?? 0);

                    $report[] = [
                        'code' => count($report) + 1,
                        'date' => \Carbon\Carbon::parse($sale->Date)->format('d-M-Y'),
                        'item' => $item,
                        'carton_packing' => $pcs_carton[$i] ?? 0,
                        'carton_qty' => $carton_qty[$i] ?? 0,
                        'pcs' => $pcs[$i] ?? 0,
                        'liter' => $liter[$i] ?? 0,
                        'net_amount' => $netAmount,
                    ];

                    $totals['carton'] += floatval($carton_qty[$i] ?? 0);
                    $totals['pcs'] += floatval($pcs[$i] ?? 0);
                    $totals['liter'] += floatval($liter[$i] ?? 0);
                    $totals['net_amount'] += $netAmount;
                }
            }
        }

        return response()->json([
            'report' => $report,
            'totals' => $totals,
        ]);
    }




    public function vendor_wise_purcahse_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            return view('admin_panel.reports.vendor_wise_purcahse_report', [
                'Vendors' => $Vendors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchVendorPurchaseReport(Request $request)
    {
        $userId = Auth::id();
        $start = $request->start_date;
        $end = $request->end_date;
        $vendorId = $request->vendor_id;

        if (!$vendorId) {
            return response()->json(['error' => 'Vendor is required'], 422);
        }

        $purchases = Purchase::where('admin_or_user_id', $userId)
            ->where('party_name', $vendorId) // ✅ match by vendor ID in party_name
            ->whereBetween('purchase_date', [$start, $end])
            ->get();

        $report = [];
        $totals = [
            'carton' => 0,
            'pcs' => 0,
            'liter' => 0,
            'net_amount' => 0,
        ];

        foreach ($purchases as $key => $purchase) {
            $items = json_decode($purchase->item ?? '[]');
            $pcs_carton = json_decode($purchase->pcs_carton ?? '[]');
            $carton_qty = json_decode($purchase->carton_qty ?? '[]');
            $pcs = json_decode($purchase->pcs ?? '[]');
            $liter = json_decode($purchase->liter ?? '[]');
            $amounts = json_decode($purchase->amount ?? '[]'); // ✅ new line

            foreach ($items as $i => $item) {
                $netAmount = floatval($amounts[$i] ?? 0);

                $report[] = [
                    'inv_no' => $purchase->invoice_number ?? 'N/A',
                    'date' => \Carbon\Carbon::parse($purchase->purchase_date)->format('d-M-Y'),
                    'item' => $item ?? 'N/A',
                    'carton_packing' => $pcs_carton[$i] ?? 0,
                    'carton_qty' => $carton_qty[$i] ?? 0,
                    'pcs' => $pcs[$i] ?? 0,
                    'liter' => $liter[$i] ?? 0,
                    'net_amount' => $netAmount,
                ];

                $totals['carton'] += floatval($carton_qty[$i] ?? 0);
                $totals['pcs'] += floatval($pcs[$i] ?? 0);
                $totals['liter'] += floatval($liter[$i] ?? 0);
                $totals['net_amount'] += $netAmount;
            }
        }

        return response()->json([
            'report' => $report,
            'totals' => $totals,
        ]);
    }

    public function Area_wise_Customer_payments()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();

        /* ================= OWNER DETECTION ================= */
        if ($authUser->usertype === 'salesman') {

            $salesman = Salesman::where('name', $authUser->name)->first();

            if (!$salesman) {
                return redirect()->back()->with('error', 'Salesman not found.');
            }

            $ownerId = $salesman->admin_or_user_id;

            // sirf logged-in salesman
            $Salesmans = collect([$salesman]);
        } else {

            // admin / distributor
            $ownerId = $authUser->id;

            $Salesmans = Salesman::where('admin_or_user_id', $ownerId)
                ->where('designation', 'Saleman')
                ->get();
        }

        /* ================= CUSTOMERS ================= */
        $Customers = Customer::where('admin_or_user_id', $ownerId)
            ->get(['id', 'customer_name', 'shop_name', 'area']);

        /* ================= CITIES (FIXED) ================= */
        $cities = City::where('admin_or_user_id', $ownerId)->get();

        return view('admin_panel.reports.Area_wise_Customer_payments', [
            'Customers' => $Customers,
            'cities'    => $cities,
            'Salesmans' => $Salesmans,
        ]);
    }



    public function fetchReceivableReport(Request $request)
    {
        $cities     = (array) ($request->city ?? []);
        $areas      = (array) ($request->area ?? []);
        $startDate  = $request->start_date;
        $endDate    = $request->end_date;
        $salesman   = $request->salesman ?? 'All';

        $authUser   = Auth::user();
        $isAdmin        = $authUser->usertype === 'admin';
        $isDistributor  = $authUser->usertype === 'distributor';
        $ownerId        = $authUser->id;

        /* ===========================
       CITY FILTER
    ============================*/
        if (in_array('All', $cities)) {

            if ($isAdmin) {
                $cities = DB::table('customers')
                    ->where('admin_or_user_id', $ownerId)
                    ->pluck('city')
                    ->merge(
                        DB::table('distributors')
                            ->where('admin_or_user_id', $ownerId)
                            ->pluck('City')
                    )
                    ->unique()
                    ->values()
                    ->toArray();
            } else {
                // distributor → only own customers cities
                $cities = DB::table('customers')
                    ->where('admin_or_user_id', $ownerId)
                    ->where('identify', 'distributor')
                    ->pluck('city')
                    ->unique()
                    ->values()
                    ->toArray();
            }
        }

        $result = [];

        foreach ($cities as $city) {

            /* ===========================
           CUSTOMERS (ADMIN + DISTRIBUTOR)
        ============================*/
            $customersQuery = DB::table('customers')
                ->where('city', $city)
                ->where('admin_or_user_id', $ownerId);

            if ($isDistributor) {
                $customersQuery->where('identify', 'distributor');
            }

            if (!empty($areas) && !in_array('All', $areas)) {
                $customersQuery->whereIn('area', $areas);
            }

            $customers = $customersQuery->get();
            $customerData = [];

            foreach ($customers as $customer) {

                if ($salesman !== 'All') {
                    $hasSales = DB::table('local_sales')
                        ->where('customer_id', $customer->id)
                        ->where('Saleman', $salesman)
                        ->exists();

                    if (!$hasSales) continue;
                }

                $ledger = DB::table('customer_ledgers')
                    ->where('customer_id', $customer->id)
                    ->first();

                $openingBalance = $ledger->opening_balance ?? 0;

                $totalSales = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn($q) => $q->where('Saleman', $salesman))
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'customer')
                    ->where('party_id', $customer->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('customer_recoveries')
                    ->where('customer_ledger_id', $customer->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn($q) => $q->where('salesman', $salesman))
                    ->sum('amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                if (round($balance, 2) != 0 || $totalSales || $totalReturns || $totalRecoveries) {
                    $customerData[] = [
                        'type'      => 'customer',
                        'pcode'     => $customer->id,
                        'name'      => $customer->customer_name,
                        'shopname'  => $customer->shop_name,
                        'address'   => $customer->area,
                        'contact'   => $customer->phone_number,
                        'balance'   => round($balance, 2),
                    ];
                }
            }

            /* ===========================
           DISTRIBUTORS (ADMIN ONLY)
        ============================*/
            $distributorData = [];

            if ($isAdmin) {

                $distributorQuery = DB::table('distributors')
                    ->where('City', $city)
                    ->where('admin_or_user_id', $ownerId);

                if (!empty($areas) && !in_array('All', $areas)) {
                    $distributorQuery->whereIn('Area', $areas);
                }

                if ($salesman !== 'All') {
                    $distributorQuery->whereExists(function ($q) use ($salesman) {
                        $q->select(DB::raw(1))
                            ->from('sales')
                            ->whereColumn('sales.distributor_id', 'distributors.id')
                            ->where('sales.Saleman', $salesman);
                    });
                }

                $distributors = $distributorQuery->get();

                foreach ($distributors as $distributor) {

                    $ledger = DB::table('distributor_ledgers')
                        ->where('distributor_id', $distributor->id)
                        ->first();

                    $openingBalance = $ledger->opening_balance ?? 0;

                    $totalSales = DB::table('sales')
                        ->where('distributor_id', $distributor->id)
                        ->whereBetween('Date', [$startDate, $endDate])
                        ->when($salesman !== 'All', fn($q) => $q->where('Saleman', $salesman))
                        ->sum('grand_total');

                    $totalReturns = DB::table('sale_returns')
                        ->where('sale_type', 'distributor')
                        ->where('party_id', $distributor->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->sum('total_return_amount');

                    $totalRecoveries = DB::table('recoveries')
                        ->where('distributor_ledger_id', $distributor->id)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->when($salesman !== 'All', fn($q) => $q->where('salesman', $salesman))
                        ->sum('amount_paid');

                    $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                    if (round($balance, 2) != 0 || $totalSales || $totalReturns || $totalRecoveries) {
                        $distributorData[] = [
                            'type'     => 'distributor',
                            'pcode'    => $distributor->id,
                            'name'     => $distributor->Customer,
                            'address'  => $distributor->Area,
                            'contact'  => $distributor->Contact,
                            'balance'  => round($balance, 2),
                        ];
                    }
                }
            }

            $result[$city] = [
                'customers'    => $customerData,
                'distributors' => $distributorData
            ];
        }

        return response()->json([
            'data' => $result,
            'salesman_name' => $salesman !== 'All' ? $salesman : 'All Salesmen'
        ]);
    }




    public function Area_wise_salesman_market_payments()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            $cities = City::all(); // Updated the variable name to avoid confusion

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.Area_wise_salesman_market_payments', [
                'Customers' => $Customers,
                'cities' => $cities,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function receivablesalesmanmarketreport(Request $request)
    {
        $salesmanFilter = $request->input('salesman');
        $city = $request->input('city');
        $areas = $request->input('area');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $result = [];

        // Get all salesmen or specific one
        $salesmen = ($salesmanFilter === 'All')
            ? Salesman::where('designation', 'Saleman')->pluck('name')
            : collect([$salesmanFilter]);

        foreach ($salesmen as $salesman) {
            $salesmanData = [];

            $customers = DB::table('customers')
                ->join('local_sales', 'customers.id', '=', 'local_sales.customer_id')
                ->where('local_sales.Saleman', $salesman)
                ->where('customers.identify', 'admin') // Or your dynamic identity logic
                ->when($city !== 'All', function ($q) use ($city) {
                    $q->where('customers.city', $city);
                })
                ->when(!empty($areas) && $city !== 'All', function ($q) use ($areas) {
                    $q->whereIn('customers.area', $areas);
                })
                ->select('customers.*')
                ->distinct()
                ->get();

            foreach ($customers as $customer) {
                // Get latest ledger
                $ledger = DB::table('customer_ledgers')
                    ->where('customer_id', $customer->id)
                    ->latest('created_at')
                    ->first();

                $openingBalance = $ledger->opening_balance ?? 0;
                $previousBalance = $ledger->previous_balance ?? 0;
                $closingBalance = $ledger->closing_balance ?? 0;

                $totalSales = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->where('Saleman', $salesman)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'customer')
                    ->where('party_id', $customer->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('customer_recoveries')
                    ->where('salesman', $salesman)
                    ->where('customer_ledger_id', $customer->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                // Group by city > area
                $salesmanData[$customer->city][$customer->area][] = [
                    'customer_name' => $customer->customer_name,
                    'shop_name' => $customer->shop_name,
                    'phone' => $customer->phone_number,
                    'opening_balance' => round($openingBalance, 2),
                    'previous_balance' => round($previousBalance, 2),
                    'closing_balance' => round($closingBalance, 2),
                    'total_sales' => round($totalSales, 2),
                    'total_returns' => round($totalReturns, 2),
                    'total_recoveries' => round($totalRecoveries, 2),
                    'balance' => round($balance, 2),
                ];
            }

            $result[$salesman] = $salesmanData;
        }

        return response()->json($result);
    }



    public function Date_wise_Sales_Report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.Date_wise_Sales_Report', [
                'Customers' => $Customers,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }
    public function getsalesreport(Request $request)
    {
        $salesman = $request->salesman;
        $type = $request->type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $sales = [];
        $authUser = Auth::user();

        // --- Distributor Sales ---
        if ($type == 'all' || $type == 'distributor') {
            $query = DB::table('sales')->whereBetween('Date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('Saleman', $salesman);
            }

            $results = $query->get();

            $distributorIds = $results->pluck('distributor_id')->unique()->filter()->values();

            $distributorMap = DB::table('distributors')
                ->whereIn('id', $distributorIds)
                ->pluck('Customer', 'id');

            foreach ($results as $row) {
                $items = json_decode($row->item, true) ?? [];
                $cartons = json_decode($row->carton_qty, true) ?? [];
                $pcs = json_decode($row->pcs, true) ?? [];

                $itemDetails = [];
                foreach ($items as $i => $itm) {
                    $itemDetails[] = $itm . " (" . ($cartons[$i] ?? 0) . " CTN, " . ($pcs[$i] ?? 0) . " PCS)";
                }

                $distributorName = $distributorMap[$row->distributor_id] ?? 'Distributor-' . $row->distributor_id;

                $sales[] = [
                    'invoice_number' => $row->invoice_number,
                    'date' => $row->Date,
                    'party_name' => $distributorName,
                    'area' => $row->distributor_area ?? 'N/A',
                    'remarks' => 'Distributor Sale',
                    'items' => implode(", ", $itemDetails),
                    'amount_paid' => number_format($row->net_amount),
                    'salesman' => $row->Saleman ?? '-'
                ];
            }
        }

        // --- Customer Sales ---
        if ($type == 'all' || $type == 'customer') {

            $query = DB::table('local_sales')
                ->whereBetween('Date', [$startDate, $endDate]);

            // 🔐 ROLE BASED FILTER (MAIN FIX)
            if ($authUser->usertype === 'admin') {
                // admin sirf apni local sales dekhe
                $query->where('admin_or_user_id', $authUser->id)
                    ->where('identify', 'admin');
            } elseif ($authUser->usertype === 'distributor') {
                // distributor sirf apni local sales dekhe
                $query->where('admin_or_user_id', $authUser->id)
                    ->where('identify', 'distributor');
            }

            if ($salesman !== 'All') {
                $query->where('Saleman', $salesman);
            }

            $results = $query->get();

            foreach ($results as $row) {

                $items   = json_decode($row->item, true) ?? [];
                $cartons = json_decode($row->carton_qty, true) ?? [];
                $pcs     = json_decode($row->pcs, true) ?? [];

                $itemDetails = [];
                foreach ($items as $i => $itm) {
                    $itemDetails[] = $itm . " (" . ($cartons[$i] ?? 0) . " CTN, " . ($pcs[$i] ?? 0) . " PCS)";
                }

                $sales[] = [
                    'invoice_number' => $row->invoice_number,
                    'date' => $row->Date,
                    'party_name' => $row->customer_shopname ?? 'Customer-' . $row->customer_id,
                    'area' => $row->customer_area ?? 'N/A',
                    'remarks' => 'Customer Sale',
                    'items' => implode(", ", $itemDetails),
                    'amount_paid' => number_format($row->net_amount),
                    'salesman' => $row->Saleman ?? '-'
                ];
            }
        }

        return response()->json($sales);
    }

    public function Product_wise_Sales_Report()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $user = Auth::user();
        if ($user->usertype === 'admin') {

            $Products = Product::where('admin_or_user_id', $user->id)
                ->select('id', 'item_name as item')
                ->orderBy('item_name')
                ->get();
        }

        // 🔹 DISTRIBUTOR CASE
        else if ($user->usertype === 'distributor') {

            $Products = DistributorProduct::where('distributor_id', $user->user_id)
                ->select('id', 'item')
                ->orderBy('item')
                ->get();
        } else {
            $Products = collect(); // safety
        }
        return view('admin_panel.reports.Product_wise_Sales_Report', compact('Products'));
    }


    public function getProductsalesreport(Request $request)
    {
        $products  = $request->Product;
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $user = Auth::user();
        $sales = [];

        /* =====================================================
       🔹 ADMIN : Distributor Sales + Admin Customers
    ===================================================== */
        if ($user->usertype === 'admin') {

            /* ---------- DISTRIBUTOR SALES ---------- */
            $results = DB::table('sales')
                ->whereBetween('Date', [$startDate, $endDate])
                ->get();

            $distributors = DB::table('distributors')
                ->select('id', 'Customer as name', 'address', 'area')
                ->get()
                ->keyBy('id');

            foreach ($results as $row) {

                $items   = json_decode($row->item, true) ?? [];
                $cartons = json_decode($row->carton_qty, true) ?? [];
                $pcs     = json_decode($row->pcs, true) ?? [];
                $liters  = json_decode($row->liter, true) ?? [];
                $amounts = json_decode($row->amount, true) ?? [];

                $d = $distributors[$row->distributor_id] ?? null;

                foreach ($items as $i => $itm) {

                    if (empty($itm)) continue;
                    if (!in_array('All', $products) && !in_array($itm, $products)) continue;

                    $sales[] = [
                        'type'       => 'Distributor',
                        'name'       => $d->name ?? '-',
                        'address'    => $d->address ?? '-',
                        'area'       => $d->area ?? '-',
                        'item'       => $itm,
                        'carton_qty' => $cartons[$i] ?? 0,
                        'pcs'        => $pcs[$i] ?? 0,
                        'liters'     => $liters[$i] ?? 0,
                        'amount'     => $amounts[$i] ?? 0,
                    ];
                }
            }

            /* ---------- ADMIN CUSTOMER SALES ---------- */
            $local = DB::table('local_sales')
                ->where('identify', 'admin')               // 🔑 admin ke customers
                ->whereBetween('Date', [$startDate, $endDate])
                ->get();

            $customers = DB::table('customers')
                ->where('identify', 'admin')
                ->select('id', 'shop_name as name', 'address', 'area')
                ->get()
                ->keyBy('id');
        }

        /* =====================================================
       🔹 DISTRIBUTOR : ONLY own customers local sales
    ===================================================== */
        if ($user->usertype === 'distributor') {

            $local = DB::table('local_sales')
                ->where('identify', 'distributor')
                ->where('admin_or_user_id', $user->id)   // 🔑 sirf apni sales
                ->whereBetween('Date', [$startDate, $endDate])
                ->get();

            $customers = DB::table('customers')
                ->where('identify', 'distributor')
                ->where('admin_or_user_id', $user->id)   // 🔑 sirf apne customers
                ->select('id', 'shop_name as name', 'address', 'area')
                ->get()
                ->keyBy('id');
        }

        /* =====================================================
       🔹 CUSTOMER SALES LOOP (COMMON)
    ===================================================== */
        if (!empty($local)) {

            foreach ($local as $row) {

                $items   = json_decode($row->item, true) ?? [];
                $cartons = json_decode($row->carton_qty, true) ?? [];
                $pcs     = json_decode($row->pcs, true) ?? [];
                $liters  = json_decode($row->liter, true) ?? [];
                $amounts = json_decode($row->amount, true) ?? [];

                $c = $customers[$row->customer_id] ?? null;

                foreach ($items as $i => $itm) {

                    if (empty($itm)) continue;
                    if (!in_array('All', $products) && !in_array($itm, $products)) continue;

                    $sales[] = [
                        'type'       => 'Customer',
                        'name'       => $c->name ?? '-',
                        'address'    => $c->address ?? '-',
                        'area'       => $c->area ?? '-',
                        'item'       => $itm,
                        'carton_qty' => $cartons[$i] ?? 0,
                        'pcs'        => $pcs[$i] ?? 0,
                        'liters'     => $liters[$i] ?? 0,
                        'amount'     => $amounts[$i] ?? 0,
                    ];
                }
            }
        }

        return response()->json(array_values($sales));
    }
}
