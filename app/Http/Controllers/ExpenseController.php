<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $category = $request->input('category');
        $date = $request->input('date');

        $expenses = Expense::with('recordedBy')
            ->when($search, function ($query, $s) {
                $query->where('description', 'like', "%{$s}%")
                    ->orWhere('category', 'like', "%{$s}%");
            })
            ->when($category, function ($query, $c) {
                $query->where('category', $c);
            })
            ->when($date, function ($query, $d) {
                $query->whereDate('expense_date', $d);
            })
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'categories' => Expense::CATEGORIES,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'date' => $date,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'string', Rule::in(Expense::CATEGORIES)],
            'description' => ['nullable', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        Expense::create([
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Expense recorded successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('success', 'Expense deleted successfully.');
    }
}
