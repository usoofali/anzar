<?php

namespace App\Http\Controllers;

use App\Models\ProductionBatch;
use App\Models\RawMaterialPurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductionBatchController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $batches = ProductionBatch::with(['rawMaterialPurchase', 'producedBy'])
            ->when($search, function ($query, $s) {
                $query->where('batch_no', 'like', "%{$s}%");
            })
            ->when($status, function ($query, $st) {
                $query->where('status', $st);
            })
            ->latest('production_date')
            ->paginate(15)
            ->withQueryString();

        $batches->getCollection()->transform(function ($batch) {
            return [
                'id' => $batch->id,
                'batch_no' => $batch->batch_no,
                'production_date' => $batch->production_date->format('Y-m-d'),
                'raw_material' => $batch->rawMaterialPurchase ? $batch->rawMaterialPurchase->purchase_no.' ('.$batch->rawMaterialPurchase->quantity_kg.' KG)' : 'N/A',
                'quantity_used_kg' => (float) $batch->quantity_used_kg,
                'bags_produced' => (int) $batch->bags_produced,
                'bags_delivered' => (int) $batch->bags_delivered,
                'remaining_stock' => (int) $batch->remaining_stock,
                'produced_by_name' => $batch->producedBy ? $batch->producedBy->name : 'N/A',
                'status' => $batch->status,
                'expected_revenue' => (float) $batch->expected_revenue,
                'total_collected' => (float) $batch->total_collected,
                'outstanding_credit' => (float) $batch->outstanding_credit,
                'returned_pieces' => (int) $batch->returned_pieces,
                'replacement_issued' => (int) $batch->replacement_issued,
            ];
        });

        // Available Raw Material Purchases not yet assigned to any batch
        $availablePurchases = RawMaterialPurchase::doesntHave('productionBatch')
            ->latest()
            ->get();

        return Inertia::render('ProductionBatches/Index', [
            'batches' => $batches,
            'availablePurchases' => $availablePurchases,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'raw_material_purchase_id' => ['required', 'exists:raw_material_purchases,id', 'unique:production_batches,raw_material_purchase_id'],
            'production_date' => ['required', 'date'],
            'bags_produced' => ['required', 'integer', 'min:1'],
        ]);

        $purchase = RawMaterialPurchase::findOrFail($validated['raw_material_purchase_id']);

        $batchCount = ProductionBatch::count() + 1;
        $batchNo = 'PB-'.str_pad((string) $batchCount, 3, '0', STR_PAD_LEFT);

        ProductionBatch::create([
            'batch_no' => $batchNo,
            'raw_material_purchase_id' => $purchase->id,
            'production_date' => $validated['production_date'],
            'quantity_used_kg' => $purchase->quantity_kg,
            'bags_produced' => $validated['bags_produced'],
            'produced_by' => auth()->id(),
            'status' => 'active',
        ]);

        return back()->with('success', 'Production batch created successfully.');
    }

    public function show(ProductionBatch $productionBatch): Response
    {
        $productionBatch->load([
            'rawMaterialPurchase',
            'producedBy',
            'deliveries.customer',
            'dailyCollections.recordedBy',
            'customerDebts.customer',
            'debtPayments.customer',
            'leakageReturns.customer',
        ]);

        $summary = [
            'id' => $productionBatch->id,
            'batch_no' => $productionBatch->batch_no,
            'production_date' => $productionBatch->production_date->format('Y-m-d'),
            'quantity_used_kg' => (float) $productionBatch->quantity_used_kg,
            'bags_produced' => (int) $productionBatch->bags_produced,
            'bags_delivered' => (int) $productionBatch->bags_delivered,
            'remaining_stock' => (int) $productionBatch->remaining_stock,
            'produced_by' => $productionBatch->producedBy->name ?? 'N/A',
            'status' => $productionBatch->status,
            'expected_revenue' => (float) $productionBatch->expected_revenue,
            'cash_collected' => (float) $productionBatch->cash_collected,
            'transfer_collected' => (float) $productionBatch->transfer_collected,
            'total_collected' => (float) $productionBatch->total_collected,
            'outstanding_credit' => (float) $productionBatch->outstanding_credit,
            'returned_pieces' => (int) $productionBatch->returned_pieces,
            'replacement_issued' => (int) $productionBatch->replacement_issued,
        ];

        return Inertia::render('ProductionBatches/Show', [
            'batch' => $summary,
            'deliveries' => $productionBatch->deliveries,
            'dailyCollections' => $productionBatch->dailyCollections,
            'customerDebts' => $productionBatch->customerDebts,
            'debtPayments' => $productionBatch->debtPayments,
            'leakageReturns' => $productionBatch->leakageReturns,
        ]);
    }

    public function toggleStatus(ProductionBatch $productionBatch): RedirectResponse
    {
        if (! auth()->user()->isManager()) {
            return back()->with('error', 'Only managers can change batch status.');
        }

        $newStatus = $productionBatch->status === 'active' ? 'closed' : 'active';
        $productionBatch->update(['status' => $newStatus]);

        $message = $newStatus === 'closed' ? 'Batch closed successfully.' : 'Batch reactivated successfully.';

        return back()->with('success', $message);
    }

    public function destroy(ProductionBatch $productionBatch): RedirectResponse
    {
        if (! auth()->user()->isManager()) {
            return back()->with('error', 'Only managers can delete batches.');
        }

        if ($productionBatch->deliveries()->exists()) {
            return back()->with('error', 'Cannot delete batch with existing deliveries.');
        }

        $productionBatch->delete();

        return back()->with('success', 'Production batch deleted successfully.');
    }
}
