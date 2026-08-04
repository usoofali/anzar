<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('manager can view staff users page', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $response = $this->actingAs($manager)->get('/users');

    $response->assertStatus(200);
});

test('manager can create a new staff user with username or email', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $response = $this->actingAs($manager)->post('/users', [
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

test('manager can reset staff password', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $staff = User::factory()->create(['role' => 'sales_staff']);

    $response = $this->actingAs($manager)->post("/users/{$staff->id}/reset-password", [
        'password' => 'newpassword123',
    ]);

    $response->assertRedirect();
    $staff->refresh();
    $this->assertTrue(Hash::check('newpassword123', $staff->password));
});
