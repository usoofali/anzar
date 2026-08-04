<?php

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\Delivery;
use App\Models\ProductionBatch;
use App\Models\RawMaterialPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('debt payment reduces outstanding amount and settles debt when zero', function () {
    $user = User::factory()->create(['role' => 'sales_staff']);
    $customer = Customer::create(['shop_name' => 'Bello Shop', 'status' => 'active']);

    $purchase = RawMaterialPurchase::create([
        'purchase_no' => 'RMP-DEBT-01',
        'supplier' => 'Nylon Co',
        'purchase_date' => '2026-07-31',
        'quantity_kg' => 20.00,
        'unit_price' => 1500.00,
        'total_cost' => 30000.00,
    ]);

    $batch = ProductionBatch::create([
        'batch_no' => 'PB-DEBT-1',
        'raw_material_purchase_id' => $purchase->id,
        'production_date' => '2026-07-31',
        'quantity_used_kg' => 20.00,
        'bags_produced' => 200,
        'status' => 'active',
    ]);

    $delivery = Delivery::create([
        'delivery_no' => 'DEL-DEBT-01',
        'batch_id' => $batch->id,
        'customer_id' => $customer->id,
        'delivery_date' => '2026-07-31',
        'bags_delivered' => 20,
        'unit_price' => 400.00,
        'total_amount' => 8000.00,
        'paid_amount' => 0.00,
        'delivered_by' => $user->id,
    ]);

    $debt = CustomerDebt::create([
        'delivery_id' => $delivery->id,
        'customer_id' => $customer->id,
        'batch_id' => $batch->id,
        'outstanding_amount' => 8000.00,
        'status' => 'open',
    ]);

    // Pay full 8000
    $response = $this->actingAs($user)->post('/debt-payments', [
        'debt_id' => $debt->id,
        'payment_date' => '2026-07-31',
        'payment_method' => 'cash',
        'amount' => 8000.00,
    ]);

    $response->assertRedirect();
    $debt->refresh();

    $this->assertEquals(0, $debt->outstanding_amount);
    $this->assertEquals('settled', $debt->status);
    $this->assertDatabaseHas('debt_payments', [
        'debt_id' => $debt->id,
        'amount' => 8000.00,
        'payment_method' => 'cash',
    ]);
});
