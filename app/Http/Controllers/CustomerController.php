<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\City;
use App\Models\Area;
use App\Models\BusinessType;
use App\Models\CustomerLedger;
use App\Models\CustomerRecovery;
use App\Models\Salesman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $authUser = Auth::user();
            $userId = Auth::id();

            if ($authUser->usertype === 'salesman') {
                // Logged-in salesman
                $salesman = $authUser; // direct Auth user hi salesman hai
                $ownerIdentify = $salesman->identify; // admin identify (jaise "admin")

                // Admin user find karo
                $admin = User::where('usertype', 'admin')
                    ->where('identify', $ownerIdentify)
                    ->first();

                if (!$admin) {
                    return redirect()->back()->with('error', 'Admin not found for this salesman.');
                }

                // Customers: admin ke bhi + salesman ke bhi
                $customers = Customer::whereIn('admin_or_user_id', [$admin->id, $salesman->id])->get();
            } else {
                // If admin
                $admin = $authUser;

                // Apne salesmen ki IDs nikaalo
                $salesmanIds = User::where('usertype', 'salesman')
                    ->where('identify', $admin->identify)
                    ->pluck('id');

                // Customers: admin ke bhi + salesmen ke bhi
                $customers = Customer::where(function ($q) use ($admin, $salesmanIds) {
                    $q->where('admin_or_user_id', $admin->id)
                        ->orWhereIn('admin_or_user_id', $salesmanIds);
                })->get();
            }

            $cities = City::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.customer.customer', compact('customers', 'cities'));
        } else {
            return redirect()->back();
        }
    }





    public function fetchAreas(Request $request)
    {
        $areas = Area::where('city_name', $request->city_id)->get();
        return response()->json($areas);
    }

    public function fetch_areas_report(Request $request)
    {
        $cities = (array) $request->input('cities'); // hamesha array bana do

        $areas = Area::whereIn('city_name', $cities)
            ->get(['city_name as city', 'area_name as area']);

        return response()->json($areas);
    }


    public function store(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $user = Auth::user();

            $customer = Customer::create([
                'admin_or_user_id' => $userId,
                'identify' => $user->identify,
                'city' => $request->city,
                'area' => $request->area,
                'customer_name' => $request->customer_name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'shop_name' => $request->shop_name,
                'business_type_name' => $request->business_type_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Distributor Ledger Create (One-time Opening Balance)
            CustomerLedger::create([
                'admin_or_user_id' => $userId,
                'customer_id' => $customer->id,
                'opening_balance' => $request->opening_balance, // Pehli dafa opening balance = previous balance
                'previous_balance' => $request->opening_balance, // Pehli dafa opening balance = previous balance
                'closing_balance' => $request->opening_balance, // Closing balance bhi initially same hoga
                'created_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Customer created successfully');
        } else {
            return redirect()->back();
        }
    }


    public function customer_ledger()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();
        $userType = $authUser->usertype; // admin / distributor / salesman
        $userIdentify = $authUser->identify; // 'admin' / 'distributor'
        $userName = $authUser->name;

        if ($userType === 'salesman') {
            // Salesman case: get owner/admin ID
            $salesman = Salesman::where('name', $userName)->first();

            if (!$salesman) {
                return redirect()->back()->with('error', 'Salesman not found.');
            }

            $ownerId = $salesman->admin_or_user_id;

            // Ledger data filtered by admin id and same identify
            $CustomerLedgers = CustomerLedger::where('admin_or_user_id', $ownerId)
                ->whereHas('Customer', function ($query) use ($userIdentify) {
                    $query->where('identify', $userIdentify);
                })
                ->with('Customer')
                ->get();

            // Salesmen list for this owner
            $Salesmans = Salesman::where('admin_or_user_id', $ownerId)
                ->where('designation', 'Saleman')
                ->get();
        } else {
            // Admin or distributor
            $ownerId = $authUser->id;

            $CustomerLedgers = CustomerLedger::where('admin_or_user_id', $ownerId)
                ->with('Customer')
                ->get();

            $Salesmans = Salesman::where('admin_or_user_id', $ownerId)
                ->where('designation', 'Saleman')
                ->get();
        }

        return view('admin_panel.customer.customer_ledger', compact('CustomerLedgers', 'Salesmans'));
    }



    public function customer_recovery_store(Request $request)
    {
        $ledger = CustomerLedger::find($request->ledger_id);
        $ledger->previous_balance -= $request->amount_paid;
        $ledger->closing_balance -= $request->amount_paid;
        $ledger->save();

        $userId = Auth::id();

        // Store recovery record (Optional)
        CustomerRecovery::create([
            'admin_or_user_id' => $userId,
            'customer_ledger_id' => $ledger->id,
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

    public function customer_recovery()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();
        if ($authUser->usertype === 'salesman') {
            // Match salesman via user_id instead of name
            $salesman = Salesman::where('id', $authUser->user_id)->first();
            if (!$salesman) {
                return redirect()->back()->with('error', 'Salesman not found.');
            }

            $ownerId = $salesman->name;
            // Fetch recoveries only by this salesman
            $Recoveries = CustomerRecovery::where('salesman', $salesman->name)
                ->with('customer')
                ->get();

            $Salesmans = collect([$salesman]);
        } else {
            $ownerId = $authUser->id;

            $Recoveries = CustomerRecovery::where('admin_or_user_id', $ownerId)
                ->with('customer')
                ->get();

            $Salesmans = Salesman::where('admin_or_user_id', $ownerId)
                ->where('designation', 'Saleman')
                ->get();
        }

        return view('admin_panel.customer.customer_recovery', compact('Recoveries', 'Salesmans'));
    }




    public function getCustomerData($id)
    {
        $customer = Customer::findOrFail($id);
        $ledger = CustomerLedger::where('customer_id', $id)->first();
        $businessTypes = BusinessType::all();
        $response = [
            'id' => $customer->id,
            'customer_name' => $customer->customer_name,
            'phone_number' => $customer->phone_number,
            'city' => $customer->city,
            'area' => $customer->area,
            'address' => $customer->address,
            'shop_name' => $customer->shop_name,
            'business_type_name' => $customer->business_type_name,
            'ledger' => $ledger,
            'business_types' => $businessTypes
        ];

        return response()->json($response);
    }


    public function update(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);

        $customer->update([
            'customer_name' => $request->customer_name,
            'phone_number' => $request->phone_number,
            'city' => $request->city,
            'area' => $request->area,
            'address' => $request->address,
            'shop_name' => $request->shop_name,
            'business_type_name' => $request->business_type_name,
        ]);

        $ledger = CustomerLedger::where('customer_id', $request->customer_id)->first();
        $recapeAmount = $request->recape_opening;
        $recapeType = $request->recape_type;

        if ($ledger) {
            if ($recapeType === "plus") {
                $ledger->opening_balance += $recapeAmount;
            } elseif ($recapeType === "minus") {
                $ledger->opening_balance -= $recapeAmount;
            }

            $ledger->previous_balance = $ledger->closing_balance;
            $ledger->closing_balance = $ledger->opening_balance;
            $ledger->save();
        } else {
            CustomerLedger::create([
                'customer_id' => $request->customer_id,
                'opening_balance' => $request->recape_opening ?? 0,
                'previous_balance' => 0,
                'closing_balance' => $request->recape_opening ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Customer updated successfully');
    }


    // public function destroy($id)
    // {
    //     Customer::findOrFail($id)->delete();
    //     return response()->json(['success' => 'Customer deleted successfully']);
    // }
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Customer not found.'], 404);
        }

        $customer->delete();

        return response()->json(['status' => 'success', 'message' => 'Customer deleted successfully.']);
    }


    public function fetchBusinessTypes()
    {
        $userId = Auth::id();
        return response()->json(BusinessType::where('admin_or_user_id', $userId)->get());
    }

    public function getCities()
    {
        $cities = City::select('id', 'city_name')->get();
        return response()->json($cities);
    }

    public function getAreas(Request $request)
    {
        $areas = Area::where('city_name', $request->city)
            ->select('id', 'area_name')
            ->get();

        return response()->json($areas);
    }

    public function updateRecovery(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'adjust_type' => 'required|in:plus,minus',
            'adjust_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $recovery = CustomerRecovery::findOrFail($id);
        $ledger = CustomerLedger::find($recovery->customer_ledger_id);

        if (!$ledger) {
            return response()->json(['message' => 'Ledger record not found.'], 404);
        }

        $adjustAmount = $request->adjust_amount;

        if ($request->adjust_type === 'plus') {
            $new_amount_paid = $recovery->amount_paid + $adjustAmount;
            $ledger->closing_balance -= $adjustAmount;  // reduce ledger balance
        } else {
            $new_amount_paid = $recovery->amount_paid - $adjustAmount;
            $ledger->closing_balance += $adjustAmount;  // increase ledger balance
        }

        // Ensure no negative values
        $new_amount_paid = max(0, $new_amount_paid);
        $ledger->closing_balance = max(0, $ledger->closing_balance);

        $ledger->save();

        $recovery->update([
            'amount_paid' => $new_amount_paid,
            'remarks' => $request->remarks,
            'date' => $request->date,
        ]);

        return redirect()->route('customer-recovery')->with('success', 'Distributor recovery updated successfully.');
    }
}
