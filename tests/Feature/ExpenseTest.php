<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('staff can record operational expenses by category', function () {
    $user = User::factory()->create(['role' => 'manager']);

    $response = $this->actingAs($user)->post('/expenses', [
        'expense_date' => '2026-07-31',
        'category' => 'Fuel',
        'description' => 'Diesel for generator',
        'amount' => 35000.00,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('expenses', [
        'category' => 'Fuel',
        'amount' => 35000.00,
    ]);
});
