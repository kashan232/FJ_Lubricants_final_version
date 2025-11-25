<?php

namespace App\Http\Controllers;

use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Business_tpyeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $businessTypes = BusinessType::where('admin_or_user_id', $userId)->get();
            return view('admin_panel.business.business_type', [
                'businessTypes' => $businessTypes,
            ]);
        } else {
            return redirect()->route('login');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_type_name' => 'required|string|max:255',
        ]);

        BusinessType::create([
            'admin_or_user_id'   => Auth::id(),
            'business_type_name' => $request->business_type_name,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return redirect()->back()->with('success', 'Business Type created successfully.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'business_type_id'   => 'required|exists:business_types,id',
            'business_type_name' => 'required|string|max:255',
        ]);

        BusinessType::where('id', $request->business_type_id)->update([
            'business_type_name' => $request->business_type_name,
            'updated_at'         => now(),
        ]);

        return redirect()->back()->with('success', 'Business Type updated successfully.');
    }
}

