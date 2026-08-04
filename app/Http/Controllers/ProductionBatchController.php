<?php

namespace App\Http\Controllers;

use App\Models\BatchProduction;
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
            'production_time' => ['required', 'string', 'in:morning,afternoon,evening,night'],
            'bags_produced' => ['required', 'integer', 'min:1'],
        ]);

        $purchase = RawMaterialPurchase::findOrFail($validated['raw_material_purchase_id']);

        if ($validated['bags_produced'] > $purchase->packing_nylon_pieces) {
            return back()->withErrors(['bags_produced' => 'Bags produced cannot exceed purchased packing nylon pieces ('.$purchase->packing_nylon_pieces.')']);
        }

        // Proportional nylon calculation
        $nylonUsed = $purchase->packing_nylon_pieces > 0
            ? $validated['bags_produced'] * ($purchase->quantity_kg / $purchase->packing_nylon_pieces)
            : 0;

        $batchCount = ProductionBatch::count() + 1;
        $batchNo = 'PB-'.str_pad((string) $batchCount, 3, '0', STR_PAD_LEFT);

        $batch = ProductionBatch::create([
            'batch_no' => $batchNo,
            'raw_material_purchase_id' => $purchase->id,
            'production_date' => $validated['production_date'],
            'quantity_used_kg' => $nylonUsed,
            'bags_produced' => $validated['bags_produced'],
            'produced_by' => auth()->id(),
            'status' => 'active',
        ]);

        // Create the first daily production run record
        $batch->batchProductions()->create([
            'production_date' => $validated['production_date'],
            'production_time' => $validated['production_time'],
            'nylon_used_kg' => $nylonUsed,
            'packing_nylon_used' => $validated['bags_produced'],
            'bags_produced' => $validated['bags_produced'],
            'produced_by' => auth()->id(),
            'remarks' => 'Initial production run',
        ]);

        return back()->with('success', 'Production batch created successfully.');
    }

    public function show(ProductionBatch $productionBatch): Response
    {
        $productionBatch->load([
            'rawMaterialPurchase',
            'producedBy',
            'batchProductions.producedBy',
            'deliveries.customer',
            'dailyCollections.recordedBy',
            'customerDebts.customer',
            'debtPayments.customer',
            'leakageReturns.customer',
        ]);

        $nylonCost = (float) ($productionBatch->rawMaterialPurchase->total_cost ?? 0);
        $unitPricePerKg = (float) ($productionBatch->rawMaterialPurchase->unit_price ?? 0);
        $bagsProduced = (int) $productionBatch->bags_produced;
        $bagsDelivered = (int) $productionBatch->bags_delivered;
        $costPerBag = $bagsProduced > 0 ? $nylonCost / $bagsProduced : 0;
        $expectedRevenue = (float) $productionBatch->expected_revenue;
        $totalCollected = (float) $productionBatch->total_collected;
        $returnedPieces = (int) $productionBatch->returned_pieces;

        // Average bag price & leakage loss value (20 sachet pieces per bag)
        $avgPricePerBag = $bagsDelivered > 0 ? ($expectedRevenue / $bagsDelivered) : 0;
        $pricePerPiece = $avgPricePerBag > 0 ? ($avgPricePerBag / 20) : 0;
        $leakageLossValue = round($returnedPieces * $pricePerPiece, 2);

        $grossProfit = $expectedRevenue - $nylonCost;
        $netProfitAfterLeakage = $grossProfit - $leakageLossValue;
        $realizedCashProfit = $totalCollected - $nylonCost;
        $profitMarginPercent = $expectedRevenue > 0 ? round(($netProfitAfterLeakage / $expectedRevenue) * 100, 1) : 0;

        // Calculate remaining capacities for packing nylon & roll nylon
        $totalNylonUsed = (float) $productionBatch->batchProductions()->sum('nylon_used_kg');
        $totalPackingUsed = (int) $productionBatch->batchProductions()->sum('packing_nylon_used');
        $remainingNylon = max(0, ($productionBatch->rawMaterialPurchase->quantity_kg ?? 0) - $totalNylonUsed);
        $remainingPacking = max(0, ($productionBatch->rawMaterialPurchase->packing_nylon_pieces ?? 0) - $totalPackingUsed);

        $summary = [
            'id' => $productionBatch->id,
            'batch_no' => $productionBatch->batch_no,
            'production_date' => $productionBatch->production_date->format('Y-m-d'),
            'raw_material_supplier' => $productionBatch->rawMaterialPurchase->supplier ?? 'N/A',
            'raw_material_purchase_no' => $productionBatch->rawMaterialPurchase->purchase_no ?? 'N/A',
            'quantity_used_kg' => (float) $productionBatch->quantity_used_kg,
            'unit_price_per_kg' => $unitPricePerKg,
            'nylon_cost' => $nylonCost,
            'cost_per_bag' => round($costPerBag, 2),
            'bags_produced' => $bagsProduced,
            'bags_delivered' => $bagsDelivered,
            'remaining_stock' => (int) $productionBatch->remaining_stock,
            'produced_by' => $productionBatch->producedBy->name ?? 'N/A',
            'status' => $productionBatch->status,
            'expected_revenue' => $expectedRevenue,
            'cash_collected' => (float) $productionBatch->cash_collected,
            'transfer_collected' => (float) $productionBatch->transfer_collected,
            'total_collected' => $totalCollected,
            'outstanding_credit' => (float) $productionBatch->outstanding_credit,
            'returned_pieces' => $returnedPieces,
            'replacement_issued' => (int) $productionBatch->replacement_issued,
            'leakage_loss_value' => $leakageLossValue,
            'gross_profit' => $grossProfit,
            'net_profit_after_leakage' => $netProfitAfterLeakage,
            'realized_cash_profit' => $realizedCashProfit,
            'profit_margin_percent' => $profitMarginPercent,
            'remaining_nylon_kg' => $remainingNylon,
            'remaining_packing_pieces' => $remainingPacking,
        ];

        // Format batch productions with user details for client
        $batchProductionsFormatted = $productionBatch->batchProductions->map(function ($prod) {
            return [
                'id' => $prod->id,
                'production_date' => $prod->production_date->format('Y-m-d'),
                'production_time' => $prod->production_time,
                'nylon_used_kg' => (float) $prod->nylon_used_kg,
                'packing_nylon_used' => (int) $prod->packing_nylon_used,
                'bags_produced' => (int) $prod->bags_produced,
                'produced_by_name' => $prod->producedBy->name ?? 'N/A',
                'remarks' => $prod->remarks,
            ];
        });

        return Inertia::render('ProductionBatches/Show', [
            'batch' => $summary,
            'deliveries' => $productionBatch->deliveries,
            'dailyCollections' => $productionBatch->dailyCollections,
            'customerDebts' => $productionBatch->customerDebts,
            'debtPayments' => $productionBatch->debtPayments,
            'leakageReturns' => $productionBatch->leakageReturns,
            'batchProductions' => $batchProductionsFormatted,
        ]);
    }

    public function storeProduction(Request $request, ProductionBatch $productionBatch): RedirectResponse
    {
        if ($productionBatch->status === 'closed') {
            return back()->with('error', 'Cannot record production for a closed batch.');
        }

        $purchase = $productionBatch->rawMaterialPurchase;

        $totalPackingUsed = (int) $productionBatch->batchProductions()->sum('packing_nylon_used');
        $remainingPacking = max(0, $purchase->packing_nylon_pieces - $totalPackingUsed);

        $validated = $request->validate([
            'production_date' => ['required', 'date'],
            'production_time' => ['required', 'string', 'in:morning,afternoon,evening,night'],
            'bags_produced' => ['required', 'integer', 'min:1', 'max:'.$remainingPacking],
            'remarks' => ['nullable', 'string'],
        ]);

        // Proportional nylon calculation
        $nylonUsed = $purchase->packing_nylon_pieces > 0
            ? $validated['bags_produced'] * ($purchase->quantity_kg / $purchase->packing_nylon_pieces)
            : 0;

        $productionBatch->batchProductions()->create([
            'production_date' => $validated['production_date'],
            'production_time' => $validated['production_time'],
            'nylon_used_kg' => $nylonUsed,
            'packing_nylon_used' => $validated['bags_produced'],
            'bags_produced' => $validated['bags_produced'],
            'produced_by' => auth()->id(),
            'remarks' => $validated['remarks'],
        ]);

        $productionBatch->updateAggregates();

        return back()->with('success', 'Production run logged successfully.');
    }

    public function destroyProduction(ProductionBatch $productionBatch, BatchProduction $batchProduction): RedirectResponse
    {
        if ($productionBatch->status === 'closed') {
            return back()->with('error', 'Cannot modify a closed batch.');
        }

        $batchProduction->delete();
        $productionBatch->updateAggregates();

        return back()->with('success', 'Production run deleted successfully.');
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
