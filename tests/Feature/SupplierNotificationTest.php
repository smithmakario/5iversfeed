<?php

use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\NewSupplierApplicationNotification;
use App\Notifications\SupplierApplicationSubmittedNotification;
use App\Notifications\SupplierApprovedNotification;
use App\Notifications\SupplierRejectedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

test('supplier receives confirmation email after submitting application', function () {
    Notification::fake();
    Storage::fake('public');

    $this->post('/supplier/apply', [
        'company_name' => 'Test Feeds Co',
        'contact_name' => 'Test User',
        'email' => 'supplier-onboard@example.com',
        'company_logo' => UploadedFile::fake()->image('logo.png'),
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('login'));

    $user = User::query()->where('email', 'supplier-onboard@example.com')->first();
    $supplier = Supplier::query()->where('email', 'supplier-onboard@example.com')->first();

    Notification::assertSentTo(
        $user,
        SupplierApplicationSubmittedNotification::class,
        fn (SupplierApplicationSubmittedNotification $notification) => $notification->supplier->is($supplier),
    );
});

test('admins are notified when supplier submits application', function () {
    Notification::fake();
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $this->post('/supplier/apply', [
        'company_name' => 'Test Feeds Co',
        'contact_name' => 'Test User',
        'email' => 'supplier-onboard@example.com',
        'company_logo' => UploadedFile::fake()->image('logo.png'),
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Notification::assertSentTo(
        $admin,
        NewSupplierApplicationNotification::class,
    );
});

test('supplier is notified when application is approved', function () {
    Notification::fake();

    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplierUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $supplier = Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Pending Co',
        'contact_name' => 'Pending User',
        'email' => $supplierUser->email,
        'status' => SupplierStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.suppliers.approve', $supplier))
        ->assertRedirect();

    Notification::assertSentTo(
        $supplierUser,
        SupplierApprovedNotification::class,
    );
});

test('supplier is notified when application is rejected', function () {
    Notification::fake();

    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplierUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $supplier = Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Pending Co',
        'contact_name' => 'Pending User',
        'email' => $supplierUser->email,
        'status' => SupplierStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.suppliers.reject', $supplier), [
            'admin_notes' => 'Incomplete documentation.',
        ])
        ->assertRedirect();

    Notification::assertSentTo(
        $supplierUser,
        SupplierRejectedNotification::class,
        fn (SupplierRejectedNotification $notification) => $notification->supplier->admin_notes === 'Incomplete documentation.',
    );
});

test('newly created approved supplier receives welcome email', function () {
    Notification::fake();

    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)->post(route('admin.suppliers.store'), [
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
        'status' => SupplierStatus::Approved->value,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'jane@newfeeds.test')->first();

    Notification::assertSentTo(
        $user,
        SupplierApprovedNotification::class,
    );
});
