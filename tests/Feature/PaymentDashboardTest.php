<?php

use App\Enums\PaymentStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\CreditRepaymentTimeline;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PaymentDashboardService;
use Illuminate\Support\Facades\Hash;

test('admin dashboard shows payment statistics', function () {
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin-pay-dash@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplierUser = User::query()->create([
        'name' => 'Supplier',
        'email' => 'supplier-pay-dash@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $supplier = Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Pay Dash Feeds',
        'contact_name' => 'Contact',
        'email' => 'supplier-pay-dash@example.com',
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $timeline = CreditRepaymentTimeline::query()->create([
        'label' => '10 Days',
        'days' => 10,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    PurchaseOrder::query()->create([
        'supplier_id' => $supplier->id,
        'created_by' => $admin->id,
        'status' => PurchaseOrderStatus::Dispatched,
        'order_date' => now()->toDateString(),
        'subtotal' => 50000,
        'tax_amount' => 0,
        'total' => 50000,
        'payment_option' => 'full_credit',
        'credit_repayment_timeline_id' => $timeline->id,
        'dispatched_at' => now()->subDays(12),
        'payment_due_date' => now()->subDays(2)->toDateString(),
        'payment_status' => PaymentStatus::Overdue->value,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Payment Overview')
        ->assertSee('Overdue')
        ->assertSee('Amount Due by Purchase Order')
        ->assertSee('days overdue');
});

test('supplier dashboard shows payment statistics', function () {
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin-pay-dash2@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplierUser = User::query()->create([
        'name' => 'Supplier',
        'email' => 'supplier-pay-dash2@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $supplier = Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Supplier Pay Dash',
        'contact_name' => 'Contact',
        'email' => 'supplier-pay-dash2@example.com',
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    PurchaseOrder::query()->create([
        'supplier_id' => $supplier->id,
        'created_by' => $admin->id,
        'status' => PurchaseOrderStatus::Confirmed,
        'order_date' => now()->toDateString(),
        'subtotal' => 20000,
        'tax_amount' => 0,
        'total' => 20000,
        'payment_option' => 'one_off',
        'payment_status' => PaymentStatus::Unpaid->value,
    ]);

    $this->actingAs($supplierUser)
        ->get(route('supplier.dashboard'))
        ->assertOk()
        ->assertSee('Payment Overview')
        ->assertSee('Awaiting dispatch');
});

test('payment dashboard service calculates days to due', function () {
    $service = app(PaymentDashboardService::class);

    $order = new PurchaseOrder([
        'payment_due_date' => now()->addDays(5)->toDateString(),
        'total' => 1000,
        'amount_paid' => 0,
    ]);

    expect($service->daysToDue($order))->toBe(5);
    expect($service->isOverdue($order))->toBeFalse();
});
