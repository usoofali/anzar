<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\Delivery;
use App\Models\ProductionBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $batchId = $request->input('batch_id');
        $customerId = $request->input('customer_id');

        $deliveries = Delivery::with(['batch', 'customer', 'deliveredBy'])
            ->when($search, function ($query, $s) {
                $query->where('delivery_no', 'like', "%{$s}%")
                    ->orWhereHas('customer', function ($q) use ($s) {
                        $q->where('shop_name', 'like', "%{$s}%");
                    })
                    ->orWhereHas('batch', function ($q) use ($s) {
                        $q->where('batch_no', 'like', "%{$s}%");
                    });
            })
            ->when($batchId, function ($query, $bId) {
                $query->where('batch_id', $bId);
            })
            ->when($customerId, function ($query, $cId) {
                $query->where('customer_id', $cId);
            })
            ->latest('delivery_date')
            ->paginate(15)
            ->withQueryString();

        $activeBatches = ProductionBatch::where('status', 'active')->latest()->get();
        $customers = Customer::where('status', 'active')->orderBy('shop_name')->get();

        return Inertia::render('Deliveries/Index', [
            'deliveries' => $deliveries,
            'activeBatches' => $activeBatches,
            'customers' => $customers,
            'filters' => [
                'search' => $search,
                'batch_id' => $batchId,
                'customer_id' => $customerId,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'exists:production_batches,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'delivery_date' => ['required', 'date'],
            'bags_delivered' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $batch = ProductionBatch::findOrFail($validated['batch_id']);

        if ($batch->status !== 'active') {
            return back()->with('error', 'Deliveries can only be recorded for active production batches.');
        }

        if ($validated['bags_delivered'] > $batch->remaining_stock) {
            return back()->with('error', "Insufficient stock in batch {$batch->batch_no}. Remaining stock is {$batch->remaining_stock} bags.");
        }

        $totalAmount = $validated['bags_delivered'] * $validated['unit_price'];

        if ($validated['paid_amount'] > $totalAmount) {
            return back()->with('error', 'Paid amount cannot exceed total delivery amount.');
        }

        DB::transaction(function () use ($validated, $totalAmount, $batch) {
            $deliveryCount = Delivery::count() + 1;
            $deliveryNo = 'DEL-'.str_pad((string) $deliveryCount, 3, '0', STR_PAD_LEFT);

            $delivery = Delivery::create([
                'delivery_no' => $deliveryNo,
                'batch_id' => $batch->id,
                'customer_id' => $validated['customer_id'],
                'delivery_date' => $validated['delivery_date'],
                'bags_delivered' => $validated['bags_delivered'],
                'unit_price' => $validated['unit_price'],
                'total_amount' => $totalAmount,
                'paid_amount' => $validated['paid_amount'],
                'delivered_by' => auth()->id(),
            ]);

            $outstandingAmount = $totalAmount - $validated['paid_amount'];

            if ($outstandingAmount > 0) {
                CustomerDebt::create([
                    'delivery_id' => $delivery->id,
                    'customer_id' => $validated['customer_id'],
                    'batch_id' => $batch->id,
                    'outstanding_amount' => $outstandingAmount,
                    'status' => 'open',
                ]);
            }
        });

        return back()->with('success', 'Delivery recorded successfully.');
    }

    public function destroy(Delivery $delivery): RedirectResponse
    {
        if ($delivery->debts()->whereHas('payments')->exists()) {
            return back()->with('error', 'Cannot delete delivery that has associated debt payments.');
        }

        $delivery->delete();

        return back()->with('success', 'Delivery deleted successfully.');
    }
}
