<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\DailyCollection;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\LeakageReturn;
use App\Models\ProductionBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $reportType = $request->input('type', 'batch_performance');
        $batchId = $request->input('batch_id');
        $customerId = $request->input('customer_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $batches = ProductionBatch::latest()->get();
        $customers = Customer::orderBy('shop_name')->get();

        $data = match ($reportType) {
            'production' => $this->getProductionReport($startDate, $endDate),
            'delivery' => $this->getDeliveryReport($batchId, $customerId, $startDate, $endDate),
            'daily_collection' => $this->getDailyCollectionReport($batchId, $startDate, $endDate),
            'outstanding_customers' => $this->getOutstandingCustomersReport($batchId, $customerId),
            'leakage' => $this->getLeakageReport($batchId, $customerId, $startDate, $endDate),
            'expense' => $this->getExpenseReport($startDate, $endDate),
            'customer_statement' => $this->getCustomerStatementReport($customerId),
            default => $this->getBatchPerformanceReport($batchId, $startDate, $endDate),
        };

        return Inertia::render('Reports/Index', [
            'reportType' => $reportType,
            'reportData' => $data,
            'startDate' => $startDate ?? '',
            'endDate' => $endDate ?? '',
            'selectedCustomerId' => $customerId ?? '',
            'selectedBatchId' => $batchId ?? '',
            'batches' => $batches,
            'customers' => $customers,
            'filters' => [
                'type' => $reportType,
                'batch_id' => $batchId,
                'customer_id' => $customerId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'generatedBy' => auth()->user()->name,
        ]);
    }

    private function getBatchPerformanceReport(?string $batchId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = ProductionBatch::with(['rawMaterialPurchase', 'producedBy']);

        if ($batchId) {
            $query->where('id', $batchId);
        }

        if ($startDate) {
            $query->whereDate('production_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('production_date', '<=', $endDate);
        }

        return $query->latest('production_date')->get()->map(function ($b) {
            return [
                'id' => $b->id,
                'batch_no' => $b->batch_no,
                'production_date' => $b->production_date->format('Y-m-d'),
                'raw_material' => ($b->rawMaterialPurchase->quantity_kg ?? 0).' KG Nylon',
                'bags_produced' => (int) $b->bags_produced,
                'bags_delivered' => (int) $b->bags_delivered,
                'remaining_stock' => (int) $b->remaining_stock,
                'expected_revenue' => (float) $b->expected_revenue,
                'cash_collected' => (float) $b->cash_collected,
                'transfer_collected' => (float) $b->transfer_collected,
                'total_collected' => (float) $b->total_collected,
                'outstanding_credit' => (float) $b->outstanding_credit,
                'returned_pieces' => (int) $b->returned_pieces,
                'replacement_issued' => (int) $b->replacement_issued,
                'status' => $b->status,
            ];
        })->toArray();
    }

    private function getProductionReport(?string $startDate, ?string $endDate): array
    {
        return ProductionBatch::with('rawMaterialPurchase')
            ->when($startDate, fn ($q) => $q->whereDate('production_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('production_date', '<=', $endDate))
            ->latest('production_date')
            ->get()
            ->map(fn ($b) => [
                'batch_no' => $b->batch_no,
                'production_date' => $b->production_date->format('Y-m-d'),
                'bags_produced' => (int) $b->bags_produced,
            ])->toArray();
    }

    private function getDeliveryReport(?string $batchId, ?string $customerId, ?string $startDate, ?string $endDate): array
    {
        return Delivery::with(['batch', 'customer'])
            ->when($batchId, fn ($q) => $q->where('batch_id', $batchId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($startDate, fn ($q) => $q->whereDate('delivery_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('delivery_date', '<=', $endDate))
            ->latest('delivery_date')
            ->get()
            ->map(fn ($d) => [
                'delivery_no' => $d->delivery_no,
                'delivery_date' => $d->delivery_date->format('Y-m-d'),
                'batch_no' => $d->batch->batch_no ?? 'N/A',
                'shop_name' => $d->customer->shop_name ?? 'N/A',
                'bags_delivered' => (int) $d->bags_delivered,
                'unit_price' => (float) $d->unit_price,
                'total_amount' => (float) $d->total_amount,
                'paid_amount' => (float) $d->paid_amount,
            ])->toArray();
    }

    private function getDailyCollectionReport(?string $batchId, ?string $startDate, ?string $endDate): array
    {
        return DailyCollection::with('batch')
            ->when($batchId, fn ($q) => $q->where('batch_id', $batchId))
            ->when($startDate, fn ($q) => $q->whereDate('collection_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('collection_date', '<=', $endDate))
            ->latest('collection_date')
            ->get()
            ->map(fn ($c) => [
                'collection_date' => $c->collection_date->format('Y-m-d'),
                'batch_no' => $c->batch->batch_no ?? 'N/A',
                'cash_amount' => (float) $c->cash_amount,
                'transfer_amount' => (float) $c->transfer_amount,
                'total_collection' => (float) $c->total_collection,
                'remarks' => $c->remarks,
            ])->toArray();
    }

    private function getOutstandingCustomersReport(?string $batchId, ?string $customerId): array
    {
        return CustomerDebt::with(['customer', 'batch', 'delivery'])
            ->where('status', 'open')
            ->when($batchId, fn ($q) => $q->where('batch_id', $batchId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->latest('created_at')
            ->get()
            ->map(fn ($cd) => [
                'shop_name' => $cd->customer->shop_name ?? 'N/A',
                'owner_name' => $cd->customer->owner_name ?? 'N/A',
                'phone' => $cd->customer->phone ?? 'N/A',
                'batch_no' => $cd->batch->batch_no ?? 'N/A',
                'delivery_no' => $cd->delivery->delivery_no ?? 'N/A',
                'delivery_date' => $cd->delivery->delivery_date ? $cd->delivery->delivery_date->format('Y-m-d') : 'N/A',
                'outstanding_amount' => (float) $cd->outstanding_amount,
                'age_days' => round($cd->created_at->diffInMinutes(now()) / 1440, 2),
            ])->toArray();
    }

    private function getLeakageReport(?string $batchId, ?string $customerId, ?string $startDate, ?string $endDate): array
    {
        return LeakageReturn::with(['batch', 'customer', 'delivery'])
            ->when($batchId, fn ($q) => $q->where('batch_id', $batchId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->latest('date')
            ->get()
            ->map(fn ($lr) => [
                'date' => $lr->date->format('Y-m-d'),
                'batch_no' => $lr->batch->batch_no ?? 'N/A',
                'shop_name' => $lr->customer->shop_name ?? 'N/A',
                'delivery_no' => $lr->delivery->delivery_no ?? 'N/A',
                'returned_pieces' => (int) $lr->returned_pieces,
                'replacement_issued' => (int) $lr->replacement_issued,
                'remarks' => $lr->remarks,
            ])->toArray();
    }

    private function getExpenseReport(?string $startDate, ?string $endDate): array
    {
        return Expense::query()
            ->when($startDate, fn ($q) => $q->whereDate('expense_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('expense_date', '<=', $endDate))
            ->latest('expense_date')
            ->get()
            ->map(fn ($e) => [
                'expense_date' => $e->expense_date->format('Y-m-d'),
                'category' => $e->category,
                'description' => $e->description,
                'amount' => (float) $e->amount,
            ])->toArray();
    }

    private function getCustomerStatementReport(?string $customerId): array
    {
        if (! $customerId) {
            $customer = Customer::first();
            $customerId = $customer?->id;
        }

        if (! $customerId) {
            return [];
        }

        $customer = Customer::find($customerId);
        if (! $customer) {
            return [];
        }

        $deliveries = Delivery::with(['batch', 'debts.payments'])
            ->where('customer_id', $customer->id)
            ->latest('delivery_date')
            ->get()
            ->map(function ($d) {
                $debt = $d->debts->first();

                return [
                    'delivery_no' => $d->delivery_no,
                    'delivery_date' => $d->delivery_date->format('Y-m-d'),
                    'batch_no' => $d->batch->batch_no ?? 'N/A',
                    'bags_delivered' => (int) $d->bags_delivered,
                    'total_amount' => (float) $d->total_amount,
                    'paid_amount' => (float) $d->paid_amount,
                    'outstanding_amount' => $debt ? (float) $debt->outstanding_amount : 0.0,
                    'debt_status' => $debt ? $debt->status : 'settled',
                ];
            });

        return [
            'customer' => [
                'shop_name' => $customer->shop_name,
                'owner_name' => $customer->owner_name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'total_outstanding' => $customer->outstanding_balance,
            ],
            'deliveries' => $deliveries,
        ];
    }
}
