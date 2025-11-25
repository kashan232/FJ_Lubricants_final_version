<?php

namespace App\Http\Controllers;

use App\Models\AddExpense;
use App\Models\City;
use App\Models\Purchase;
use App\Models\Vendor;
use App\Models\VendorBuilty;
use App\Models\VendorLedger;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{

    public function vendors()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Vendors = Vendor::all();
            $cities = City::where('admin_or_user_id', $userId)->get();
            return view('admin_panel.vendors.vendors', compact('Vendors', 'cities'));
        } else {
            return redirect()->back();
        }
    }

    public function store_vendors(Request $request)
    {

        if (Auth::id()) {
            $userId = Auth::id();
            $Vendor = Vendor::create([
                'admin_or_user_id' => $userId,
                'Party_code' => $request->Party_code,
                'Party_name' => $request->Party_name,
                'Party_address' => $request->Party_address,
                'Party_phone' => $request->Party_phone,
                'City' => $request->city,
                'Area' => $request->area,
                'created_at' => Carbon::now(),
            ]);

            // Vendor Ledger Create (One-time Opening Balance)
            VendorLedger::create([
                'admin_or_user_id' => $userId,
                'vendor_id' => $Vendor->id,
                'opening_balance' => $request->opening_balance, // Pehli dafa opening balance = previous balance
                'previous_balance' => $request->opening_balance, // Pehli dafa opening balance = previous balance
                'closing_balance' => $request->opening_balance, // Closing balance bhi initially same hoga
                'created_at' => Carbon::now(),
            ]);


            return redirect()->back()->with('success', 'Vendor added successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update_vendors(Request $request, $id)
    {
        $request->validate([
            'Party_code' => 'required',
            'Party_name' => 'required',
            'Party_address' => 'required',
            'Party_phone' => 'required',
            'city' => 'required',
            'area' => 'required',
            'recape_type' => 'nullable|string',
            'recape_opening' => 'nullable|numeric',
        ]);

        $vendor = Vendor::find($id);
        if (!$vendor) {
            return redirect()->back()->with('error', 'Vendor not found.');
        }

        $ledger = VendorLedger::where('vendor_id', $id)->first();

        if ($ledger) {
            $existingOpening = $ledger->opening_balance;
            $recapeType = $request->recape_type;
            $recapeAmount = $request->recape_opening;

            if ($recapeType === "plus") {
                $ledger->opening_balance += $recapeAmount;
            } elseif ($recapeType === "minus") {
                $ledger->opening_balance -= $recapeAmount;
            }

            $ledger->previous_balance = $ledger->closing_balance;
            $ledger->closing_balance = $ledger->opening_balance;

            $ledger->save();
        } else {
            // If no ledger record exists, create one
            VendorLedger::create([
                'vendor_id' => $id,
                'opening_balance' => $request->recape_opening ?? 0,
                'previous_balance' => 0,
                'closing_balance' => $request->recape_opening ?? 0,
            ]);
        }

        $vendor->update([
            'Party_code' => $request->Party_code,
            'Party_name' => $request->Party_name,
            'Party_address' => $request->Party_address,
            'Party_phone' => $request->Party_phone,
            'City' => $request->city,
            'Area' => $request->area,
        ]);

        return redirect()->back()->with('success', 'Vendor updated successfully.');
    }


    public function vendors_ledger()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $VendorLedgers = VendorLedger::where('admin_or_user_id', $userId)->with('vendor')->get();
            return view('admin_panel.vendors.vendors_ledger', compact('VendorLedgers'));
        } else {
            return redirect()->back();
        }
    }

    public function vendors_payment(Request $request)
    {
        $vendor = Vendor::findOrFail($request->vendor_id);
        $purchase = Purchase::findOrFail($request->purchase_id);

        $amountPaid = $request->amount_paid;

        // Check previous payments
        $previousPayments = VendorPayment::where('purchase_id', $purchase->id)->sum('amount_paid');

        // Calculate remaining amount
        $remainingAmount = $purchase->grand_total - $previousPayments;

        // ✅ Validation: Amount Paid should not exceed Remaining Amount
        if ($amountPaid > $remainingAmount) {
            return redirect()->back()->with('error', 'Amount Paid cannot be greater than the Remaining Amount.');
        }

        // Update remaining amount after payment
        $newRemainingAmount = $remainingAmount - $amountPaid;
        $purchase->remaining_amount = $newRemainingAmount;
        $purchase->status = ($newRemainingAmount <= 0) ? 'Paid' : 'Unpaid';
        $purchase->save();

        // Update Vendor Ledger
        $vendorLedger = VendorLedger::where('vendor_id', $vendor->id)->first();
        if ($vendorLedger) {
            $vendorLedger->closing_balance -= $amountPaid;
            $vendorLedger->save();
        } else {
            VendorLedger::create([
                'vendor_id' => $vendor->id,
                'previous_balance' => 0,
                'closing_balance' => -$amountPaid,
            ]);
        }

        $userId = Auth::id();

        // Store Payment Record
        VendorPayment::create([
            'admin_or_user_id' => $userId,
            'vendor_id' => $vendor->id,
            'purchase_id' => $purchase->id,
            'amount_paid' => $amountPaid,
            'payment_date' => $request->payment_date,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Payment recorded successfully.');
    }


    public function amount_paid_vendors()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $VendorPayments = VendorPayment::where('admin_or_user_id', $userId)->with('vendor')->get();
            return view('admin_panel.vendors.vendor_recovery', compact('VendorPayments'));
        } else {
            return redirect()->back();
        }
    }

    public function getLedger($id)
    {
        $ledger = VendorLedger::where('vendor_id', $id)->first();

        if (!$ledger) {
            return response()->json(['opening_balance' => 0]);
        }

        return response()->json([
            'opening_balance' => $ledger->opening_balance
        ]);
    }

    public function update_vendor_payment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:vendor_payments,id',
            'adjust_type' => 'required|in:plus,minus',
            'adjust_amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $payment = VendorPayment::findOrFail($request->payment_id);
        $vendorId = $payment->vendor_id;

        // Update the vendor payment
        $adjustment = $request->adjust_type === 'plus'
            ? $payment->amount_paid + $request->adjust_amount
            : $payment->amount_paid - $request->adjust_amount;

        $payment->update([
            'amount_paid' => $adjustment,
            'payment_date' => $request->date,
            'description' => $request->description,
        ]);

        // Update the vendor ledger
        $ledger = VendorLedger::where('vendor_id', $vendorId)->latest()->first();

        if ($ledger) {
            $newClosing = $ledger->closing_balance;
            $newRecovery = $ledger->recovery;

            if ($request->adjust_type === 'plus') {
                $newClosing -= $request->adjust_amount;
                $newRecovery += $request->adjust_amount;
            } else { // minus
                $newClosing -= $request->adjust_amount;
                $newRecovery += $request->adjust_amount;
            }

            $ledger->update([
                'closing_balance' => $newClosing,
            ]);
        }

        return redirect()->back()->with('success', 'Vendor payment and ledger updated successfully.');
    }


    public function vendors_builty()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Vendors = Vendor::all();
            $builtyRecords = VendorBuilty::with('vendor')->latest()->get(); // Relation used here
            $cities = City::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.vendors.vendors_builty', compact('Vendors', 'cities', 'builtyRecords'));
        } else {
            return redirect()->back();
        }
    }


    public function store_vendors_builty(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required',
            'date' => 'required|date',
            'month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        list($vendor_id, $vendor_name) = explode('|', $request->vendor_id);

        // Step 1: Save the Builty
        $builty = VendorBuilty::create([
            'vendor_id' => $vendor_id,
            'date' => $request->date,
            'month' => $request->month,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        // Step 2: Update Ledger
        $ledger = VendorLedger::where('vendor_id', $vendor_id)->first();

        if ($ledger) {
            $ledger->previous_balance = $ledger->closing_balance;
            $ledger->closing_balance += $request->amount;
            $ledger->save();
        } else {
            VendorLedger::create([
                'admin_or_user_id' => Auth::id(),
                'vendor_id' => $vendor_id,
                'opening_balance' => 0,
                'previous_balance' => 0,
                'closing_balance' => $request->amount,
            ]);
        }

        // Step 3: Add to Expenses
        AddExpense::create([
            'admin_or_user_id' => Auth::id(),
            'expense_category' => 'BILTY EXPENSES',
            'title' => $vendor_name,
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Vendor Builty added and ledger updated successfully.');
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            'vendor_id' => 'required',
            'date' => 'required|date',
            'month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        // Step 1: Find the builty record
        $builty = VendorBuilty::findOrFail($id);

        // Step 2: Get the old amount
        $oldAmount = $builty->amount;

        // Step 3: Update the builty
        $builty->update([
            'vendor_id' => $request->vendor_id,
            'date' => $request->date,
            'month' => $request->month,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        // Step 4: Adjust ledger balance
        $ledger = VendorLedger::where('vendor_id', $request->vendor_id)->first();

        if ($ledger) {
            $ledger->previous_balance = $ledger->closing_balance;
            $ledger->closing_balance = ($ledger->closing_balance - $oldAmount) + $request->amount;
            $ledger->save();
        }

        return redirect()->back()->with('success', 'Vendor Builty & Ledger updated successfully.');
    }
}
