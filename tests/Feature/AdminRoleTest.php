<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // make sure the admin role exists for the tests
    Role::firstOrCreate(['name' => 'admin']);
});

it('prevents a non-admin from visiting the admin page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/users')
        ->assertStatus(403);
});

it('allows an admin to visit the admin page', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/admin/users')
        ->assertOk();
});

it('blocks non-admin api modifications', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/admin/users/'.$user->id.'/toggle-admin')
        ->assertStatus(403);
});

it('prevents admin from revoking their own role via api', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/admin/users/'.$user->id.'/toggle-admin')
        ->assertStatus(403);
});

it('lets admin change another user role via api', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $other = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/admin/users/'.$other->id.'/toggle-admin')
        ->assertOk()
        ->assertJsonStructure(['user', 'hasRole']);
});
