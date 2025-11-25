<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateBillController extends Controller
{
     public function create_bill()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            return view('admin_panel.Create_bills.add_bill');
        } else {
            return redirect()->back();
        }
    }
}
