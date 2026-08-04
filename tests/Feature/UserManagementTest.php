<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view staff users page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/users');

    $response->assertStatus(200);
});

test('admin can create a new staff user with username or email', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Khadija Bello',
        'email' => 'khadija@anzar.com',
        'username' => 'khadija',
        'phone' => '08099998888',
        'role' => 'sales_staff',
        'status' => 'active',
        'password' => 'password123',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'email' => 'khadija@anzar.com',
        'username' => 'khadija',
        'role' => 'sales_staff',
    ]);
});

test('admin can reset staff password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staff = User::factory()->create(['role' => 'sales_staff']);

    $response = $this->actingAs($admin)->post("/users/{$staff->id}/reset-password", [
        'password' => 'newpassword123',
    ]);

    $response->assertRedirect();
    $staff->refresh();
    $this->assertTrue(Hash::check('newpassword123', $staff->password));
});
