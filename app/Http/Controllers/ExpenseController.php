<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\AddExpense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function expense()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $expenses = Expense::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.expense.expenses', [
                'expenses' => $expenses,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_expense_category(Request $request)
    {
        if (Auth::id()) {

            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();

            Expense::create([
                'admin_or_user_id' => $userId,
                'expense_category' => $request->expense_category, // Ensure the input name matches
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Expense created successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        $expense_id = $request->input('expense_id');

        Expense::where('id', $expense_id)->update([
            'expense_category' => $request->expense_category,
        ]);

        return redirect()->back()->with('success', 'Expense updated successfully');
    }

    public function delete_Add_ExpenseBtn($id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found.'], 404);
        }

        $expense->delete();

        return response()->json(['success' => 'Expense deleted successfully.']);
    }


    public function addExpenseScreen()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            // Expense categories fetch karna
            $expenseCategories = Expense::all();

            // Sare expenses fetch karna
            $expenses = AddExpense::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.expense.add_expenses', [
                'expenseCategories' => $expenseCategories,
                'expenses' => $expenses,
            ]);
        } else {
            return redirect()->back();
        }
    }


    public function store_addexpense(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            AddExpense::create([
                'admin_or_user_id' => $userId,
                'expense_category' => $request->expense_category,
                'title' => $request->title,
                'amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Expense added successfully');
        } else {
            return redirect()->back();
        }
    }

    // ✅ Update Expense Function (Added)
    public function update_addexpense(Request $request)
    {
        $request->validate([
            'expense_id' => 'required|exists:add_expenses,id',
            'expense_category' => 'required',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $expense = AddExpense::findOrFail($request->expense_id);
        $expense->update([
            'expense_category' => $request->expense_category,
            'title' => $request->title,
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Expense updated successfully.');
    }
    public function delete_add_expense($id)
    {
        $expense = AddExpense::find($id);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found.'], 404);
        }

        $expense->delete();

        return response()->json(['success' => 'Expense deleted successfully.']);
    }
}
