<?php

namespace App\Http\Controllers;

use App\Models\DailyCollection;
use App\Models\ProductionBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DailyCollectionController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $batchId = $request->input('batch_id');
        $date = $request->input('date');

        $collections = DailyCollection::with(['batch', 'recordedBy'])
            ->when($search, function ($query, $s) {
                $query->whereHas('batch', function ($q) use ($s) {
                    $q->where('batch_no', 'like', "%{$s}%");
                });
            })
            ->when($batchId, function ($query, $bId) {
                $query->where('batch_id', $bId);
            })
            ->when($date, function ($query, $d) {
                $query->whereDate('collection_date', $d);
            })
            ->latest('collection_date')
            ->paginate(15)
            ->withQueryString();

        $collections->getCollection()->transform(function ($collection) {
            $collection->total_collection = $collection->total_collection;

            return $collection;
        });

        $batches = ProductionBatch::latest()->get();

        return Inertia::render('DailyCollections/Index', [
            'collections' => $collections,
            'batches' => $batches,
            'filters' => [
                'search' => $search,
                'batch_id' => $batchId,
                'date' => $date,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'exists:production_batches,id'],
            'collection_date' => ['required', 'date'],
            'cash_amount' => ['required', 'numeric', 'min:0'],
            'transfer_amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ($validated['cash_amount'] == 0 && $validated['transfer_amount'] == 0) {
            return back()->with('error', 'Collection amount must be greater than zero.');
        }

        DailyCollection::create([
            'batch_id' => $validated['batch_id'],
            'collection_date' => $validated['collection_date'],
            'cash_amount' => $validated['cash_amount'],
            'transfer_amount' => $validated['transfer_amount'],
            'recorded_by' => auth()->id(),
            'remarks' => $validated['remarks'],
        ]);

        return back()->with('success', 'Daily collection recorded successfully.');
    }

    public function destroy(DailyCollection $dailyCollection): RedirectResponse
    {
        $dailyCollection->delete();

        return back()->with('success', 'Daily collection deleted successfully.');
    }
}
