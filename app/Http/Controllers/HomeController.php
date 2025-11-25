<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
         if(Auth::id())
         {
            $usertype =Auth()->user()->usertype;
            $userId = Auth::id();
            if($usertype=='distributor')
            {
                return view('distributor_panel.dashboard', [
                    'userId' => $userId,
                ]);
            }else if($usertype=='admin')
            {
                return view('admin_panel.dashboard', [
                    'userId' => $userId,
                ]);
            }else if($usertype=='salesman')
            {
                return view('salesman_panel.dashboard', [
                    'userId' => $userId,
                ]);
            } 

            else
            {
                return redirect()->back(); 
            }
         }
    }
}
