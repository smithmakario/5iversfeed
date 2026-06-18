<?php

use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can view create supplier form', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.suppliers.create'))
        ->assertOk()
        ->assertSee('Add Supplier');
});

test('admin can create an approved supplier', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post(route('admin.suppliers.store'), [
        'company_name' => 'New Feeds Co',
        'contact_name' => 'Jane Supplier',
        'email' => 'jane@newfeeds.test',
        'phone' => '08012345678',
        'address' => '12 Industrial Ave',
        'city' => 'Lagos',
        'state' => 'Lagos',
        'country' => 'Nigeria',
        'tax_id' => 'TAX-001',
        'registration_number' => 'RC-001',
        'admin_notes' => 'Added by admin',
        'status' => SupplierStatus::Approved->value,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $supplier = Supplier::query()->where('email', 'jane@newfeeds.test')->first();

    $response->assertRedirect(route('admin.suppliers.show', $supplier));
    $response->assertSessionHas('success');

    expect($supplier)->not->toBeNull()
        ->and($supplier->company_name)->toBe('New Feeds Co')
        ->and($supplier->status)->toBe(SupplierStatus::Approved)
        ->and($supplier->approved_by)->toBe($admin->id)
        ->and($supplier->approved_at)->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'email' => 'jane@newfeeds.test',
        'role' => UserRole::Supplier->value,
    ]);
});

test('guest cannot create suppliers', function () {
    $this->post(route('admin.suppliers.store'), [
        'company_name' => 'New Feeds Co',
        'contact_name' => 'Jane Supplier',
        'email' => 'jane@newfeeds.test',
        'status' => SupplierStatus::Approved->value,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('login'));
});
