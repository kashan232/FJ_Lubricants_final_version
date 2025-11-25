<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CityAndAreaController extends Controller
{
    public function city()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Citys = City::where('admin_or_user_id', $userId)->get();
            return view('admin_panel.city.cities', [
                'Citys' => $Citys,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_city(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            City::create([
                'admin_or_user_id'    => $userId,
                'city_name'          => $request->city_name,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'City created successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        // Get the cloth type ID from the request
        $city_id = $request->input('city_id');
        // dd($city_id);

        // Update the cloth type in the database
        City::where('id', $city_id)->update([
            'city_name' => $request->city_name,
        ]);

        return redirect()->back()->with('success', 'City updated successfully');
    }


    public function Area()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $userId = Auth::id();
            $Citys = City::where('admin_or_user_id', $userId)->get();
            $Areas = Area::where('admin_or_user_id', $userId)->get();
            
            return view('admin_panel.areas.areas', [
                'Citys' => $Citys,
                'Areas' => $Areas,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_Area(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            Area::create([
                'admin_or_user_id'    => $userId,
                'city_name'          => $request->city_name,
                'area_name'          => $request->area_name,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'Area created successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update_area(Request $request)
    {
        // Get the cloth type ID from the request
        $area_id = $request->input('area_id');
        // dd($area_id);

        // Update the cloth type in the database
        Area::where('id', $area_id)->update([
            'city_name'          => $request->city_name,
            'area_name'          => $request->area_name,
        ]);

        return redirect()->back()->with('success', 'Area updated successfully');
    }
}
