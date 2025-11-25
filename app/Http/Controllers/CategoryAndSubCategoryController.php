<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryAndSubCategoryController extends Controller
{

    public function category()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Categories = Category::all(); // Adjust according to your database structure

            return view('admin_panel.Categories.Categories', [
                'Categories' => $Categories,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_category(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            Category::create([
                'admin_or_user_id'    => $userId,
                'category_name'          => $request->category_name,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'Category created successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update_category(Request $request)
    {
        // Get the cloth type ID from the request
        $Category_id = $request->input('Category_id');
        // dd($Category_id);

        // Update the cloth type in the database
        Category::where('id', $Category_id)->update([
            'category_name' => $request->category_name,
        ]);

        return redirect()->back()->with('success', 'Category updated successfully');
    }


    public function sub_category()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Categories = Category::all(); // Adjust according to your database structure
            $Sub_Categories = SubCategory::all(); // Adjust according to your database structure

            return view('admin_panel.Sub_Categories.Sub_Categories', [
                'Categories' => $Categories,
                'Sub_Categories' => $Sub_Categories,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_sub_category(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            SubCategory::create([
                'admin_or_user_id'    => $userId,
                'category_name'          => $request->category_name,
                'sub_category_name'          => $request->sub_category_name,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'SubCategory created successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update_sub_category(Request $request)
    {
        // Get the cloth type ID from the request
        $sub_category_id = $request->input('sub_category_id');
        // dd($sub_category_id);

        // Update the cloth type in the database
        SubCategory::where('id', $sub_category_id)->update([
            'category_name'          => $request->category_name,
            'sub_category_name'          => $request->sub_category_name,
        ]);

        return redirect()->back()->with('success', 'SubCategory updated successfully');
    }
}
