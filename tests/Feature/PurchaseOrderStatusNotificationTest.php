<?php

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\FeedType;
use App\Models\Formulation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\PurchaseOrderStatusChangedNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

function createPurchaseOrderFixture(PurchaseOrderStatus $status = PurchaseOrderStatus::Submitted): array
{
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin-notify@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplierUser = User::query()->create([
        'name' => 'Supplier User',
        'email' => 'supplier-notify@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $supplier = Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Notify Feeds Ltd',
        'contact_name' => 'Supplier Contact',
        'email' => 'supplier-notify@example.com',
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $feedType = FeedType::query()->create(['name' => 'Poultry', 'slug' => 'poultry']);
    $brand = Brand::query()->create(['name' => 'Test Brand', 'slug' => 'test-brand']);
    $formulation = Formulation::query()->create([
        'feed_type_id' => $feedType->id,
        'brand_id' => $brand->id,
        'supplier_id' => $supplier->id,
        'name' => 'Starter Mash',
        'sku' => 'SKU-NOTIFY-001',
        'unit' => 'bag',
        'price_per_unit' => 1000,
    ]);

    $purchaseOrder = PurchaseOrder::query()->create([
        'supplier_id' => $supplier->id,
        'created_by' => $admin->id,
        'status' => $status,
        'order_date' => now()->toDateString(),
        'subtotal' => 10000,
        'tax_amount' => 0,
        'total' => 10000,
    ]);

    $purchaseOrder->items()->create([
        'formulation_id' => $formulation->id,
        'product_name' => $formulation->name,
        'quantity' => 10,
        'unit_price' => 1000,
        'subtotal' => 10000,
    ]);

    return compact('admin', 'supplierUser', 'supplier', 'purchaseOrder', 'formulation');
}

test('supplier is notified when admin issues a purchase order', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createPurchaseOrderFixture(PurchaseOrderStatus::Draft);

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.issue', $purchaseOrder))
        ->assertRedirect();

    Notification::assertSentTo(
        $supplierUser,
        PurchaseOrderStatusChangedNotification::class,
        fn (PurchaseOrderStatusChangedNotification $notification) => $notification->newStatus === PurchaseOrderStatus::Submitted
            && $notification->recipientRole === 'supplier',
    );
});

test('admin is notified when supplier confirms a purchase order', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createPurchaseOrderFixture();

    $this->actingAs($supplierUser)
        ->post(route('supplier.purchase-orders.confirm', $purchaseOrder))
        ->assertRedirect();

    Notification::assertSentTo(
        $admin,
        PurchaseOrderStatusChangedNotification::class,
        fn (PurchaseOrderStatusChangedNotification $notification) => $notification->newStatus === PurchaseOrderStatus::Confirmed
            && $notification->recipientRole === 'admin',
    );
});

test('admin is notified when supplier dispatches a purchase order', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createPurchaseOrderFixture(PurchaseOrderStatus::Confirmed);

    $this->actingAs($supplierUser)
        ->post(route('supplier.purchase-orders.dispatch', $purchaseOrder), [
            'dispatched_at' => now()->toDateString(),
        ])
        ->assertRedirect();

    Notification::assertSentTo(
        $admin,
        PurchaseOrderStatusChangedNotification::class,
        fn (PurchaseOrderStatusChangedNotification $notification) => $notification->newStatus === PurchaseOrderStatus::Dispatched
            && $notification->recipientRole === 'admin',
    );
});

test('admin is notified when supplier rejects a purchase order', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createPurchaseOrderFixture();

    $this->actingAs($supplierUser)
        ->post(route('supplier.purchase-orders.reject', $purchaseOrder), [
            'rejection_reason' => 'Out of stock for requested quantity.',
        ])
        ->assertRedirect();

    Notification::assertSentTo(
        $admin,
        PurchaseOrderStatusChangedNotification::class,
        fn (PurchaseOrderStatusChangedNotification $notification) => $notification->newStatus === PurchaseOrderStatus::Rejected
            && $notification->recipientRole === 'admin',
    );
});

test('supplier is notified when purchase order is marked received', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createPurchaseOrderFixture(PurchaseOrderStatus::Dispatched);

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.receive', $purchaseOrder))
        ->assertRedirect();

    Notification::assertSentTo(
        $supplierUser,
        PurchaseOrderStatusChangedNotification::class,
        fn (PurchaseOrderStatusChangedNotification $notification) => $notification->newStatus === PurchaseOrderStatus::Received,
    );
});

test('supplier is notified when purchase order is cancelled', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createPurchaseOrderFixture();

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.cancel', $purchaseOrder))
        ->assertRedirect();

    Notification::assertSentTo(
        $supplierUser,
        PurchaseOrderStatusChangedNotification::class,
        fn (PurchaseOrderStatusChangedNotification $notification) => $notification->newStatus === PurchaseOrderStatus::Cancelled,
    );
});

test('no notification is sent when draft purchase order is edited', function () {
    Notification::fake();

    ['admin' => $admin, 'purchaseOrder' => $purchaseOrder, 'formulation' => $formulation, 'supplier' => $supplier] = createPurchaseOrderFixture(PurchaseOrderStatus::Draft);

    $this->actingAs($admin)
        ->put(route('admin.purchase-orders.update', $purchaseOrder), [
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'tax_amount' => 0,
            'payment_option' => 'one_off',
            'items' => [
                [
                    'formulation_id' => $formulation->id,
                    'quantity' => 5,
                ],
            ],
        ])
        ->assertRedirect();

    Notification::assertNothingSent();
});

test('admin can edit issued purchase order awaiting supplier acceptance', function () {
    ['admin' => $admin, 'purchaseOrder' => $purchaseOrder, 'formulation' => $formulation, 'supplier' => $supplier] = createPurchaseOrderFixture();

    $this->actingAs($admin)
        ->get(route('admin.purchase-orders.edit', $purchaseOrder))
        ->assertOk()
        ->assertSee('Edit Purchase Order')
        ->assertSee('issued to the supplier');
});

test('supplier is notified when issued purchase order is updated', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder, 'formulation' => $formulation, 'supplier' => $supplier] = createPurchaseOrderFixture();

    $this->actingAs($admin)
        ->put(route('admin.purchase-orders.update', $purchaseOrder), [
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'tax_amount' => 0,
            'payment_option' => 'one_off',
            'items' => [
                [
                    'formulation_id' => $formulation->id,
                    'quantity' => 8,
                ],
            ],
        ])
        ->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder));

    Notification::assertSentTo(
        $supplierUser,
        \App\Notifications\PurchaseOrderUpdatedNotification::class,
    );
});

test('confirmed purchase order cannot be edited by admin', function () {
    ['admin' => $admin, 'purchaseOrder' => $purchaseOrder] = createPurchaseOrderFixture(PurchaseOrderStatus::Confirmed);

    $this->actingAs($admin)
        ->get(route('admin.purchase-orders.edit', $purchaseOrder))
        ->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder))
        ->assertSessionHas('error');
});

test('supplier without user account receives on-demand mail notification', function () {
    Notification::fake();

    ['admin' => $admin, 'supplier' => $supplier, 'purchaseOrder' => $purchaseOrder] = createPurchaseOrderFixture(PurchaseOrderStatus::Draft);

    $supplier->update(['user_id' => null, 'email' => 'standalone-supplier@example.com']);

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.issue', $purchaseOrder))
        ->assertRedirect();

    Notification::assertSentOnDemand(
        PurchaseOrderStatusChangedNotification::class,
        fn (PurchaseOrderStatusChangedNotification $notification, array $channels, object $notifiable) => $notifiable->routes['mail'] === 'standalone-supplier@example.com',
    );
});

test('invalid status transitions are rejected', function () {
    ['admin' => $admin, 'purchaseOrder' => $purchaseOrder] = createPurchaseOrderFixture(PurchaseOrderStatus::Submitted);

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.receive', $purchaseOrder))
        ->assertRedirect()
        ->assertSessionHas('error');
});
