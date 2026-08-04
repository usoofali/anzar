<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $customers = Customer::query()
            ->when($search, function ($query, $s) {
                $query->where('shop_name', 'like', "%{$s}%")
                    ->orWhere('owner_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            })
            ->when($status, function ($query, $st) {
                $query->where('status', $st);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Calculate outstanding balances for each customer
        $customers->getCollection()->transform(function ($customer) {
            $customer->outstanding_balance = $customer->outstanding_balance;

            return $customer;
        });

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);

        Customer::create($validated);

        return back()->with('success', 'Customer created successfully.');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);

        $customer->update($validated);

        return back()->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->debts()->where('status', 'open')->exists()) {
            return back()->with('error', 'Cannot delete customer with open debts.');
        }

        $customer->delete();

        return back()->with('success', 'Customer deleted successfully.');
    }
}
