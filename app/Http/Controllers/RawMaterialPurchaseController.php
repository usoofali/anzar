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
            'purchase_date' => ['required', 'date'],
            'quantity_kg' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $count = RawMaterialPurchase::count() + 1;
        $validated['purchase_no'] = 'RMP-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
        $validated['total_cost'] = $validated['quantity_kg'] * $validated['unit_price'];

        RawMaterialPurchase::create($validated);

        return back()->with('success', 'Raw material purchase recorded successfully.');
    }

    public function destroy(RawMaterialPurchase $purchase): RedirectResponse
    {
        if ($purchase->productionBatch()->exists()) {
            return back()->with('error', 'Cannot delete purchase that is already linked to a production batch.');
        }

        $purchase->delete();

        return back()->with('success', 'Raw material purchase deleted successfully.');
    }
}
