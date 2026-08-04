<?php

namespace App\Http\Controllers;

use App\Models\CustomerDebt;
use App\Models\DailyCollection;
use App\Models\DebtPayment;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\LeakageReturn;
use App\Models\ProductionBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Production Metrics
        $activeBatchesCount = ProductionBatch::where('status', 'active')->count();
        $bagsProducedToday = ProductionBatch::whereDate('production_date', $today)->sum('bags_produced');
        $bagsProducedThisMonth = ProductionBatch::whereDate('production_date', '>=', $startOfMonth)->sum('bags_produced');

        // Deliveries & Collections Today
        $bagsDeliveredToday = Delivery::whereDate('delivery_date', $today)->sum('bags_delivered');

        $dailyCashToday = DailyCollection::whereDate('collection_date', $today)->sum('cash_amount')
            + DebtPayment::whereDate('payment_date', $today)->where('payment_method', 'cash')->sum('amount')
            + Delivery::whereDate('delivery_date', $today)->sum('paid_amount');

        $dailyTransferToday = DailyCollection::whereDate('collection_date', $today)->sum('transfer_amount')
            + DebtPayment::whereDate('payment_date', $today)->where('payment_method', 'transfer')->sum('amount');

        $totalOutstandingCredit = CustomerDebt::where('status', 'open')->sum('outstanding_amount');
        $customersOwingCount = CustomerDebt::where('status', 'open')->distinct('customer_id')->count('customer_id');

        // Quality (Leakages)
        $leakagePiecesToday = LeakageReturn::whereDate('date', $today)->sum('returned_pieces');
        $leakagePiecesThisMonth = LeakageReturn::whereDate('date', '>=', $startOfMonth)->sum('returned_pieces');

        // Expenses
        $expensesToday = Expense::whereDate('expense_date', $today)->sum('amount');
        $expensesThisMonth = Expense::whereDate('expense_date', '>=', $startOfMonth)->sum('amount');

        // Tables & Lists
        $recentDeliveries = Delivery::with(['batch', 'customer'])
            ->latest()
            ->take(5)
            ->get();

        $customersOwing = CustomerDebt::with(['customer', 'batch', 'delivery'])
            ->where('status', 'open')
            ->latest()
            ->take(5)
            ->get();

        $activeBatches = ProductionBatch::with('rawMaterialPurchase')
            ->where('status', 'active')
            ->latest()
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'production_date' => $batch->production_date->format('Y-m-d'),
                    'bags_produced' => $batch->bags_produced,
                    'bags_delivered' => $batch->bags_delivered,
                    'remaining_stock' => $batch->remaining_stock,
                    'outstanding_credit' => $batch->outstanding_credit,
                    'status' => $batch->status,
                ];
            });

        $recentPayments = DebtPayment::with(['customer', 'batch'])
            ->latest()
            ->take(5)
            ->get();

        $recentLeakageReturns = LeakageReturn::with(['customer', 'batch'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'active_batches' => $activeBatchesCount,
                'bags_produced_today' => (int) $bagsProducedToday,
                'bags_produced_month' => (int) $bagsProducedThisMonth,
                'bags_delivered_today' => (int) $bagsDeliveredToday,
                'daily_cash_today' => (float) $dailyCashToday,
                'daily_transfer_today' => (float) $dailyTransferToday,
                'outstanding_credit' => (float) $totalOutstandingCredit,
                'customers_owing_count' => $customersOwingCount,
                'leakage_pieces_today' => (int) $leakagePiecesToday,
                'leakage_pieces_month' => (int) $leakagePiecesThisMonth,
                'expenses_today' => (float) $expensesToday,
                'expenses_month' => (float) $expensesThisMonth,
            ],
            'recentDeliveries' => $recentDeliveries,
            'customersOwing' => $customersOwing,
            'activeBatches' => $activeBatches,
            'recentPayments' => $recentPayments,
            'recentLeakageReturns' => $recentLeakageReturns,
        ]);
    }
}
