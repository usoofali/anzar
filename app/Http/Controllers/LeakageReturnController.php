<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\LeakageReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeakageReturnController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $batchId = $request->input('batch_id');

        $returns = LeakageReturn::with(['delivery', 'batch', 'customer'])
            ->when($search, function ($query, $s) {
                $query->whereHas('customer', function ($q) use ($s) {
                    $q->where('shop_name', 'like', "%{$s}%");
                })->orWhereHas('batch', function ($q) use ($s) {
                    $q->where('batch_no', 'like', "%{$s}%");
                });
            })
            ->when($batchId, function ($query, $bId) {
                $query->where('batch_id', $bId);
            })
            ->latest('date')
            ->paginate(15)
            ->withQueryString();

        $deliveries = Delivery::with(['customer', 'batch'])
            ->latest('delivery_date')
            ->take(50)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'delivery_no' => $d->delivery_no,
                'customer_name' => $d->customer->shop_name ?? 'N/A',
                'batch_no' => $d->batch->batch_no ?? 'N/A',
            ]);

        return Inertia::render('LeakageReturns/Index', [
            'leakageReturns' => $returns,
            'recentDeliveries' => $deliveries,
            'filters' => [
                'search' => $search,
                'batch_id' => $batchId,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_id' => ['required', 'exists:deliveries,id'],
            'date' => ['required', 'date'],
            'returned_pieces' => ['required', 'integer', 'min:1'],
            'replacement_issued' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $delivery = Delivery::findOrFail($validated['delivery_id']);

        LeakageReturn::create([
            'delivery_id' => $delivery->id,
            'batch_id' => $delivery->batch_id,
            'customer_id' => $delivery->customer_id,
            'date' => $validated['date'],
            'returned_pieces' => $validated['returned_pieces'],
            'replacement_issued' => $validated['replacement_issued'],
            'remarks' => $validated['remarks'],
        ]);

        return back()->with('success', 'Leakage return recorded successfully.');
    }

    public function destroy(LeakageReturn $leakageReturn): RedirectResponse
    {
        $leakageReturn->delete();

        return back()->with('success', 'Leakage return deleted successfully.');
    }
}
