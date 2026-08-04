<?php

namespace App\Http\Controllers;

use App\Models\RawMaterialPurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RawMaterialPurchaseController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $purchases = RawMaterialPurchase::with('productionBatch')
            ->when($search, function ($query, $s) {
                $query->where('purchase_no', 'like', "%{$s}%");
            })
            ->latest('purchase_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('RawMaterials/Index', [
            'purchases' => $purchases,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier' => ['nullable', 'string'],
            'purchase_date' => ['required', 'date'],
            'quantity_kg' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'packing_nylon_pieces' => ['required', 'integer', 'min:0'],
            'packing_unit_price' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $count = RawMaterialPurchase::count() + 1;
        $validated['purchase_no'] = 'RMP-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
        $validated['total_cost'] = ($validated['quantity_kg'] * $validated['unit_price']) + ($validated['packing_nylon_pieces'] * $validated['packing_unit_price']);

        RawMaterialPurchase::create($validated);

        return back()->with('success', 'Raw material purchase recorded successfully.');
    }

    public function destroy(RawMaterialPurchase $rawMaterialPurchase): RedirectResponse
    {
        $batch = $rawMaterialPurchase->productionBatch;

        if ($batch) {
            if ($batch->deliveries()->exists()) {
                return back()->with('error', "Cannot delete purchase {$rawMaterialPurchase->purchase_no}: linked batch {$batch->batch_no} already has recorded deliveries.");
            }
            $batch->delete();
        }

        $rawMaterialPurchase->delete();

        return back()->with('success', "Raw material purchase {$rawMaterialPurchase->purchase_no} deleted successfully.");
    }
}
