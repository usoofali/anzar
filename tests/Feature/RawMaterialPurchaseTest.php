<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('staff can record raw material nylon purchase', function () {
    $user = User::factory()->create(['role' => 'production_staff']);

    $response = $this->actingAs($user)->post('/raw-materials', [
        'supplier' => 'Kano Packaging Ltd',
        'purchase_date' => '2026-07-31',
        'quantity_kg' => 40.00,
        'unit_price' => 1500.00,
        'remarks' => '40 KG nylon rolls',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('raw_material_purchases', [
        'supplier' => 'Kano Packaging Ltd',
        'quantity_kg' => 40.00,
        'total_cost' => 60000.00,
    ]);
});
