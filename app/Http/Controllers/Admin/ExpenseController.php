<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display the expense management page.
     */
    public function index()
    {
        $expenses = Expense::with('creator')
            ->orderByDesc('spent_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $totalExpenses = Expense::sum('amount');

        $todayExpenses = Expense::whereDate('spent_at', today())
            ->sum('amount');

        return view('admin.expenses.index', compact(
            'expenses',
            'totalExpenses',
            'todayExpenses'
        ));
    }

    /**
     * Store a new business expense.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:99999999.99',
            ],
            'spent_at' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $validated['created_by'] = $request->user()->id;

        Expense::create($validated);

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }
}
