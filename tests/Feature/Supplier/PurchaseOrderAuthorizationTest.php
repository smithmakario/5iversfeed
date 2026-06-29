<?php

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createSupplierPurchaseOrderAuthorizationFixture(): array
{
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin-po-auth@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $owner = User::query()->create([
        'name' => 'Owner Supplier',
        'email' => 'owner-supplier@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $other = User::query()->create([
        'name' => 'Other Supplier',
        'email' => 'other-supplier@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $ownerSupplier = Supplier::query()->create([
        'user_id' => $owner->id,
        'company_name' => 'Owner Feeds',
        'contact_name' => 'Owner',
        'email' => $owner->email,
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    Supplier::query()->create([
        'user_id' => $other->id,
        'company_name' => 'Other Feeds',
        'contact_name' => 'Other',
        'email' => $other->email,
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $purchaseOrder = PurchaseOrder::query()->create([
        'supplier_id' => $ownerSupplier->id,
        'created_by' => $admin->id,
        'status' => PurchaseOrderStatus::Submitted,
        'order_date' => now()->toDateString(),
        'subtotal' => 1000,
        'tax_amount' => 0,
        'total' => 1000,
    ]);

    return compact('admin', 'owner', 'other', 'purchaseOrder');
}

test('admin is redirected to admin purchase order when opening supplier link', function () {
    ['admin' => $admin, 'purchaseOrder' => $purchaseOrder] = createSupplierPurchaseOrderAuthorizationFixture();

    $this->actingAs($admin)
        ->get(route('supplier.purchase-orders.show', $purchaseOrder))
        ->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder));
});

test('approved supplier can view their own purchase order', function () {
    ['owner' => $owner, 'purchaseOrder' => $purchaseOrder] = createSupplierPurchaseOrderAuthorizationFixture();

    $this->actingAs($owner)
        ->get(route('supplier.purchase-orders.show', $purchaseOrder))
        ->assertOk();
});

test('supplier cannot view another suppliers purchase order', function () {
    ['other' => $other, 'purchaseOrder' => $purchaseOrder] = createSupplierPurchaseOrderAuthorizationFixture();

    $this->actingAs($other)
        ->get(route('supplier.purchase-orders.show', $purchaseOrder))
        ->assertForbidden();
});

test('supplier cannot view another suppliers purchase order from seeded data', function () {
    $purchaseOrder = PurchaseOrder::query()->find(3);

    if (! $purchaseOrder) {
        $this->markTestSkipped('PO #3 not present.');
    }

    $otherSupplier = User::query()->where('email', 'supplier@5iversfeed.test')->first();

    if (! $otherSupplier || $purchaseOrder->supplier_id === $otherSupplier->supplier?->id) {
        $this->markTestSkipped('Need PO owned by a different supplier than seeded supplier.');
    }

    $this->actingAs($otherSupplier)
        ->get(route('supplier.purchase-orders.show', $purchaseOrder))
        ->assertForbidden();
});
