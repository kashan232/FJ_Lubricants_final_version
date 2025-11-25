<?php

namespace App\Http\Controllers;

use App\Models\CustomerLedger;
use App\Models\DistributorLedger;
use App\Models\LocalSale;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleReturnController extends Controller
{
    public function add_sale_return()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            return view('admin_panel.sale_return.add_sale_return');
        } else {
            return redirect()->back();
        }
    }

    public function getSaleInvoices(Request $request)
    {
        $user = Auth::user();
        $saleType = $request->sale_type;
        // Check if the logged-in user is distributor
        if ($user->usertype === 'distributor') {
            // Distributors will always get local sales only
            $invoices = DB::table('local_sales')
                ->where('admin_or_user_id', $user->id)
                ->pluck('invoice_number');
        } else {
            // Admin users can choose type
            if ($saleType === 'distributor') {
                $invoices = DB::table('sales')
                    ->where('admin_or_user_id', $user->id)
                    ->pluck('invoice_number');
            } else {
                $invoices = DB::table('local_sales')
                    ->where('admin_or_user_id', $user->id)
                    ->pluck('invoice_number');
            }
        }

        return response()->json($invoices);
    }


    public function fetchSaleDetails(Request $request)
    {
        $type = $request->input('sale_type');
        $invoiceNumber = $request->input('invoice_number');

        if (!$type || !$invoiceNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Sale type and invoice number are required.'
            ]);
        }

        if ($type === 'distributor') {
            $sale = Sale::with('distributor')->where('invoice_number', $invoiceNumber)->first();
        } elseif ($type === 'customer') {
            $sale = LocalSale::with('customer')->where('invoice_number', $invoiceNumber)->first();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid sale type.'
            ]);
        }

        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'Sale details not found.'
            ]);
        }

        try {
            $createdAt = Carbon::parse($sale->created_at);
        } catch (\Exception $e) {
            $createdAt = null;
        }

        $items = json_decode($sale->item, true) ?? [];
        $cartonQty = json_decode($sale->carton_qty, true) ?? [];
        $pcsQty = json_decode($sale->pcs, true) ?? [];
        $liter = json_decode($sale->liter, true) ?? [];
        $rate = json_decode($sale->rate, true) ?? [];
        $discount = json_decode($sale->discount, true) ?? [];
        $returnQty = json_decode($sale->return_qty, true) ?? [];
        $pcscarton = json_decode($sale->pcs_carton, true) ?? [];

        $partyName = $type === 'distributor'
            ? ($sale->distributor->Customer ?? 'N/A')
            : ($sale->customer->customer_name ?? 'N/A');

        $rows = [];
        $grandTotal = 0;
        $totalReturnAmount = 0;
        $count = count($items);

        for ($index = 0; $index < $count; $index++) {
            $cartonQuantity = $cartonQty[$index] ?? 0;
            $pcsQuantity = $pcsQty[$index] ?? 0;
            $rateAmount = $rate[$index] ?? 0;
            $pcsPerCarton = $pcscarton[$index] ?? 0;
            $returnQtyValue = $returnQty[$index] ?? 0;
            $discountAmount = $discount[$index] ?? 0;
            $literQty = $liter[$index] ?? 0;

            // Calculate total per row
            $cartonTotal = $cartonQuantity * $rateAmount;

            // Only calculate pcs amount if pcs/carton is not zero
            $pcsTotal = 0;
            if ($pcsPerCarton > 0 && $pcsQuantity > 0) {
                $ratePerPcs = $rateAmount / $pcsPerCarton;
                $pcsTotal = $pcsQuantity * $ratePerPcs;
            }

            $itemTotal = $cartonTotal + $pcsTotal;

            $grandTotal += $itemTotal;
            $totalReturnAmount += $returnQtyValue * $rateAmount;

            $rows[] = [
                'invoice_number' => $sale->invoice_number,
                'date' => $createdAt ? $createdAt->format('Y-m-d') : 'N/A',
                'distributor' => $partyName,
                'item' => $items[$index] ?? 'N/A',
                'carton_quantity' => $cartonQuantity,
                'pcs_quantity' => $pcsQuantity,
                'liter' => $literQty,
                'rate' => $rateAmount,
                'discount_amount' => $discountAmount,
                'packing' => $pcsPerCarton,
                'return_qty' => $returnQtyValue,
                'return_amount' => $returnQtyValue * $rateAmount,
                'item_total' => round($itemTotal, 2)
            ];
        }

        return response()->json([
            'success' => true,
            'sales' => $rows,
            'summary' => [
                'grand_total' => round($grandTotal, 2),
                'discount_value' => $sale->discount_value ?? 0,
                'scheme_value' => $sale->scheme_value ?? 0,
                'net_amount' => $sale->net_amount ?? 0,
                'total_return_amount' => round($totalReturnAmount, 2),
            ],
            'party_id' => $type === 'distributor' ? $sale->distributor_id : $sale->customer_id
        ]);
    }

    public function store(Request $request)
    {
        // Validate input data
        $validatedData = $request->validate([
            'sale_type' => 'required',
            'party_id' => 'required',
            'invoice_number' => 'required',
            'return_items' => 'required|array',
            'return_items.*.item_id' => 'required',
            'return_items.*.item_name' => 'required',
            'return_items.*.pcs_per_carton' => 'required|integer',
            'return_items.*.carton_qty' => 'required|integer',
            'return_items.*.pcs_qty' => 'required|integer',
            'return_items.*.rate' => 'required|numeric',
            'return_items.*.discount' => 'nullable|numeric',
            'return_items.*.total' => 'required|numeric',
        ]);

        $user = Auth::user();
        $userId = $user->id;

        $items = collect($validatedData['return_items']);
        $totalReturnAmount = $items->sum('total');

        // Create SaleReturn
        $saleReturn = SaleReturn::create([
            'admin_or_user_id' => $userId,
            'sale_type' => $validatedData['sale_type'],
            'party_id' => $validatedData['party_id'],
            'invoice_number' => $validatedData['invoice_number'],
            'item_ids' => $items->pluck('item_id')->implode(','),
            'item_names' => $items->pluck('item_name')->implode(','),
            'pcs_per_carton' => $items->pluck('pcs_per_carton')->implode(','),
            'carton_qty' => $items->pluck('carton_qty')->implode(','),
            'pcs_qty' => $items->pluck('pcs_qty')->implode(','),
            'rate' => $items->pluck('rate')->implode(','),
            'discount' => $items->pluck('discount')->implode(','),
            'total' => $items->pluck('total')->implode(','),
            'total_return_amount' => $totalReturnAmount,
        ]);

        // Update return status and ledger
        if ($validatedData['sale_type'] === 'distributor') {
            $sale = Sale::where('invoice_number', $validatedData['invoice_number'])->first();
            if ($sale) {
                $sale->return_status = 1;
                $sale->save();
            }

            $ledger = DB::table('distributor_ledgers')->where('distributor_id', $validatedData['party_id'])->first();
            if ($ledger) {
                $newClosingBalance = $ledger->closing_balance - $totalReturnAmount;
                DB::table('distributor_ledgers')
                    ->where('distributor_id', $validatedData['party_id'])
                    ->update(['closing_balance' => $newClosingBalance]);
            }
        } elseif ($validatedData['sale_type'] === 'customer') {
            $localSale = LocalSale::where('invoice_number', $validatedData['invoice_number'])->first();
            if ($localSale) {
                $localSale->return_status = 1;
                $localSale->save();
            }

            CustomerLedger::where('customer_id', $validatedData['party_id'])
                ->decrement('closing_balance', $totalReturnAmount);
        }

        // 🔁 STOCK RESTORE LOGIC
        foreach ($items as $item) {
            $cartons = $item['carton_qty'];
            $pcs = $item['pcs_qty'];

            if ($user->usertype === 'distributor') {
                DB::table('distributor_products')
                    ->where('distributor_id', $user->user_id)
                    ->where('item', $item['item_name']) // or use item_id if available
                    ->increment('carton_quantity', $cartons);
            } elseif ($user->usertype === 'admin') {
                DB::table('products')
                    ->where('admin_or_user_id', $userId)
                    ->where('item_name', $item['item_name']) // or use item_id if available
                    ->increment('carton_quantity', $cartons);

                DB::table('products')
                    ->where('admin_or_user_id', $userId)
                    ->where('item_name', $item['item_name'])
                    ->increment('loose_pieces', $pcs);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sale return recorded and stock updated successfully.',
            'data' => $saleReturn,
        ]);
    }


    public function all_sale_return()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $salesReturns = SaleReturn::with(['distributor', 'customer'])
                ->where('admin_or_user_id', $userId)
                ->get();

            return view('admin_panel.sale_return.all_sale_return', compact('salesReturns'));
        } else {
            return redirect()->back();
        }
    }
}
