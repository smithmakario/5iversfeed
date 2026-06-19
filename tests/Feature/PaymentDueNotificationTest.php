<?php

use App\Enums\PaymentOption;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\CreditRepaymentTimeline;
use App\Models\FeedType;
use App\Models\Formulation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\PaymentDueNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

function createPaymentReminderFixture(array $overrides = []): array
{
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin-payment@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplierUser = User::query()->create([
        'name' => 'Supplier User',
        'email' => 'supplier-payment@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $supplier = Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Payment Feeds Ltd',
        'contact_name' => 'Supplier Contact',
        'email' => 'supplier-payment@example.com',
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $feedType = FeedType::query()->create(['name' => 'Poultry', 'slug' => 'poultry-pay']);
    $brand = Brand::query()->create(['name' => 'Pay Brand', 'slug' => 'pay-brand']);
    $formulation = Formulation::query()->create([
        'feed_type_id' => $feedType->id,
        'brand_id' => $brand->id,
        'supplier_id' => $supplier->id,
        'name' => 'Starter Mash',
        'sku' => 'SKU-PAY-001',
        'unit' => 'bag',
        'price_per_unit' => 1000,
    ]);

    $timeline = CreditRepaymentTimeline::query()->create([
        'label' => '30 Days',
        'days' => 30,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $purchaseOrder = PurchaseOrder::query()->create(array_merge([
        'supplier_id' => $supplier->id,
        'created_by' => $admin->id,
        'status' => PurchaseOrderStatus::Dispatched,
        'order_date' => now()->toDateString(),
        'dispatched_at' => now()->subDays(23),
        'subtotal' => 10000,
        'tax_amount' => 0,
        'total' => 10000,
        'payment_option' => PaymentOption::FullCredit,
        'credit_repayment_timeline_id' => $timeline->id,
        'payment_due_date' => now()->addDays(7)->toDateString(),
        'payment_status' => PaymentStatus::OnCredit,
        'amount_paid' => 0,
    ], $overrides));

    $purchaseOrder->items()->create([
        'formulation_id' => $formulation->id,
        'product_name' => $formulation->name,
        'quantity' => 10,
        'unit_price' => 1000,
        'subtotal' => 10000,
    ]);

    return compact('admin', 'supplierUser', 'supplier', 'purchaseOrder');
}

test('payment due soon reminders are sent to admin and supplier', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser] = createPaymentReminderFixture();

    Artisan::call('payments:send-reminders');

    Notification::assertSentTo(
        $admin,
        PaymentDueNotification::class,
        fn (PaymentDueNotification $notification) => $notification->reminderType === 'due_soon',
    );

    Notification::assertSentTo(
        $supplierUser,
        PaymentDueNotification::class,
        fn (PaymentDueNotification $notification) => $notification->reminderType === 'due_soon'
            && $notification->recipientRole === 'supplier',
    );
});

test('payment due today reminders are sent', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createPaymentReminderFixture([
        'dispatched_at' => now()->subDays(30),
        'payment_due_date' => now()->toDateString(),
    ]);

    Artisan::call('payments:send-reminders');

    Notification::assertSentTo(
        $admin,
        PaymentDueNotification::class,
        fn (PaymentDueNotification $notification) => $notification->reminderType === 'due_today',
    );

    Notification::assertSentTo(
        $supplierUser,
        PaymentDueNotification::class,
        fn (PaymentDueNotification $notification) => $notification->reminderType === 'due_today',
    );
});

test('overdue payment reminders are sent', function () {
    Notification::fake();

    ['admin' => $admin, 'supplierUser' => $supplierUser] = createPaymentReminderFixture([
        'dispatched_at' => now()->subDays(35),
        'payment_due_date' => now()->subDays(5)->toDateString(),
        'payment_status' => PaymentStatus::Overdue,
    ]);

    Artisan::call('payments:send-reminders');

    Notification::assertSentTo(
        $admin,
        PaymentDueNotification::class,
        fn (PaymentDueNotification $notification) => $notification->reminderType === 'overdue',
    );

    Notification::assertSentTo(
        $supplierUser,
        PaymentDueNotification::class,
        fn (PaymentDueNotification $notification) => $notification->reminderType === 'overdue',
    );
});

test('no payment reminders sent when order is fully paid', function () {
    Notification::fake();

    createPaymentReminderFixture([
        'amount_paid' => 10000,
        'payment_status' => PaymentStatus::Paid,
    ]);

    Artisan::call('payments:send-reminders');

    Notification::assertNothingSent();
});

test('duplicate payment reminders are not sent on the same day', function () {
    Notification::fake();

    ['admin' => $admin] = createPaymentReminderFixture();

    Artisan::call('payments:send-reminders');
    Notification::assertSentTo($admin, PaymentDueNotification::class);

    Notification::fake();

    Artisan::call('payments:send-reminders');
    Notification::assertNothingSent();
});
