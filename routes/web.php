<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDebtController;
use App\Http\Controllers\DailyCollectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtPaymentController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LeakageReturnController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\RawMaterialPurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (accessible to all authenticated staff)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Production Module (Manager & Production Staff)
    Route::middleware(['role:production_staff'])->group(function () {
        // Raw Material Purchases
        Route::get('/raw-materials', [RawMaterialPurchaseController::class, 'index'])->name('raw-materials.index');
        Route::post('/raw-materials', [RawMaterialPurchaseController::class, 'store'])->name('raw-materials.store');
        Route::delete('/raw-materials/{rawMaterialPurchase}', [RawMaterialPurchaseController::class, 'destroy'])->name('raw-materials.destroy');

        // Production Batches
        Route::get('/production-batches', [ProductionBatchController::class, 'index'])->name('production-batches.index');
        Route::post('/production-batches', [ProductionBatchController::class, 'store'])->name('production-batches.store');
        Route::get('/production-batches/{productionBatch}', [ProductionBatchController::class, 'show'])->name('production-batches.show');
        Route::post('/production-batches/{productionBatch}/toggle-status', [ProductionBatchController::class, 'toggleStatus'])->name('production-batches.toggle-status');
        Route::delete('/production-batches/{productionBatch}', [ProductionBatchController::class, 'destroy'])->name('production-batches.destroy');
        Route::post('/production-batches/{productionBatch}/productions', [ProductionBatchController::class, 'storeProduction'])->name('production-batches.store-production');
        Route::delete('/production-batches/{productionBatch}/productions/{batchProduction}', [ProductionBatchController::class, 'destroyProduction'])->name('production-batches.destroy-production');
    });

    // Distribution & Sales Module (Manager & Sales Staff)
    Route::middleware(['role:sales_staff'])->group(function () {
        // Customers (Shops)
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Deliveries
        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
        Route::delete('/deliveries/{delivery}', [DeliveryController::class, 'destroy'])->name('deliveries.destroy');

        // Daily Collections
        Route::get('/daily-collections', [DailyCollectionController::class, 'index'])->name('daily-collections.index');
        Route::post('/daily-collections', [DailyCollectionController::class, 'store'])->name('daily-collections.store');
        Route::delete('/daily-collections/{dailyCollection}', [DailyCollectionController::class, 'destroy'])->name('daily-collections.destroy');

        // Customer Debts & Debt Payments
        Route::get('/customer-debts', [CustomerDebtController::class, 'index'])->name('customer-debts.index');
        Route::post('/debt-payments', [DebtPaymentController::class, 'store'])->name('debt-payments.store');
        Route::delete('/debt-payments/{debtPayment}', [DebtPaymentController::class, 'destroy'])->name('debt-payments.destroy');

        // Leakage Returns
        Route::get('/leakage-returns', [LeakageReturnController::class, 'index'])->name('leakage-returns.index');
        Route::post('/leakage-returns', [LeakageReturnController::class, 'store'])->name('leakage-returns.store');
        Route::delete('/leakage-returns/{leakageReturn}', [LeakageReturnController::class, 'destroy'])->name('leakage-returns.destroy');
    });

    // Expenses Module (All Staff can view & record expenses for Manager to oversee)
    Route::middleware(['role:manager,production_staff,sales_staff'])->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    });

    // Manager-Only Module (Reports, Expense Deletion)
    Route::middleware(['role:manager'])->group(function () {
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // Admin-Only Module (User Management)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

require __DIR__.'/settings.php';
