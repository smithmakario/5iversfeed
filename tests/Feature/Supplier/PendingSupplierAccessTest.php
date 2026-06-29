<?php

use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('pending supplier is redirected to approval page after login', function () {
    $supplierUser = User::query()->create([
        'name' => 'Pending Supplier',
        'email' => 'pending-supplier@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Pending Feeds Co',
        'contact_name' => 'Pending Supplier',
        'email' => 'pending-supplier@example.com',
        'status' => SupplierStatus::Pending,
    ]);

    $this->post('/login', [
        'email' => 'pending-supplier@example.com',
        'password' => 'password',
    ])->assertRedirect(route('supplier.pending'));
});

test('pending supplier sees awaiting approval page instead of 403', function () {
    $supplierUser = User::query()->create([
        'name' => 'Pending Supplier',
        'email' => 'pending-supplier-page@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Pending Feeds Co',
        'contact_name' => 'Pending Supplier',
        'email' => 'pending-supplier-page@example.com',
        'status' => SupplierStatus::Pending,
    ]);

    $this->actingAs($supplierUser)
        ->get(route('supplier.dashboard'))
        ->assertRedirect(route('supplier.pending'));

    $this->actingAs($supplierUser)
        ->get(route('supplier.pending'))
        ->assertOk()
        ->assertSee('Awaiting 5ivers Feed approval')
        ->assertSee('Pending Feeds Co')
        ->assertDontSee('403');
});

test('approved supplier can access supplier dashboard', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplierUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Approved Feeds Co',
        'contact_name' => 'Approved Supplier',
        'email' => $supplierUser->email,
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $this->actingAs($supplierUser)
        ->get(route('supplier.dashboard'))
        ->assertOk();
});
