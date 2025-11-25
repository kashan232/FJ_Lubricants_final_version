<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use App\Models\Distributor;
use App\Models\DistributorBalanceTransfer;
use App\Models\DistributorLedger;
use App\Models\Recovery;
use App\Models\Salesman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DistributorController extends Controller
{

    public function Distributor()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $distributors = Distributor::where('admin_or_user_id', $userId)->get();
            $cities = City::where('admin_or_user_id', $userId)->get();
            return view('admin_panel.distributors.distributors', compact('distributors', 'cities'));
        } else {
            return redirect()->back();
        }
    }

    public function store_Distributor(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            // Distributor Create
            $distributor = Distributor::create([
                'admin_or_user_id' => $userId,
                'Customer' => $request->Customer,
                'Owner' => $request->owner,
                'Address' => $request->address,
                'Contact' => $request->contact,
                'City' => $request->city,
                'Area' => $request->area,
                'Email' => $request->email,
                'Password' => Hash::make($request->password),
                'created_at' => Carbon::now(),
            ]);

            // Distributor Ledger Create (One-time Opening Balance)
            DistributorLedger::create([
                'admin_or_user_id' => $userId,
                'distributor_id' => $distributor->id,
                'opening_balance' => $request->opening_balance, // Pehli dafa opening balance = previous balance
                'previous_balance' => $request->opening_balance, // Pehli dafa opening balance = previous balance
                'closing_balance' => $request->opening_balance, // Closing balance bhi initially same hoga
                'created_at' => Carbon::now(),
            ]);

            // Create Distributor User Account
            User::create([
                'user_id' => $distributor->id,
                'name' => $request->Customer,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'usertype' => 'distributor',
                'identify' => 'distributor',
            ]);

            return redirect()->back()->with('success', 'Distributor added successfully');
        } else {
            return redirect()->back();
        }
    }
    public function getDistributorLedger($id)
    {
        $ledger = DistributorLedger::where('distributor_id', $id)->first();
        return response()->json([
            'opening_balance' => $ledger ? $ledger->opening_balance : 0
        ]);
    }
    public function update_Distributor(Request $request, $id)
    {
        $request->validate([
            'Customer' => 'required',
            'owner' => 'required',
            'address' => 'required',
            'contact' => 'required',
            'city' => 'required',
            'area' => 'required',
            'recape_type' => 'required',
            'recape_opening_balance' => 'required|numeric',
        ]);

        $distributor = Distributor::find($id);
        if (!$distributor) {
            return redirect()->back()->with('error', 'Distributor not found.');
        }

        $distributor->update([
            'Customer' => $request->Customer,
            'Owner' => $request->owner,
            'Address' => $request->address,
            'Contact' => $request->contact,
            'City' => $request->city,
            'Area' => $request->area,
            'updated_at' => now(),
        ]);

        $ledger = DistributorLedger::where('distributor_id', $id)->first();

        if ($ledger) {
            $recapeType = $request->recape_type;
            $recapeAmount = $request->recape_opening_balance;

            $newOpeningBalance = ($recapeType === 'plus')
                ? $ledger->opening_balance + $recapeAmount
                : $ledger->opening_balance - $recapeAmount;

            $newClosingBalance = ($recapeType === 'plus')
                ? $ledger->closing_balance + $recapeAmount
                : $ledger->closing_balance - $recapeAmount;

            $ledger->update([
                'opening_balance' => max(0, $newOpeningBalance),
                'closing_balance' => max(0, $newClosingBalance),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Distributor updated successfully.');
    }


    public function destroy($id)
    {
        Distributor::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Distributor deleted successfully');
    }

    public function get_areas(Request $request)
    {
        if (Auth::id()) {
            // Fetch the areas based on the selected city
            $areas = Area::where('city_name', $request->city_id)
                ->pluck('area_name', 'id');  // Ensure you fetch area names and IDs

            // Return the areas in JSON format
            return response()->json($areas);
        } else {
            return redirect()->back();
        }
    }

    public function Distributor_ledger()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $user = Auth::user();
        // If logged-in user is Admin → show all ledgers
        if ($user->usertype === 'admin') {
            $DistributorLedgers = DistributorLedger::with('distributor')->get();
            $Salesmans = Salesman::where('designation', 'Saleman')->get();
        }
        // If logged-in user is Distributor → show only their own ledger
        elseif ($user->usertype === 'distributor') {

            // Step 1: find the distributor record linked to this user
            $distributor = Distributor::where('Email', $user->email)->first();
            if ($distributor) {
                // Step 2: get only the ledgers for this distributor
                $DistributorLedgers = DistributorLedger::where('distributor_id', $distributor->id)
                    ->with('distributor')
                    ->get();
            } else {
                $DistributorLedgers = collect(); // empty if not found
            }

            // Salesmen filtered by the distributor’s admin_or_user_id (optional)
            $Salesmans = Salesman::where('admin_or_user_id', $distributor->admin_or_user_id ?? null)
                ->where('designation', 'Saleman')
                ->get();
        } else {
            // Any other usertype → no access
            $DistributorLedgers = collect();
            $Salesmans = collect();
        }

        return view('admin_panel.distributors.distributors_ledger', compact('DistributorLedgers', 'Salesmans'));
    }


    public function recovery_store(Request $request)
    {
        $ledger = DistributorLedger::find($request->ledger_id);

        // ❌ Previous balance ko nahi chhedna
        // $ledger->previous_balance -= $request->amount_paid;  ❌ Remove this line

        // ✅ Sirf closing_balance ko update karna hai
        $ledger->closing_balance -= $request->amount_paid;
        $ledger->save();

        $userId = Auth::id();

        // Recovery Record Save Karna Hai
        Recovery::create([
            'admin_or_user_id' => $userId,
            'distributor_ledger_id' => $ledger->id,
            'amount_paid' => $request->amount_paid,
            'salesman' => $request->salesman,
            'date' => $request->date,
            'remarks' => $request->remarks,
        ]);

        return response()->json([
            'success' => true,
            'new_closing_balance' => number_format($ledger->closing_balance, 0)
        ]);
    }


    public function Distributor_recovery()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $user = Auth::user();

        if ($user->usertype === 'admin') {
            // ✅ Admin sees all recoveries
            $Recoveries = Recovery::with(['ledger.distributor'])->get();
            $distributors = Distributor::all(['id', 'Customer']);
        } elseif ($user->usertype === 'distributor') {
            // ✅ Distributor sees only their own recoveries
            // Step 1: Get their user_id from users table
            $user_id = $user->user_id;
            // Step 1: Get the distributor record for this logged-in user
            $distributor = Distributor::where('id', $user_id)->first();


            if ($distributor) {
                // Step 2: Fetch recoveries for this distributor directly
                $Recoveries = Recovery::where('distributor_ledger_id', $distributor->id)
                    ->with(['distributor']) // agar relation defined hai
                    ->get();

                $distributors = collect([$distributor]);
            } else {
                $Recoveries = collect();
                $distributors = collect();
            }
        } else {
            // 🚫 Any other user type
            return redirect()->back();
        }

        // ✅ Salesmans
        $Salesmans = Salesman::where('admin_or_user_id', $user->id)
            ->where('designation', 'Saleman')
            ->get();

        return view('admin_panel.distributors.distributor_recovery', compact('Recoveries', 'Salesmans', 'distributors'));
    }



    public function getDistributorLedgerbalance($id)
    {
        $ledger = DistributorLedger::where('distributor_id', $id)->first();
        return response()->json([
            'closing_balance' => $ledger ? $ledger->closing_balance : 0
        ]);
    }

    public function updateDistributorRecovery(Request $request, $id)
    {
        $request->validate([
            'salesman' => 'required',
            'date' => 'required|date',
            'adjust_type' => 'required|in:plus,minus',
            'adjust_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $recovery = Recovery::findOrFail($id);
        $ledger = DistributorLedger::find($recovery->distributor_ledger_id);

        if (!$ledger) {
            return redirect()->back()->with('error', 'Ledger record not found.');
        }

        $adjustAmount = $request->adjust_amount;

        if ($request->adjust_type === 'plus') {
            // Plus adjustment
            $new_amount_paid = $recovery->amount_paid + $adjustAmount;
            $ledger->closing_balance -= $adjustAmount;
        } else {
            // Minus adjustment
            $new_amount_paid = $recovery->amount_paid - $adjustAmount;
            $ledger->closing_balance += $adjustAmount;
        }

        // Prevent negative balances
        $new_amount_paid = max(0, $new_amount_paid);
        $ledger->closing_balance = max(0, $ledger->closing_balance);

        $ledger->save();

        $recovery->update([
            'amount_paid' => $new_amount_paid,
            'salesman' => $request->salesman,
            'remarks' => $request->description,
            'date' => $request->date,
        ]);

        return redirect()->route('Distributor-recovery')->with('success', 'Distributor recovery updated successfully.');
    }

    public function Distributor_Balance_Transfer()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            // distributor list for dropdown
            $distributors = Distributor::all(['id', 'Customer']);

            // balance transfer list for table
            $transfers = DistributorBalanceTransfer::with('toDistributor:id,Customer') // relation for 'to distributor'
                ->orderBy('transfer_date', 'desc')
                ->get();

            return view('admin_panel.distributors.Distributor_Balance_Transfer', compact('distributors', 'transfers'));
        } else {
            return redirect()->back();
        }
    }


    public function storeTransfer(Request $request)
    {
        $request->validate([
            'from_distributor' => 'required',
            'to_distributor'   => 'required',
            'amount'           => 'required',
            'transfer_date'    => 'required',
            'reason'           => 'required',
        ]);

        $userId = Auth::id();

        DB::beginTransaction();
        try {
            // 1️⃣ Find existing ledger for to_distributor
            $ledger = DistributorLedger::where('distributor_id', $request->to_distributor)->first();
            if (!$ledger) {
                throw new \Exception("Ledger record not found for distributor ID: {$request->to_distributor}");
            }

            // 2️⃣ Update closing balance
            $ledger->closing_balance = $ledger->closing_balance + $request->amount;
            $ledger->previous_balance = $ledger->previous_balance ?? 0;
            $ledger->save();

            // 3️⃣ Abhi ledger update ho gaya, abhi transfer record save karo
            DistributorBalanceTransfer::create([
                'admin_or_user_id' => $userId,
                'from_distributor' => $request->from_distributor,
                'to_distributor'   => $request->to_distributor,
                'amount'           => $request->amount,
                'transfer_date'    => $request->transfer_date,
                'reason'           => $request->reason,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Ledger updated & transfer recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    public function destroyTransfer($id)
    {
        DB::beginTransaction();
        try {
            // 1️⃣ Find transfer record
            $transfer = DistributorBalanceTransfer::findOrFail($id);

            // 2️⃣ Ledger find karo for "to distributor"
            $ledger = DistributorLedger::where('distributor_id', $transfer->to_distributor)->first();

            if (!$ledger) {
                throw new \Exception("Ledger not found for distributor ID: {$transfer->to_distributor}");
            }

            // 3️⃣ Ledger balance update (minus transfer amount)
            $ledger->closing_balance = $ledger->closing_balance - $transfer->amount;
            $ledger->save();

            // 4️⃣ Transfer record delete
            $transfer->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Transfer deleted & ledger updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        // Find transfer record
        $transfer = DistributorBalanceTransfer::findOrFail($id);

        // Old values
        $oldAmount = $transfer->amount;
        $oldTo = $transfer->to_distributor;

        // Update transfer data
        $transfer->from_distributor = $request->from_distributor;
        $transfer->to_distributor   = $request->to_distributor;
        $transfer->amount           = $request->amount;
        $transfer->transfer_date    = $request->transfer_date;
        $transfer->reason           = $request->reason;
        $transfer->save();

        // -------- Ledger Update Logic --------
        // 1. Agar distributor change ho gaya
        if ($oldTo != $request->to_distributor) {
            // Old ledger se purana amount hatao
            $oldLedger = DistributorLedger::where('distributor_id', $oldTo)->first();
            if ($oldLedger) {
                $oldLedger->closing_balance -= $oldAmount;
                $oldLedger->previous_balance = $oldLedger->closing_balance;
                $oldLedger->save();
            }

            // New ledger me naya amount add karo
            $newLedger = DistributorLedger::where('distributor_id', $request->to_distributor)->first();
            if ($newLedger) {
                $newLedger->closing_balance += $request->amount;
                $newLedger->previous_balance = $newLedger->closing_balance;
                $newLedger->save();
            }
        } else {
            // 2. Agar same distributor hai → sirf adjust karo
            $ledger = DistributorLedger::where('distributor_id', $request->to_distributor)->first();
            if ($ledger) {
                $ledger->closing_balance = ($ledger->closing_balance - $oldAmount) + $request->amount;
                $ledger->previous_balance = $ledger->closing_balance;
                $ledger->save();
            }
        }

        return redirect()->back()->with('success', 'Balance transfer updated successfully!');
    }
}
