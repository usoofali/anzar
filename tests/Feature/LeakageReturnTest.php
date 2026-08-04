<?php

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\ProductionBatch;
use App\Models\RawMaterialPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('staff can record leakage returns in pieces', function () {
    $user = User::factory()->create(['role' => 'sales_staff']);
    $customer = Customer::create(['shop_name' => 'Shop A', 'status' => 'active']);

    $purchase = RawMaterialPurchase::create([
        'purchase_no' => 'RMP-LEAK-01',
        'supplier' => 'Nylon Co',
        'purchase_date' => '2026-07-31',
        'quantity_kg' => 10.00,
        'unit_price' => 1000.00,
        'total_cost' => 10000.00,
    ]);

    $batch = ProductionBatch::create([
        'batch_no' => 'PB-LEAK-1',
        'raw_material_purchase_id' => $purchase->id,
        'production_date' => '2026-07-31',
        'quantity_used_kg' => 10.00,
        'bags_produced' => 100,
        'status' => 'active',
    ]);

    $delivery = Delivery::create([
        'delivery_no' => 'DEL-LEAK-01',
        'batch_id' => $batch->id,
        'customer_id' => $customer->id,
        'delivery_date' => '2026-07-31',
        'bags_delivered' => 10,
        'unit_price' => 400.00,
        'total_amount' => 4000.00,
        'paid_amount' => 4000.00,
    ]);

    $response = $this->actingAs($user)->post('/leakage-returns', [
        'delivery_id' => $delivery->id,
        'date' => '2026-07-31',
        'returned_pieces' => 6,
        'replacement_issued' => 6,
        'remarks' => '6 leaking pieces replaced',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('leakage_returns', [
        'delivery_id' => $delivery->id,
        'returned_pieces' => 6,
        'replacement_issued' => 6,
    ]);
});
