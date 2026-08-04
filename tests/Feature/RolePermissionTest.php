<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('production staff can access production module and expenses but is blocked from sales, reports, and users', function () {
    $user = User::factory()->create(['role' => 'production_staff']);

    // Allowed routes
    $this->actingAs($user)->get('/dashboard')->assertStatus(200);
    $this->actingAs($user)->get('/raw-materials')->assertStatus(200);
    $this->actingAs($user)->get('/production-batches')->assertStatus(200);
    $this->actingAs($user)->get('/expenses')->assertStatus(200);

    // Blocked routes
    $this->actingAs($user)->get('/customers')->assertStatus(403);
    $this->actingAs($user)->get('/deliveries')->assertStatus(403);
    $this->actingAs($user)->get('/daily-collections')->assertStatus(403);
    $this->actingAs($user)->get('/customer-debts')->assertStatus(403);
    $this->actingAs($user)->get('/leakage-returns')->assertStatus(403);
    $this->actingAs($user)->get('/reports')->assertStatus(403);
    $this->actingAs($user)->get('/users')->assertStatus(403);
});

test('sales staff can access distribution module and expenses but is blocked from production, reports, and users', function () {
    $user = User::factory()->create(['role' => 'sales_staff']);

    // Allowed routes
    $this->actingAs($user)->get('/dashboard')->assertStatus(200);
    $this->actingAs($user)->get('/customers')->assertStatus(200);
    $this->actingAs($user)->get('/deliveries')->assertStatus(200);
    $this->actingAs($user)->get('/daily-collections')->assertStatus(200);
    $this->actingAs($user)->get('/customer-debts')->assertStatus(200);
    $this->actingAs($user)->get('/leakage-returns')->assertStatus(200);
    $this->actingAs($user)->get('/expenses')->assertStatus(200);

    // Blocked routes
    $this->actingAs($user)->get('/raw-materials')->assertStatus(403);
    $this->actingAs($user)->get('/production-batches')->assertStatus(403);
    $this->actingAs($user)->get('/reports')->assertStatus(403);
    $this->actingAs($user)->get('/users')->assertStatus(403);
});

test('manager has access to all modules except user management', function () {
    $user = User::factory()->create(['role' => 'manager']);

    $this->actingAs($user)->get('/dashboard')->assertStatus(200);
    $this->actingAs($user)->get('/raw-materials')->assertStatus(200);
    $this->actingAs($user)->get('/production-batches')->assertStatus(200);
    $this->actingAs($user)->get('/customers')->assertStatus(200);
    $this->actingAs($user)->get('/deliveries')->assertStatus(200);
    $this->actingAs($user)->get('/daily-collections')->assertStatus(200);
    $this->actingAs($user)->get('/customer-debts')->assertStatus(200);
    $this->actingAs($user)->get('/leakage-returns')->assertStatus(200);
    $this->actingAs($user)->get('/expenses')->assertStatus(200);
    $this->actingAs($user)->get('/reports')->assertStatus(200);

    // Manager is blocked from user management (admin-only)
    $this->actingAs($user)->get('/users')->assertStatus(403);
});

test('admin has full access to all system modules including user management', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)->get('/dashboard')->assertStatus(200);
    $this->actingAs($user)->get('/raw-materials')->assertStatus(200);
    $this->actingAs($user)->get('/production-batches')->assertStatus(200);
    $this->actingAs($user)->get('/customers')->assertStatus(200);
    $this->actingAs($user)->get('/deliveries')->assertStatus(200);
    $this->actingAs($user)->get('/daily-collections')->assertStatus(200);
    $this->actingAs($user)->get('/customer-debts')->assertStatus(200);
    $this->actingAs($user)->get('/leakage-returns')->assertStatus(200);
    $this->actingAs($user)->get('/expenses')->assertStatus(200);
    $this->actingAs($user)->get('/reports')->assertStatus(200);
    $this->actingAs($user)->get('/users')->assertStatus(200);
});
