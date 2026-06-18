<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
        'email_verified_at' => now(),
    ]);
});

it('allows admins to view the users index', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Users');
});

it('allows admins to suspend and activate a user', function () {
    $supplierUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'status' => UserStatus::Active,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.users.suspend', $supplierUser))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($supplierUser->fresh()->status)->toBe(UserStatus::Suspended);

    $this->actingAs($this->admin)
        ->post(route('admin.users.activate', $supplierUser))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($supplierUser->fresh()->status)->toBe(UserStatus::Active);
});

it('prevents admins from suspending themselves', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.suspend', $this->admin))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($this->admin->fresh()->status)->toBe(UserStatus::Active);
});

it('allows admins to reset a user password', function () {
    $supplierUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'status' => UserStatus::Active,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.users.password.update', $supplierUser), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->post(route('login'), [
        'email' => $supplierUser->email,
        'password' => 'new-password-123',
    ])->assertRedirect(route('dashboard'));
});

it('blocks suspended users from logging in', function () {
    $suspendedUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'status' => UserStatus::Suspended,
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);

    $this->post(route('login'), [
        'email' => $suspendedUser->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');
});

it('denies non-admins access to user management', function () {
    $supplierUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'status' => UserStatus::Active,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($supplierUser)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});
