<?php

use App\Enums\PaymentOption;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\CreditRepaymentTimeline;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderActivity;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createDispatchPaymentDateFixture(): array
{
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin-dates@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplierUser = User::query()->create([
        'name' => 'Supplier User',
        'email' => 'supplier-dates@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $supplier = Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Date Feeds Ltd',
        'contact_name' => 'Supplier Contact',
        'email' => 'supplier-dates@example.com',
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $timeline = CreditRepaymentTimeline::query()->create([
        'label' => '30 Days',
        'days' => 30,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $purchaseOrder = PurchaseOrder::query()->create([
        'supplier_id' => $supplier->id,
        'created_by' => $admin->id,
        'status' => PurchaseOrderStatus::Confirmed,
        'order_date' => now()->subDays(10)->toDateString(),
        'subtotal' => 10000,
        'tax_amount' => 0,
        'total' => 10000,
        'payment_option' => PaymentOption::FullCredit,
        'credit_repayment_timeline_id' => $timeline->id,
    ]);

    return compact('admin', 'supplierUser', 'purchaseOrder', 'timeline');
}

test('supplier can dispatch with selected date', function () {
    ['supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createDispatchPaymentDateFixture();

    $dispatchDate = now()->subDays(2)->toDateString();

    $this->actingAs($supplierUser)
        ->post(route('supplier.purchase-orders.dispatch', $purchaseOrder), [
            'dispatched_at' => $dispatchDate,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $purchaseOrder->refresh();

    expect($purchaseOrder->status)->toBe(PurchaseOrderStatus::Dispatched)
        ->and($purchaseOrder->dispatched_at?->toDateString())->toBe($dispatchDate)
        ->and($purchaseOrder->payment_due_date?->toDateString())->toBe(now()->subDays(2)->addDays(30)->toDateString());
});

test('supplier cannot dispatch with future date', function () {
    ['supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createDispatchPaymentDateFixture();

    $this->actingAs($supplierUser)
        ->post(route('supplier.purchase-orders.dispatch', $purchaseOrder), [
            'dispatched_at' => now()->addDay()->toDateString(),
        ])
        ->assertSessionHasErrors('dispatched_at');
});

test('admin can record payment with selected date', function () {
    ['admin' => $admin, 'purchaseOrder' => $purchaseOrder] = createDispatchPaymentDateFixture();

    $purchaseOrder->update([
        'status' => PurchaseOrderStatus::Dispatched,
        'dispatched_at' => now()->subDays(5),
        'payment_due_date' => now()->addDays(25)->toDateString(),
    ]);

    $paymentDate = now()->subDay()->toDateString();

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.record-payment', $purchaseOrder), [
            'amount' => 5000,
            'payment_date' => $paymentDate,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $purchaseOrder->refresh();

    expect((float) $purchaseOrder->amount_paid)->toBe(5000.0);

    $activity = PurchaseOrderActivity::query()
        ->where('purchase_order_id', $purchaseOrder->id)
        ->where('type', 'payment_recorded')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->metadata['payment_date'])->toBe($paymentDate)
        ->and($activity->created_at->toDateString())->toBe($paymentDate);
});

test('admin cannot record payment with future date', function () {
    ['admin' => $admin, 'purchaseOrder' => $purchaseOrder] = createDispatchPaymentDateFixture();

    $purchaseOrder->update(['status' => PurchaseOrderStatus::Dispatched, 'dispatched_at' => now()]);

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.record-payment', $purchaseOrder), [
            'amount' => 1000,
            'payment_date' => now()->addDay()->toDateString(),
        ])
        ->assertSessionHasErrors('payment_date');
});
