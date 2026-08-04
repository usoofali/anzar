<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\ProductionBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerDebtController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $batchId = $request->input('batch_id');
        $customerId = $request->input('customer_id');
        $status = $request->has('status') ? $request->input('status') : 'open';

        $debts = CustomerDebt::with(['customer', 'batch', 'delivery', 'payments.receivedBy'])
            ->when($search, function ($query, $s) {
                $query->whereHas('customer', function ($q) use ($s) {
                    $q->where('shop_name', 'like', "%{$s}%")
                        ->orWhere('owner_name', 'like', "%{$s}%");
                })->orWhereHas('batch', function ($q) use ($s) {
                    $q->where('batch_no', 'like', "%{$s}%");
                });
            })
            ->when($batchId, function ($query, $bId) {
                $query->where('batch_id', $bId);
            })
            ->when($customerId, function ($query, $cId) {
                $query->where('customer_id', $cId);
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $batches = ProductionBatch::latest()->get();
        $customers = Customer::orderBy('shop_name')->get();

        return Inertia::render('CustomerDebts/Index', [
            'debts' => $debts,
            'batches' => $batches,
            'customers' => $customers,
            'filters' => [
                'search' => $search,
                'batch_id' => $batchId,
                'customer_id' => $customerId,
                'status' => $status,
            ],
        ]);
    }
}
