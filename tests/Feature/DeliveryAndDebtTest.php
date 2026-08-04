<?php

use App\Models\Customer;
use App\Models\ProductionBatch;
use App\Models\RawMaterialPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('recording delivery underpaid automatically creates open customer debt', function () {
    $user = User::factory()->create(['role' => 'sales_staff']);
    $customer = Customer::create(['shop_name' => 'Kano Retail Store', 'status' => 'active']);

    $purchase = RawMaterialPurchase::create([
        'purchase_no' => 'RMP-DEL-01',
        'supplier' => 'Nylon Co',
        'purchase_date' => '2026-07-31',
        'quantity_kg' => 25.00,
        'unit_price' => 1500.00,
        'total_cost' => 37500.00,
    ]);

    $batch = ProductionBatch::create([
        'batch_no' => 'PB-DEL-1',
        'raw_material_purchase_id' => $purchase->id,
        'production_date' => '2026-07-31',
        'quantity_used_kg' => 25.00,
        'bags_produced' => 200,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post('/deliveries', [
        'batch_id' => $batch->id,
        'customer_id' => $customer->id,
        'delivery_date' => '2026-07-31',
        'bags_delivered' => 50,
        'unit_price' => 400.00,
        'paid_amount' => 10000.00, // Total = 20,000, Paid = 10,000, Outstanding = 10,000
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('deliveries', [
        'batch_id' => $batch->id,
        'customer_id' => $customer->id,
        'bags_delivered' => 50,
        'total_amount' => 20000.00,
        'paid_amount' => 10000.00,
    ]);

    $this->assertDatabaseHas('customer_debts', [
        'customer_id' => $customer->id,
        'batch_id' => $batch->id,
        'outstanding_amount' => 10000.00,
        'status' => 'open',
    ]);
});
