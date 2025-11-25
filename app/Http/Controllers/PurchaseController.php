<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Size;
use App\Models\SubCategory;
use App\Models\Vendor;
use App\Models\VendorLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function Purchase()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $categories = Category::where('admin_or_user_id', $userId)->get();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get();
            return view('admin_panel.purchase.add_purchase', compact('categories', 'Vendors'));
        } else {
            return redirect()->back();
        }
    }

    public function getSubcategories($categoryname)
    {
        $subcategories = SubCategory::where('category_name', $categoryname)
            ->pluck('sub_category_name', 'id'); // Fetch subcategory names with their IDs

        return response()->json($subcategories);
    }

    public function getItems(Request $request)
    {
        $items = Product::where('category', $request->category_name)
            ->where('sub_category', $request->sub_category_name)
            ->get(['id', 'item_name', 'item_code', 'pcs_in_carton', 'size', 'retail_price']); // Fetch all required fields

        return response()->json($items);
    }

    public function store_Purchase(Request $request)
    {

        $request->validate([
            'purchase_date' => 'required|date',
            'party_code' => 'required',
            'party_name' => 'required',
            'category' => 'required|array',
            'subcategory' => 'required|array',
            'item' => 'required|array',
            'rate' => 'required|array',
            'carton_qty' => 'required|array',
            'pcs' => 'required|array',
            'liter' => 'required|array',
            'gross_total' => 'required|array',
            'discount' => 'nullable|array',
            'amount' => 'required|array',
            'pcs_carton' => 'required|array',
            'grand_total' => 'required|numeric',
        ]);
        $userId = Auth::id();
        $invoiceNo = Purchase::generateInvoiceNo();
        // JSON encode data to store in a single row
        $purchaseData = [
            'admin_or_user_id' => $userId,
            'invoice_number' => $invoiceNo,
            'purchase_date' => $request->purchase_date,
            'party_code' => $request->party_code,
            'party_name' => $request->party_name,
            'category' => json_encode($request->category),
            'subcategory' => json_encode($request->subcategory),
            'item' => json_encode($request->item),
            'size' => json_encode($request->size),
            'rate' => json_encode($request->rate),
            'carton_qty' => json_encode($request->carton_qty),
            'pcs' => json_encode($request->pcs),
            'liter' => json_encode($request->liter),
            'gross_total' => json_encode($request->gross_total),
            'discount' => json_encode($request->discount ?? []),
            'amount' => json_encode($request->amount),
            'pcs_carton' => json_encode($request->pcs_carton),
            'grand_total' => $request->grand_total,
        ];

        // Save purchase data in a single row
        $purchase = Purchase::create($purchaseData);

        // Step 2: Update Product Stock and Wholesale Price
        foreach ($request->item as $key => $item_name) {
            $category = $request->category[$key];
            $subcategory = $request->subcategory[$key];
            $carton_qty = $request->carton_qty[$key]; // Purchased cartons
            $pcs = $request->pcs[$key]; // Purchased pieces (added to stock directly)
            $rate = $request->rate[$key];

            // Find the product
            $product = Product::where('item_name', $item_name)
                ->where('category', $category)
                ->where('sub_category', $subcategory) // Adjusted column name
                ->first();

            if ($product) {
                // Pehle ka stock
                $previous_cartons = $product->carton_quantity;
                $pcs_in_carton = $product->pcs_in_carton; // Carton ke andar kitne pcs hain
                $previous_stock = $product->initial_stock;

                // ✅ Naye stock ki calculation
                $new_carton_quantity = $previous_cartons + $carton_qty;
                $new_initial_stock = $previous_stock + ($carton_qty * $pcs_in_carton) + $pcs;

                // ✅ Stock update (Loose pieces field hata di)
                $product->carton_quantity = $new_carton_quantity;
                $product->initial_stock = $new_initial_stock;
                $product->wholesale_price = $rate;

                $product->save();
            }
        }



        // Fetch previous balance for distributor
        $previousBalance = VendorLedger::where('vendor_id', $request->party_name)
            ->value('closing_balance') ?? 0; // If no previous balance, start from 0
        // Calculate new balances

        $newPreviousBalance = $request->grand_total;

        $newClosingBalance = $previousBalance + $request->grand_total;

        // Update or create distributor ledger
        VendorLedger::updateOrCreate(
            ['vendor_id' => $request->party_name],
            [
                'vendor_id' => $request->party_name,
                'admin_or_user_id' => $userId,
                'previous_balance' => $newPreviousBalance,
                'closing_balance' => $newClosingBalance,
            ]
        );


        return redirect()->route('purchase.invoice', $purchase->id)->with('success', 'Purchase saved successfully and stock updated!');
    }

    public function all_Purchases()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Purchases = Purchase::where('admin_or_user_id', $userId)
                ->with('vendor')
                ->get();

            foreach ($Purchases as $purchase) {
                if (!$purchase->vendor) {
                    logger("Vendor not found for Purchase ID: {$purchase->id}, Party Code: {$purchase->party_code}");
                }
            }

            return view('admin_panel.purchase.all_purchase', compact('Purchases'));
        } else {
            return redirect()->back();
        }
    }

    public function purchaseInvoice($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->gross_total_sum = array_sum(json_decode($purchase->amount));
        $purchase->discount_total_sum = array_sum(json_decode($purchase->discount));
        $purchase->grand_total = $purchase->gross_total_sum - $purchase->discount_total_sum;
        return view('admin_panel.purchase.invoice', compact('purchase'));
    }

    public function purchaseedit($id)
    {

        if (Auth::id()) {
            $userId = Auth::id();

            $purchase = Purchase::findOrFail($id);
            $categories = Category::where('admin_or_user_id', $userId)->get();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get();
            return view('admin_panel.purchase.edit_purchase', compact('categories', 'Vendors', 'purchase'));
        } else {
            return redirect()->back();
        }
    }

    public function update_purchase(Request $request, $id)
    {
        // Validate the request data.  Use the same validation rules as store.
        $request->validate([
            'purchase_date' => 'required|date',
            'party_code' => 'required',
            'party_name' => 'required',
            'category' => 'required|array',
            'subcategory' => 'required|array',
            'item' => 'required|array',
            'rate' => 'required|array',
            'carton_qty' => 'required|array',
            'pcs' => 'required|array',
            'liter' => 'required|array',
            'gross_total' => 'required|array',
            'discount' => 'nullable|array',
            'amount' => 'required|array',
            'pcs_carton' => 'required|array',
            'grand_total' => 'required|numeric',
        ]);

        $userId = Auth::id();

        $purchase = Purchase::findOrFail($id);
        $oldItems   = json_decode($purchase->item, true) ?? [];
        $oldAmounts = json_decode($purchase->amount, true) ?? [];
        
        $newItems   = $request->item;
        $newAmounts = $request->amount;

        $diffAmount = 0;
        foreach ($newItems as $index => $itemName) {
            if (!in_array($itemName, $oldItems)) {
                $diffAmount += (float) $newAmounts[$index];
            }
        }
        foreach ($oldItems as $index => $itemName) {
            if (!in_array($itemName, $newItems)) {
                $diffAmount -= (float) $oldAmounts[$index];
            }
        }

        // STEP 5: Update Vendor Ledger
        $ledger = VendorLedger::where('vendor_id', $request->party_name)->first();

        if ($ledger) {
            // Sirf difference add karo
            $ledger->closing_balance = $ledger->closing_balance + $diffAmount;
            $ledger->previous_balance = $ledger->closing_balance; // ya purana rakho agar required ho
            $ledger->admin_or_user_id = $userId;
            $ledger->updated_at = now();
            $ledger->save();
        } else {
            // Agar ledger pehli dafa ban raha hai
            VendorLedger::create([
                'vendor_id' => $request->party_name,
                'admin_or_user_id' => $userId,
                'previous_balance' => 0,
                'closing_balance' => $request->grand_total, // first entry
                'updated_at' => now(),
            ]);
        }

        // Update the purchase data.  Use the same structure as store.
        $purchaseData = [
            'admin_or_user_id' => $userId,
            'purchase_date' => $request->purchase_date,
            'party_code' => $request->party_code,
            'party_name' => $request->party_name,
            'category' => json_encode($request->category),
            'subcategory' => json_encode($request->subcategory),
            'item' => json_encode($request->item),
            'size' => json_encode($request->size),
            'rate' => json_encode($request->rate),
            'carton_qty' => json_encode($request->carton_qty),
            'pcs' => json_encode($request->pcs),
            'liter' => json_encode($request->liter),
            'gross_total' => json_encode($request->gross_total),
            'discount' => json_encode($request->discount ?? []),
            'amount' => json_encode($request->amount),
            'pcs_carton' => json_encode($request->pcs_carton),
            'grand_total' => $request->grand_total,
        ];

        $purchase->update($purchaseData); // Use update() instead of create()

        // Step 2: Update Product Stock and Wholesale Price
        foreach ($request->item as $key => $item_name) {
            $category = $request->category[$key];
            $subcategory = $request->subcategory[$key];
            $carton_qty = $request->carton_qty[$key];
            $pcs = $request->pcs[$key];
            $rate = $request->rate[$key];

            // Find the product
            $product = Product::where('item_name', $item_name)
                ->where('category', $category)
                ->where('sub_category', $subcategory)
                ->first();

            if ($product) {
                // Pehle ka stock
                $previous_cartons = $product->carton_quantity;
                $pcs_in_carton = $product->pcs_in_carton;
                $previous_stock = $product->initial_stock;

                // Calculate new stock
                $new_carton_quantity = $previous_cartons + $carton_qty;
                $new_initial_stock = $previous_stock + ($carton_qty * $pcs_in_carton) + $pcs;

                // Update product stock and price
                $product->carton_quantity = $new_carton_quantity;
                $product->initial_stock = $new_initial_stock;
                $product->wholesale_price = $rate;
                $product->save();
            }
        }

        return redirect()->back()->with('success', 'Purchase updated successfully and stock updated!');
    }
}
