<?php

namespace App\Http\Controllers;

use App\Models\CustomerDebt;
use App\Models\DebtPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DebtPaymentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'debt_id' => ['required', 'exists:customer_debts,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'transfer'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $debt = CustomerDebt::findOrFail($validated['debt_id']);

        if ($debt->status === 'settled') {
            return back()->with('error', 'This customer debt has already been settled.');
        }

        if ($validated['amount'] > $debt->outstanding_amount) {
            return back()->with('error', "Payment amount (₦{$validated['amount']}) exceeds outstanding debt (₦{$debt->outstanding_amount}).");
        }

        DB::transaction(function () use ($debt, $validated) {
            DebtPayment::create([
                'debt_id' => $debt->id,
                'customer_id' => $debt->customer_id,
                'batch_id' => $debt->batch_id,
                'delivery_id' => $debt->delivery_id,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'received_by' => auth()->id(),
            ]);

            $newBalance = max(0, $debt->outstanding_amount - $validated['amount']);
            $newStatus = $newBalance == 0 ? 'settled' : 'open';

            $debt->update([
                'outstanding_amount' => $newBalance,
                'status' => $newStatus,
            ]);
        });

        return back()->with('success', 'Debt payment recorded successfully.');
    }

    public function destroy(DebtPayment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment) {
            $debt = $payment->debt;

            // Revert debt balance
            $revertedBalance = $debt->outstanding_amount + $payment->amount;
            $debt->update([
                'outstanding_amount' => $revertedBalance,
                'status' => 'open',
            ]);

            $payment->delete();
        });

        return back()->with('success', 'Debt payment deleted and balance reverted.');
    }
}
