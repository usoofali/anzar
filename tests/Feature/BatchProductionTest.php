<?php

use App\Models\BatchProduction;
use App\Models\ProductionBatch;
use App\Models\RawMaterialPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('initial production run is created automatically when batch is created', function () {
    $user = User::factory()->create(['role' => 'production_staff']);

    $purchase = RawMaterialPurchase::create([
        'purchase_no' => 'RMP-T100',
        'supplier' => 'Film Co',
        'purchase_date' => '2026-08-04',
        'quantity_kg' => 20.00,
        'unit_price' => 1500.00,
        'packing_nylon_pieces' => 400,
        'packing_unit_price' => 20.00,
        'total_cost' => 38000.00,
    ]);

    $response = $this->actingAs($user)->post('/production-batches', [
        'raw_material_purchase_id' => $purchase->id,
        'production_date' => '2026-08-04',
        'production_time' => 'morning',
        'bags_produced' => 100,
    ]);

    $response->assertRedirect();

    $batch = ProductionBatch::first();
    $this->assertNotNull($batch);
    $this->assertEquals(100, $batch->bags_produced);

    // Verify first BatchProduction record
    $this->assertDatabaseHas('batch_productions', [
        'production_batch_id' => $batch->id,
        'bags_produced' => 100,
        'packing_nylon_used' => 100,
    ]);
});

test('staff can record subsequent sub-production runs within purchase limits', function () {
    $user = User::factory()->create(['role' => 'production_staff']);

    $purchase = RawMaterialPurchase::create([
        'purchase_no' => 'RMP-T101',
        'supplier' => 'Film Co',
        'purchase_date' => '2026-08-04',
        'quantity_kg' => 20.00,
        'unit_price' => 1500.00,
        'packing_nylon_pieces' => 400,
        'packing_unit_price' => 20.00,
        'total_cost' => 38000.00,
    ]);

    $batch = ProductionBatch::create([
        'batch_no' => 'PB-T101',
        'raw_material_purchase_id' => $purchase->id,
        'production_date' => '2026-08-04',
        'bags_produced' => 100,
        'produced_by' => $user->id,
        'status' => 'active',
    ]);

    // Seed first run
    $batch->batchProductions()->create([
        'production_date' => '2026-08-04',
        'packing_nylon_used' => 100,
        'bags_produced' => 100,
        'produced_by' => $user->id,
    ]);

    // Record second run: 200 bags (uses 10kg nylon proportionally)
    $response = $this->actingAs($user)->post("/production-batches/{$batch->id}/productions", [
        'production_date' => '2026-08-05',
        'production_time' => 'afternoon',
        'bags_produced' => 200,
        'remarks' => 'Second run',
    ]);

    $response->assertRedirect();
    $batch->refresh();

    // Aggregates should be updated
    $this->assertEquals(300, $batch->bags_produced);

    // Recording run exceeding remaining packing nylon should fail validation
    $response2 = $this->actingAs($user)->post("/production-batches/{$batch->id}/productions", [
        'production_date' => '2026-08-06',
        'production_time' => 'evening',
        'bags_produced' => 300, // Remaining is 100 (400 - 300), so 300 should fail
    ]);
    $response2->assertSessionHasErrors(['bags_produced']);
});

test('manager can delete sub-production runs and aggregates update dynamically', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $purchase = RawMaterialPurchase::create([
        'purchase_no' => 'RMP-T102',
        'supplier' => 'Film Co',
        'purchase_date' => '2026-08-04',
        'quantity_kg' => 30.00,
        'unit_price' => 1500.00,
        'packing_nylon_pieces' => 500,
        'packing_unit_price' => 20.00,
        'total_cost' => 55000.00,
    ]);

    $batch = ProductionBatch::create([
        'batch_no' => 'PB-T102',
        'raw_material_purchase_id' => $purchase->id,
        'production_date' => '2026-08-04',
        'bags_produced' => 300,
        'produced_by' => $manager->id,
        'status' => 'active',
    ]);

    $run1 = $batch->batchProductions()->create([
        'production_date' => '2026-08-04',
        'packing_nylon_used' => 100,
        'bags_produced' => 100,
        'produced_by' => $manager->id,
    ]);

    $run2 = $batch->batchProductions()->create([
        'production_date' => '2026-08-05',
        'packing_nylon_used' => 200,
        'bags_produced' => 200,
        'produced_by' => $manager->id,
    ]);

    // Initial check
    $batch->updateAggregates();
    $this->assertEquals(300, $batch->bags_produced);

    // Delete run2
    $response = $this->actingAs($manager)->delete("/production-batches/{$batch->id}/productions/{$run2->id}");
    $response->assertRedirect();

    $batch->refresh();
    // Aggregates should reflect run1 only now
    $this->assertEquals(100, $batch->bags_produced);
});
