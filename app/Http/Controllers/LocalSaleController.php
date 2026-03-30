<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\LocalSale;
use App\Models\Product;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocalSaleController extends Controller
{
    public function local_sale()
    {
        if (Auth::check()) {
            $authUser = Auth::user();

            if ($authUser->usertype === 'salesman') {
                // Find logged-in salesman
                $salesman = Salesman::where('name', $authUser->name)->first();

                if (!$salesman) {
                    return redirect()->back()->with('error', 'Salesman not found.');
                }

                $ownerId = $salesman->admin_or_user_id;

                // Only logged-in salesman visible
                $Salesmans = collect([$salesman]);
            } else {
                // If admin/owner
                $ownerId = $authUser->id;

                // All salesmen created by this owner
                $Salesmans = Salesman::where('admin_or_user_id', $ownerId)
                    ->where('designation', 'Saleman')
                    ->get();
            }

            // Customers only under this owner
            $Customers = Customer::where('admin_or_user_id', $ownerId)
                ->get();

            // All categories
            $categories = Category::all();
            $Staffs = Salesman::where('admin_or_user_id', $ownerId)->get();


            return view('admin_panel.local_sale.add_sale', compact('Customers', 'categories', 'Salesmans'));
        } else {
            return redirect()->back();
        }
    }


    public function store_local_sale(Request $request)
    {
        if (!Auth::id()) {
            return redirect()->back();
        }

        $user = Auth::user();
        $userId = $user->id;
        $invoiceNo = LocalSale::generateSaleInvoiceNo();

        // Validation
        $request->validate([
            'Date' => 'required|date',
            'Booker' => 'required|string',
            'Saleman' => 'required|string',
            'grand_total' => 'required|numeric',
            'discount_value' => 'required|numeric',
            'scheme_value' => 'required|numeric',
            'net_amount' => 'required|numeric',
            'category' => 'required|array',
            'subcategory' => 'required|array',
            'code' => 'required|array',
            'item' => 'required|array',
            'size' => 'required|array',
            'pcs_carton' => 'required|array',
            'carton_qty' => 'required|array',
            'pcs' => 'required|array',
            'liter' => 'required|array',
            'rate' => 'required|array',
            'discount' => 'required|array',
            'amount' => 'required|array',
        ]);

        // Sale Data Save
        $sale = LocalSale::create([
            'admin_or_user_id' => $userId,
            'identify' => $user->identify,
            'invoice_number' => $invoiceNo,
            'Date' => $request->Date,
            'customer_id' => $request->customer_id,
            'customer_shopname' => $request->customer_shopname,
            'customer_city' => $request->customer_city,
            'customer_area' => $request->customer_area,
            'customer_address' => $request->customer_address,
            'customer_phone' => $request->customer_phone,
            'Booker' => $request->Booker,
            'Saleman' => $request->Saleman,
            'category' => json_encode($request->category),
            'subcategory' => json_encode($request->subcategory),
            'code' => json_encode($request->code),
            'item' => json_encode($request->item),
            'size' => json_encode($request->size),
            'pcs_carton' => json_encode($request->pcs_carton),
            'carton_qty' => json_encode($request->carton_qty),
            'pcs' => json_encode($request->pcs),
            'liter' => json_encode($request->liter),
            'rate' => json_encode($request->rate),
            'discount' => json_encode($request->discount),
            'amount' => json_encode($request->amount),
            'grand_total' => $request->grand_total,
            'discount_value' => $request->discount_value,
            'scheme_value' => $request->scheme_value,
            'net_amount' => $request->net_amount,
        ]);

        // 🔁 STOCK LOGIC
        foreach ($request->code as $index => $item_code) {
            $cartonQty = (int) $request->carton_qty[$index];
            $pcsSold = (int) $request->pcs[$index];

            if ($user->usertype == 'admin') {
                // 🟢 Admin sale - update from Product stock
                $product = Product::where('item_code', $item_code)->first();
                if ($product) {
                    $totalSold = ($cartonQty * $product->pcs_in_carton) + $pcsSold;

                    $product->carton_quantity -= $cartonQty;
                    $product->initial_stock -= $totalSold;

                    $product->carton_quantity = max($product->carton_quantity, 0);
                    $product->initial_stock = max($product->initial_stock, 0);
                    $product->save();
                }
            }

            if ($user->usertype == 'distributor') {
                // 🔵 Distributor sale - update from DistributorProduct stock
                $distributorProduct = \App\Models\DistributorProduct::where([
                    'distributor_id' => $user->user_id,
                    'code' => $item_code,
                ])->first();

                if ($distributorProduct) {
                    $totalSold = ($cartonQty * $distributorProduct->pcs_carton) + $pcsSold;

                    $distributorProduct->carton_quantity -= $cartonQty;
                    $distributorProduct->pcs -= $pcsSold;
                    $distributorProduct->initial_stock -= $totalSold;

                    $distributorProduct->carton_quantity = max($distributorProduct->carton_quantity, 0);
                    $distributorProduct->pcs = max($distributorProduct->pcs, 0);
                    $distributorProduct->initial_stock = max($distributorProduct->initial_stock, 0);
                    $distributorProduct->save();
                }
            }
        }

        // Ledger Update (same for both)
        $previousBalance = CustomerLedger::where('customer_id', $request->customer_id)
            ->value('closing_balance') ?? 0;

        $newPreviousBalance = $request->net_amount;
        $newClosingBalance = $previousBalance + $request->net_amount;

        CustomerLedger::updateOrCreate(
            ['customer_id' => $request->customer_id],
            [
                'customer_id' => $request->customer_id,
                'admin_or_user_id' => $userId,
                'previous_balance' => $newPreviousBalance,
                'closing_balance' => $newClosingBalance,
            ]
        );

        return redirect()->route('local.sale.invoice', $sale->id)->with('success', 'Sale recorded and stock updated!');
    }

    public function all_local_sale()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();
        $userType = $authUser->usertype; // admin / distributor / salesman
        $userIdentify = $authUser->identify; // 'admin' / 'distributor'
        $userName = $authUser->name;

        // Agar user salesman hai
        if ($userType === 'salesman') {
            $Sales = LocalSale::where('Saleman', $userName)
                ->where('identify', $userIdentify) // 👈 yeh line add karo
                ->with('customer')
                ->get();
        } else {
            // admin ya distributor ka apna data
            $Sales = LocalSale::where('admin_or_user_id', $authUser->id)
                ->with('customer')
                ->get();
        }

        return view('admin_panel.local_sale.all_sale', compact('Sales'));
    }


    public function show_local_sale($id)
    {
        if (Auth::id()) {
            $sale = LocalSale::with('customer')->findOrFail($id);
            // dd($sale);
            return view('admin_panel.local_sale.show_sale', compact('sale'));
        } else {
            return redirect()->back();
        }
    }

    public function localsaleInvoice($id)
    {
        $sale = LocalSale::with('customer')->findOrFail($id);

        $customerId = $sale->customer_id;
        $adminId = $sale->admin_or_user_id;

        // Fetch latest ledger entry for this customer
        $customerLedger = CustomerLedger::where('customer_id', $customerId)
            ->where('admin_or_user_id', $adminId)
            ->latest()
            ->first();

        return view('admin_panel.local_sale.invoice', compact('sale', 'customerLedger'));
    }

    public function delete_localsale($id)
    {
        $sale = LocalSale::findOrFail($id);
        $customerId = $sale->customer_id;
        $netAmount = $sale->net_amount;

        // Step 1: Decode product-related arrays
        $categories = json_decode($sale->category);
        $subcategories = json_decode($sale->subcategory);
        $codes = json_decode($sale->code);
        $items = json_decode($sale->item);
        $sizes = json_decode($sale->size);
        $cartonQtys = json_decode($sale->carton_qty);
        $pcs = json_decode($sale->pcs);

        // Step 2: Loop through all products in the sale
        for ($i = 0; $i < count($codes); $i++) {
            $product = Product::where('item_code', $codes[$i])
                ->where('item_name', $items[$i])
                ->where('category', $categories[$i])
                ->where('sub_category', $subcategories[$i])
                ->where('size', $sizes[$i])
                ->first();

            if ($product) {
                $cartonQty = (int) $cartonQtys[$i];
                $pcsReturned = (int) $pcs[$i];
                $pcsPerCarton = (int) $product->pcs_in_carton;

                // Restore stock as it was reduced during sale
                $product->carton_quantity += $cartonQty;
                $product->initial_stock += ($cartonQty * $pcsPerCarton) + $pcsReturned;

                $product->save();
            }
        }

        // Step 3: Delete the sale
        $sale->forceDelete();

        // Step 4: Update customer ledger
        $ledger = CustomerLedger::where('customer_id', $customerId)->latest()->first();
        if ($ledger) {
            $ledger->closing_balance -= $netAmount;
            $ledger->save();
        }

        return redirect()->back()->with('success', 'Local Sale deleted, stock restored, and Customer ledger updated.');
    }

    public function localsaleEdit($id)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Customers = Customer::where('admin_or_user_id', $userId)->get();
            $categories = Category::all();  // all categories from DB
            $Staffs = Salesman::where('admin_or_user_id', $userId)->get();
            $original = LocalSale::findOrFail($id);
            return view('admin_panel.local_sale.edit_sale', compact('Customers', 'categories', 'Staffs', 'original'));
        } else {
            return redirect()->back();
        }
    }

    public function localsaleupdate(Request $request, $id)
    {
        if (!Auth::id()) {
            return redirect()->back();
        }

        $user = Auth::user();
        $userId = $user->id;

        $request->validate([
            'Date' => 'required|date',
            'Booker' => 'required|string',
            'Saleman' => 'required|string',
            'grand_total' => 'required|numeric',
            'discount_value' => 'required|numeric',
            'scheme_value' => 'required|numeric',
            'net_amount' => 'required|numeric',
            'category' => 'required|array',
            'subcategory' => 'required|array',
            'code' => 'required|array',
            'item' => 'required|array',
            'size' => 'required|array',
            'pcs_carton' => 'required|array',
            'carton_qty' => 'required|array',
            'pcs' => 'required|array',
            'liter' => 'required|array',
            'rate' => 'required|array',
            'discount' => 'required|array',
            'amount' => 'required|array',
        ]);

        // STEP 1: Fetch existing sale (OLD values)
        $sale = LocalSale::findOrFail($id);
        $oldItems   = json_decode($sale->item, true) ?? [];
        $oldAmounts = json_decode($sale->amount, true) ?? [];
        // dd($oldItems,$oldAmounts);

        // STEP 2: NEW values from request
        $newItems   = $request->item;
        $newAmounts = $request->amount;
        // dd($newItems,$newAmounts);

        // STEP 3: Calculate difference BEFORE updating
        $diffAmount = 0;
        // dd($diffAmount);

        // New added items
        foreach ($newItems as $index => $itemName) {
            if (!in_array($itemName, $oldItems)) {
                $diffAmount += (float) $newAmounts[$index];
            }
        }

        // Removed items
        foreach ($oldItems as $index => $itemName) {
            if (!in_array($itemName, $newItems)) {
                $diffAmount -= (float) $oldAmounts[$index];
            }
        }

        // STEP 4: Ledger Update
        $ledger = CustomerLedger::where('customer_id', $request->customer_id)->first();

        if ($ledger) {
            $ledger->closing_balance = $ledger->closing_balance + $diffAmount;
            $ledger->previous_balance = $request->net_amount;
            $ledger->admin_or_user_id = $userId;
            $ledger->updated_at = now();
            $ledger->save();
        } else {
            CustomerLedger::create([
                'customer_id' => $request->customer_id,
                'admin_or_user_id' => $userId,
                'previous_balance' => $request->net_amount,
                'closing_balance' => $request->net_amount,
                'updated_at' => now(),
            ]);
        }

        // STEP 1: Revert OLD sale stock (Add back)
        $oldItems   = json_decode($sale->item, true) ?? [];
        $oldCartons = json_decode($sale->carton_qty, true) ?? [];
        $oldPcs     = json_decode($sale->pcs, true) ?? [];
        $oldCodes   = json_decode($sale->code, true) ?? [];

        foreach ($oldCodes as $key => $item_code) {
            if ($user->usertype == 'admin') {
                $product = Product::where('item_code', $item_code)->first();
                if ($product) {
                    $oldTotal = ($oldCartons[$key] * $product->pcs_in_carton) + $oldPcs[$key];
                    $product->carton_quantity += $oldCartons[$key];
                    $product->initial_stock += $oldTotal;
                    $product->save();
                }
            }
            if ($user->usertype == 'distributor') {
                $distributorProduct = \App\Models\DistributorProduct::where([
                    'distributor_id' => $user->user_id,
                    'code' => $item_code,
                ])->first();
                if ($distributorProduct) {
                    $oldTotal = ($oldCartons[$key] * $distributorProduct->pcs_carton) + $oldPcs[$key];
                    $distributorProduct->carton_quantity += $oldCartons[$key];
                    $distributorProduct->pcs += $oldPcs[$key];
                    $distributorProduct->initial_stock += $oldTotal;
                    $distributorProduct->save();
                }
            }
        }

        // STEP 2: Update Sale Data
        $sale->update([
            'Date' => $request->Date,
            'customer_id' => $request->customer_id,
            'customer_shopname' => $request->customer_shopname,
            'customer_city' => $request->customer_city,
            'customer_area' => $request->customer_area,
            'customer_address' => $request->customer_address,
            'customer_phone' => $request->customer_phone,
            'Booker' => $request->Booker,
            'Saleman' => $request->Saleman,
            'category' => json_encode($request->category),
            'subcategory' => json_encode($request->subcategory),
            'code' => json_encode($request->code),
            'item' => json_encode($request->item),
            'size' => json_encode($request->size),
            'pcs_carton' => json_encode($request->pcs_carton),
            'carton_qty' => json_encode($request->carton_qty),
            'pcs' => json_encode($request->pcs),
            'liter' => json_encode($request->liter),
            'rate' => json_encode($request->rate),
            'discount' => json_encode($request->discount),
            'amount' => json_encode($request->amount),
            'grand_total' => $request->grand_total,
            'discount_value' => $request->discount_value,
            'scheme_value' => $request->scheme_value,
            'net_amount' => $request->net_amount,
        ]);

        // STEP 3: Apply NEW sale stock
        foreach ($request->code as $index => $item_code) {
            $cartonQty = (int) $request->carton_qty[$index];
            $pcsSold = (int) $request->pcs[$index];

            if ($user->usertype == 'admin') {
                $product = Product::where('item_code', $item_code)->first();
                if ($product) {
                    $totalSold = ($cartonQty * $product->pcs_in_carton) + $pcsSold;

                    $product->carton_quantity -= $cartonQty;
                    $product->initial_stock -= $totalSold;

                    $product->carton_quantity = max($product->carton_quantity, 0);
                    $product->initial_stock = max($product->initial_stock, 0);
                    $product->save();
                }
            }

            if ($user->usertype == 'distributor') {
                $distributorProduct = \App\Models\DistributorProduct::where([
                    'distributor_id' => $user->user_id,
                    'code' => $item_code,
                ])->first();

                if ($distributorProduct) {
                    $totalSold = ($cartonQty * $distributorProduct->pcs_carton) + $pcsSold;

                    $distributorProduct->carton_quantity -= $cartonQty;
                    $distributorProduct->pcs -= $pcsSold;
                    $distributorProduct->initial_stock -= $totalSold;

                    $distributorProduct->carton_quantity = max($distributorProduct->carton_quantity, 0);
                    $distributorProduct->pcs = max($distributorProduct->pcs, 0);
                    $distributorProduct->initial_stock = max($distributorProduct->initial_stock, 0);
                    $distributorProduct->save();
                }
            }
        }

        // STEP 4: Ledger Update (with item-level comparison)


        return redirect()->route('local.sale.invoice', $sale->id)->with('success', 'Sale updated successfully and stock adjusted!');
    }
}
