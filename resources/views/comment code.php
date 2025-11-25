
<!-- store local function -->
public function store_local_sale(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $user = Auth::user();
            dd($user);
            $invoiceNo = LocalSale::generateSaleInvoiceNo();
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

            // Stock Update Logic
            foreach ($request->code as $index => $item_code) {
                $product = Product::where('item_code', $item_code)->first();
                if ($product) {
                    $cartonQty = (int) $request->carton_qty[$index];
                    $pcsSold = (int) $request->pcs[$index];

                    // Stock Calculation
                    $product->carton_quantity -= $cartonQty;
                    $product->initial_stock -= ($cartonQty * $product->pcs_in_carton) + $pcsSold;

                    // Ensure stock doesn't go negative
                    $product->carton_quantity = max($product->carton_quantity, 0);
                    $product->initial_stock = max($product->initial_stock, 0);

                    $product->save();
                }
            }

            // Fetch previous balance for distributor
            $previousBalance = CustomerLedger::where('customer_id', $request->customer_id)
                ->value('closing_balance') ?? 0; // If no previous balance, start from 0
            // Calculate new balances
            $newPreviousBalance = $request->net_amount;
            $newClosingBalance = $previousBalance + $request->net_amount;

            // Update or create distributor ledger
            CustomerLedger::updateOrCreate(
                ['customer_id' => $request->customer_id],
                [
                    'customer_id' => $request->customer_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $newPreviousBalance,
                    'closing_balance' => $newClosingBalance,
                ]
            );


            return redirect()->route('local.sale.invoice', $sale->id)->with('success', 'Sale recorded successfully and stock updated!');
        } else {
            return redirect()->back();
        }
    }

Update local customer 
public function localsaleupdate(Request $request, $id)
    {
        if (Auth::id()) {
            $userId = Auth::id();
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

            // Fetch existing sale record
            $sale = LocalSale::findOrFail($id);

            // Undo previous stock impact (if you want to be safe)
            // Optional: implement if you want to restore stock quantities based on old sale
            // For simplicity, this is skipped here

            // Update sale data
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
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'code' => $request->code,
                'item' => $request->item,
                'size' => $request->size,
                'pcs_carton' => $request->pcs_carton,
                'carton_qty' => $request->carton_qty,
                'pcs' => $request->pcs,
                'liter' => $request->liter,
                'rate' => $request->rate,
                'discount' => $request->discount,
                'amount' => $request->amount,
                'grand_total' => $request->grand_total,
                'discount_value' => $request->discount_value,
                'scheme_value' => $request->scheme_value,
                'net_amount' => $request->net_amount,
            ]);


            // Update stock quantities based on new data
            foreach ($request->code as $index => $item_code) {
                $product = Product::where('item_code', $item_code)->first();
                if ($product) {
                    $cartonQty = (int) $request->carton_qty[$index];
                    $pcsSold = (int) ($request->pcs[$index] ?? 0);

                    // Adjust stock - **You may want to first revert old sale stock quantities**
                    $product->carton_quantity -= $cartonQty;
                    $product->initial_stock -= ($cartonQty * $product->pcs_in_carton) + $pcsSold;

                    // Prevent negative stock
                    $product->carton_quantity = max($product->carton_quantity, 0);
                    $product->initial_stock = max($product->initial_stock, 0);

                    $product->save();
                }
            }

            // Update distributor ledger
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
                    'updated_at' => now(),
                ]
            );

            return redirect()->route('local.sale.invoice', $sale->id)->with('success', 'Sale updated successfully and stock updated!');
        } else {
            return redirect()->back();
        }
    }

    