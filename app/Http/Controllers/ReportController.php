<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerRecovery;
use App\Models\Distributor;
use App\Models\LocalSale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Sale;
use App\Models\Salesman;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function Distributor_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Distributors = Distributor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            return view('admin_panel.reports.distributor_ledger_record', [
                'Distributors' => $Distributors,
            ]);
        } else {
            return redirect()->back();
        }
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

        // ✅ Opening Balance = BaseOpening + (Sales − Recoveries − Returns)
        $openingBalance = $baseOpening + $previousSales - ($previousRecoveries + $previousReturns);

        // ---- Current Period Transactions ----
        $recoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'salesman', 'date', 'remarks')
            ->get();

        $sales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->select('invoice_number', 'Date', 'Booker', 'Saleman', 'grand_total', 'discount_value', 'scheme_value', 'net_amount')
            ->get();

        $saleReturns = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('invoice_number', 'created_at', 'total_return_amount')
            ->get();

        // ✅ Closing Balance = Opening + Sales − (Recoveries + Returns)
        $closingBalance = $openingBalance
            + $sales->sum('net_amount')
            - ($recoveries->sum('amount_paid') + $saleReturns->sum('total_return_amount'));

        // ---- Distributor Balance Transfers (Jo is distributor ko mile hain)
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
            ->select('id', 'invoice_number', 'purchase_date', 'grand_total')
            ->get()
            ->map(function ($purchase) {
                return [
                    'invoice_number' => $purchase->invoice_number,
                    'date'          => $purchase->purchase_date,
                    'grand_total'   => $purchase->grand_total,
                    'net_amount'    => $purchase->grand_total,
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

        // ✅ Opening Balance = Ledger Opening + (Sales − Recoveries − Returns)
        $openingBalance = $baseOpening + $previousSales - ($previousRecoveries + $previousReturns);

        // ---- Current Period Transactions ----
        $recoveries = DB::table('customer_recoveries')
            ->where('customer_ledger_id', $CustomerId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'salesman', 'date', 'remarks')
            ->get();

        $localSales = DB::table('local_sales')
            ->where('customer_id', $CustomerId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->select('invoice_number', 'Date', 'customer_shopname', 'grand_total', 'discount_value', 'scheme_value', 'net_amount', 'Saleman')
            ->get();

        $saleReturns = DB::table('sale_returns')
            ->where('sale_type', 'customer')
            ->where('party_id', $CustomerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('invoice_number', 'total_return_amount', 'created_at')
            ->get();

        // ✅ Closing Balance = Opening + Sales − (Recoveries + Returns)
        $closingBalance = $openingBalance
            + $localSales->sum('net_amount')
            - ($recoveries->sum('amount_paid') + $saleReturns->sum('total_return_amount'));

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
            // 1️⃣ **Total Purchased Quantity**
            $purchaseData = Purchase::whereJsonContains('item', $item->item_name)->get();
            $totalPurchasedQty = 0;

            foreach ($purchaseData as $purchase) {
                $itemNames = json_decode($purchase->item, true);
                $cartonQtyArray = json_decode($purchase->carton_qty, true);

                if (is_array($itemNames) && is_array($cartonQtyArray)) {
                    foreach ($itemNames as $index => $purchasedItem) {
                        if ($purchasedItem === $item->item_name) {
                            $totalPurchasedQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // 2️⃣ **Total Distributor Sale Quantity**
            $salesData = Sale::whereJsonContains('item', $item->item_name)->get();
            $totalDistributorSoldQty = 0;

            foreach ($salesData as $sale) {
                $itemNames = json_decode($sale->item, true);
                $cartonQtyArray = json_decode($sale->carton_qty, true);

                if (is_array($itemNames) && is_array($cartonQtyArray)) {
                    foreach ($itemNames as $index => $soldItem) {
                        if ($soldItem === $item->item_name) {
                            $totalDistributorSoldQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // 3️⃣ **Total Local Sale Quantity**
            $localSalesData = LocalSale::whereJsonContains('item', $item->item_name)->get();
            $totalLocalSoldQty = 0;

            foreach ($localSalesData as $localSale) {
                $itemNames = json_decode($localSale->item, true);
                $cartonQtyArray = json_decode($localSale->carton_qty, true);

                if (is_array($itemNames) && is_array($cartonQtyArray)) {
                    foreach ($itemNames as $index => $soldItem) {
                        if ($soldItem === $item->item_name) {
                            $totalLocalSoldQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // 4️⃣ **Total Purchase Return Quantity**
            $returnData = DB::table('purchase_returns')->whereJsonContains('item', $item->item_name)->get();
            $totalPurchaseReturnQty = 0;

            foreach ($returnData as $return) {
                $itemNames = json_decode($return->item, true);
                $returnQtyArray = json_decode($return->return_qty, true);

                if (is_array($itemNames) && is_array($returnQtyArray)) {
                    foreach ($itemNames as $index => $returnItem) {
                        if ($returnItem === $item->item_name) {
                            $totalPurchaseReturnQty += isset($returnQtyArray[$index]) ? intval($returnQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // 5️⃣ **Total Distributor Return Quantity**
            $distributorReturns = DB::table('sale_returns')
                ->where('sale_type', 'distributor')
                ->where('item_names', 'LIKE', '%' . $item->item_name . '%')
                ->get();

            $totalDistributorReturnQty = 0;

            foreach ($distributorReturns as $return) {
                $itemNames = json_decode($return->item_names, true);
                $cartonQtyArray = json_decode($return->carton_qty, true);

                // Handle if not JSON (plain string case)
                if (!is_array($itemNames)) {
                    $itemNames = [$return->item_names];
                    $cartonQtyArray = [$return->carton_qty];
                }

                foreach ($itemNames as $index => $returnItem) {
                    if ($returnItem === $item->item_name) {
                        $totalDistributorReturnQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                    }
                }
            }


            // 6️⃣ **Total Local Return Quantity**
            $localReturns = DB::table('sale_returns')
                ->where('sale_type', 'customer')
                ->where('item_names', 'LIKE', '%' . $item->item_name . '%')
                ->get();

            $totalLocalReturnQty = 0;

            foreach ($localReturns as $return) {
                $itemNames = json_decode($return->item_names, true);
                $cartonQtyArray = json_decode($return->carton_qty, true);

                if (!is_array($itemNames)) {
                    $itemNames = [$return->item_names];
                    $cartonQtyArray = [$return->carton_qty];
                }

                foreach ($itemNames as $index => $returnItem) {
                    if ($returnItem === $item->item_name) {
                        $totalLocalReturnQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                    }
                }
            }

            // ✅ Assign the Correct Values (Separate Counts)
            $item->total_purchased = $totalPurchasedQty;
            $item->total_purchase_return = $totalPurchaseReturnQty;
            $item->total_distributor_sold = $totalDistributorSoldQty;
            $item->total_distributor_return = $totalDistributorReturnQty; // New Line
            $item->total_local_sold = $totalLocalSoldQty;
            $item->total_local_return = $totalLocalReturnQty; // New Line
        }

        return response()->json($items);
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
        $salesman = $request->salesman;
        $type = $request->type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $recoveries = [];

        // Distributor Recoveries
        if ($type == 'all' || $type == 'distributor') {
            $query = Recovery::whereBetween('date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('salesman', $salesman);
            }

            $distributorRecoveries = $query->get();

            foreach ($distributorRecoveries as $recovery) {
                $distributor = Distributor::find($recovery->distributor_ledger_id);
                $recoveries[] = [
                    'date'        => $recovery->date,
                    'shop_name'   => '-', // Distributors ke liye shop name nahi hota
                    'party_name'  => $distributor->Customer ?? 'N/A',
                    'area'        => $distributor->Area ?? 'N/A',
                    'remarks'     => $recovery->remarks,
                    'amount_paid' => number_format($recovery->amount_paid),
                    'salesman'    => $recovery->salesman ?? '-'
                ];
            }
        }

        // Customer Recoveries
        if ($type == 'all' || $type == 'customer') {
            $query = CustomerRecovery::whereBetween('date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('salesman', $salesman);
            }

            $customerRecoveries = $query->get();

            foreach ($customerRecoveries as $recovery) {
                $customer = Customer::find($recovery->customer_ledger_id);
                $recoveries[] = [
                    'date'        => $recovery->date,
                    'shop_name'   => $customer->shop_name ?? 'N/A',   // ✅ Shop Name
                    'party_name'  => $customer->customer_name ?? 'N/A', // ✅ Party = Customer Name
                    'area'        => $customer->area ?? 'N/A',
                    'remarks'     => $recovery->remarks,
                    'amount_paid' => number_format($recovery->amount_paid),
                    'salesman'    => $recovery->salesman ?? '-'
                ];
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
        $userId = Auth::id();
        $start = $request->start_date;
        $end = $request->end_date;

        $purchases = Purchase::where('admin_or_user_id', $userId)
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
            $amounts = json_decode($purchase->amount ?? '[]'); // 👈 Use amount field here

            foreach ($items as $i => $item) {
                $netAmount = floatval($amounts[$i] ?? 0);

                $report[] = [
                    'code' => $key + 1,
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

        // Step 3: Fetch all cities
        $cities = City::all();

        return view('admin_panel.reports.Area_wise_Customer_payments', [
            'Customers' => $Customers,
            'cities'    => $cities,
            'Salesmans' => $Salesmans,
        ]);
    }


    public function fetchReceivableReport(Request $request)
    {
        $cities = $request->city ?? [];  // multiple cities array
        $areas = $request->area;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $salesman = $request->salesman;

        if (in_array('All', (array)$cities)) {
            $customerCities = DB::table('customers')->select('city')->distinct()->pluck('city')->toArray();
            $distributorCities = DB::table('distributors')->select('City')->distinct()->pluck('City')->toArray();
            $allCities = array_unique(array_merge($customerCities, $distributorCities));
        } else {
            $allCities = (array)$cities; // multiple cities handle
        }

        $result = [];

        foreach ($allCities as $c) {
            // ==== CUSTOMERS ====
            $customersQuery = DB::table('customers')->where('city', $c);

            if (!empty($areas) && !in_array('All', (array)$areas)) {
                $customersQuery->whereIn('area', (array)$areas);
            }

            $customers = $customersQuery->get();
            $customerData = [];

            foreach ($customers as $customer) {
                $customerHasSalesBySalesman = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->when($salesman !== 'All', fn($query) => $query->where('Saleman', $salesman))
                    ->exists();

                if ($salesman !== 'All' && !$customerHasSalesBySalesman) {
                    continue;
                }

                $ledger = DB::table('customer_ledgers')->where('customer_id', $customer->id)->first();
                $openingBalance = $ledger->opening_balance ?? 0;

                $totalSales = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn($query) => $query->where('Saleman', $salesman))
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'customer')
                    ->where('party_id', $customer->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->when($salesman !== 'All', function ($query) use ($salesman) {
                        return $query->whereExists(function ($subquery) use ($salesman) {
                            $subquery->select(DB::raw(1))
                                ->from('local_sales')
                                ->whereColumn('local_sales.customer_id', 'sale_returns.party_id')
                                ->where('local_sales.Saleman', $salesman);
                        });
                    })
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('customer_recoveries')
                    ->where('customer_ledger_id', $customer->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn($query) => $query->where('salesman', $salesman))
                    ->sum('amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                if (round($balance, 2) != 0 || $totalSales > 0 || $totalReturns > 0 || $totalRecoveries > 0) {
                    $customerData[] = [
                        'type' => 'customer',
                        'pcode' => $customer->id,
                        'name' => $customer->customer_name,
                        'shopname' => $customer->shop_name,
                        'address' => $customer->area,
                        'contact' => $customer->phone_number,
                        'balance' => round($balance, 2),
                    ];
                }
            }

            // ==== DISTRIBUTORS ====
            $distributorQuery = DB::table('distributors')->where('City', $c);

            if (!empty($areas) && !in_array('All', (array)$areas)) {
                $distributorQuery->whereIn('Area', (array)$areas);
            }

            if ($salesman !== 'All') {
                $distributorQuery->whereExists(function ($query) use ($salesman) {
                    $query->select(DB::raw(1))
                        ->from('sales')
                        ->whereColumn('sales.distributor_id', 'distributors.id')
                        ->where('sales.Saleman', $salesman);
                });
            }

            $distributors = $distributorQuery->get();
            $distributorData = [];

            foreach ($distributors as $distributor) {
                $distributorHasSalesBySalesman = DB::table('sales')
                    ->where('distributor_id', $distributor->id)
                    ->when($salesman !== 'All', fn($query) => $query->where('Saleman', $salesman))
                    ->exists();

                if ($salesman !== 'All' && !$distributorHasSalesBySalesman) {
                    continue;
                }

                $ledger = DB::table('distributor_ledgers')->where('distributor_id', $distributor->id)->first();
                $openingBalance = $ledger->opening_balance ?? 0;

                $totalSales = DB::table('sales')
                    ->where('distributor_id', $distributor->id)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn($query) => $query->where('Saleman', $salesman))
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'distributor')
                    ->where('party_id', $distributor->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->when($salesman !== 'All', function ($query) use ($salesman) {
                        return $query->whereExists(function ($subquery) use ($salesman) {
                            $subquery->select(DB::raw(1))
                                ->from('sales')
                                ->whereColumn('sales.distributor_id', 'sale_returns.party_id')
                                ->where('sales.Saleman', $salesman);
                        });
                    })
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('recoveries')
                    ->where('distributor_ledger_id', $distributor->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn($query) => $query->where('salesman', $salesman))
                    ->sum('amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                if (round($balance, 2) != 0 || $totalSales > 0 || $totalReturns > 0 || $totalRecoveries > 0) {
                    $distributorData[] = [
                        'type' => 'distributor',
                        'pcode' => $distributor->id,
                        'name' => $distributor->Customer,
                        'address' => $distributor->Area,
                        'contact' => $distributor->Contact,
                        'balance' => round($balance, 2),
                    ];
                }
            }

            $result[$c] = [
                'distributors' => $distributorData,
                'customers' => $customerData
            ];
        }

        return response()->json([
            'data' => $result,
            'salesman_name' => ($salesman !== 'All') ? $salesman : 'All Salesmen'
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
            $query = DB::table('local_sales')->whereBetween('Date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('Saleman', $salesman);
            }

            $results = $query->get();

            foreach ($results as $row) {
                $items = json_decode($row->item, true) ?? [];
                $cartons = json_decode($row->carton_qty, true) ?? [];
                $pcs = json_decode($row->pcs, true) ?? [];

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
        if (Auth::id()) {
            $userId = Auth::id();
            $Products = Product::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.Product_wise_Sales_Report', [
                'Products' => $Products,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getProductsalesreport(Request $request)
    {
        $products  = $request->Product;
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $sales = [];

        // --- Distributor Sales ---
        $query   = DB::table('sales')->whereBetween('Date', [$startDate, $endDate]);
        $results = $query->get();

        $distributorIds = $results->pluck('distributor_id')->unique()->filter()->values();
        $distributorMap = DB::table('distributors')
            ->whereIn('id', $distributorIds)
            ->pluck('Customer', 'id');

        foreach ($results as $row) {
            $items   = json_decode($row->item, true) ?? [];
            $cartons = json_decode($row->carton_qty, true) ?? [];
            $pcs     = json_decode($row->pcs, true) ?? [];
            $amounts = json_decode($row->amount, true) ?? [];
            $liters  = property_exists($row, 'liter') ? (json_decode($row->liter, true) ?? []) : [];

            foreach ($items as $i => $itm) {
                // ❌ Null ya "Select Item" skip karo
                if (empty($itm) || strtolower($itm) == "select item") {
                    continue;
                }

                // Agar filter laga hai to "All" ke ilawa wahi products allow hongay
                if (!in_array("All", $products) && !in_array($itm, $products)) {
                    continue;
                }

                if (!isset($sales[$itm])) {
                    $sales[$itm] = [
                        'item'       => $itm,
                        'carton_qty' => 0,
                        'pcs'        => 0,
                        'liters'     => 0,
                        'amount'     => 0,
                    ];
                }

                $sales[$itm]['carton_qty'] += $cartons[$i] ?? 0;
                $sales[$itm]['pcs']        += $pcs[$i] ?? 0;
                $sales[$itm]['liters']     += $liters[$i] ?? 0;
                $sales[$itm]['amount']     += $amounts[$i] ?? 0;
            }
        }

        // --- Customer Sales ---
        $query   = DB::table('local_sales')->whereBetween('Date', [$startDate, $endDate]);
        $results = $query->get();

        foreach ($results as $row) {
            $items   = json_decode($row->item, true) ?? [];
            $cartons = json_decode($row->carton_qty, true) ?? [];
            $pcs     = json_decode($row->pcs, true) ?? [];
            $amounts = json_decode($row->amount, true) ?? [];
            $liters  = property_exists($row, 'liter') ? (json_decode($row->liter, true) ?? []) : [];

            foreach ($items as $i => $itm) {
                // ❌ Null ya "Select Item" skip karo
                if (empty($itm) || strtolower($itm) == "select item") {
                    continue;
                }

                if (!in_array("All", $products) && !in_array($itm, $products)) {
                    continue;
                }

                if (!isset($sales[$itm])) {
                    $sales[$itm] = [
                        'item'       => $itm,
                        'carton_qty' => 0,
                        'pcs'        => 0,
                        'liters'     => 0,
                        'amount'     => 0,
                    ];
                }

                $sales[$itm]['carton_qty'] += $cartons[$i] ?? 0;
                $sales[$itm]['pcs']        += $pcs[$i] ?? 0;
                $sales[$itm]['liters']     += $liters[$i] ?? 0;
                $sales[$itm]['amount']     += $amounts[$i] ?? 0;
            }
        }

        return response()->json(array_values($sales));
    }
}
