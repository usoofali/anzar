<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can generate reports page', function () {
    $user = User::factory()->create(['role' => 'manager']);

    $response = $this->actingAs($user)->get('/reports?type=batch_performance');

    $response->assertStatus(200);
});
