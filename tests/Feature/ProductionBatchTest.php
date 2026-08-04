<?php

use App\Models\ProductionBatch;
use App\Models\RawMaterialPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('production staff can create production batch linked to nylon purchase', function () {
    $user = User::factory()->create(['role' => 'production_staff']);

    $purchase = RawMaterialPurchase::create([
        'purchase_no' => 'RMP-TEST-001',
        'supplier' => 'Polymer Co',
        'purchase_date' => '2026-07-31',
        'quantity_kg' => 30.00,
        'unit_price' => 2000.00,
        'packing_nylon_pieces' => 500,
        'packing_unit_price' => 25.00,
        'total_cost' => 72500.00,
    ]);

    $response = $this->actingAs($user)->post('/production-batches', [
        'raw_material_purchase_id' => $purchase->id,
        'production_date' => '2026-07-31',
        'production_time' => 'morning',
        'bags_produced' => 300,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('production_batches', [
        'raw_material_purchase_id' => $purchase->id,
        'bags_produced' => 300,
        'quantity_used_kg' => 18.00, // 300 * (30.00 / 500)
        'status' => 'active',
    ]);
});

test('manager can close and reopen a production batch', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $purchase = RawMaterialPurchase::create([
        'purchase_no' => 'RMP-TEST-002',
        'supplier' => 'Polymer Co',
        'purchase_date' => '2026-07-31',
        'quantity_kg' => 20.00,
        'unit_price' => 2000.00,
        'total_cost' => 40000.00,
    ]);

    $batch = ProductionBatch::create([
        'batch_no' => 'PB-999',
        'raw_material_purchase_id' => $purchase->id,
        'production_date' => '2026-07-31',
        'quantity_used_kg' => 20.00,
        'bags_produced' => 200,
        'produced_by' => $manager->id,
        'status' => 'active',
    ]);

    // Close batch
    $response = $this->actingAs($manager)->post("/production-batches/{$batch->id}/toggle-status");
    $response->assertRedirect();
    $this->assertEquals('closed', $batch->fresh()->status);

    // Reopen batch
    $response2 = $this->actingAs($manager)->post("/production-batches/{$batch->id}/toggle-status");
    $response2->assertRedirect();
    $this->assertEquals('active', $batch->fresh()->status);
});
