<?php

use App\Enums\PaymentOption;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\CreditRepaymentTimeline;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderActivity;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SupplierStatementService;
use Illuminate\Support\Facades\Hash;

function createStatementFixture(): array
{
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin-statement@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplier = Supplier::query()->create([
        'company_name' => 'Statement Feeds Ltd',
        'contact_name' => 'Contact',
        'email' => 'statement@example.com',
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

    $paidOnTime = PurchaseOrder::query()->create([
        'supplier_id' => $supplier->id,
        'created_by' => $admin->id,
        'status' => PurchaseOrderStatus::Received,
        'order_date' => now()->subDays(20)->toDateString(),
        'subtotal' => 10000,
        'tax_amount' => 0,
        'total' => 10000,
        'payment_option' => PaymentOption::FullCredit,
        'credit_repayment_timeline_id' => $timeline->id,
        'dispatched_at' => now()->subDays(18),
        'payment_due_date' => now()->subDays(2)->toDateString(),
        'payment_status' => PaymentStatus::Paid,
        'amount_paid' => 10000,
    ]);

    $paidActivity = PurchaseOrderActivity::query()->create([
        'purchase_order_id' => $paidOnTime->id,
        'user_id' => $admin->id,
        'type' => 'payment_recorded',
        'description' => 'Payment recorded',
        'metadata' => ['amount' => 10000],
    ]);

    $paidActivity->forceFill([
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subDays(5),
    ])->saveQuietly();

    $overdue = PurchaseOrder::query()->create([
        'supplier_id' => $supplier->id,
        'created_by' => $admin->id,
        'status' => PurchaseOrderStatus::Dispatched,
        'order_date' => now()->subDays(10)->toDateString(),
        'subtotal' => 5000,
        'tax_amount' => 0,
        'total' => 5000,
        'payment_option' => PaymentOption::FullCredit,
        'credit_repayment_timeline_id' => $timeline->id,
        'dispatched_at' => now()->subDays(8),
        'payment_due_date' => now()->subDays(2)->toDateString(),
        'payment_status' => PaymentStatus::Overdue,
        'amount_paid' => 0,
    ]);

    return compact('admin', 'supplier', 'paidOnTime', 'overdue');
}

test('admin can generate supplier statement from reports page', function () {
    ['admin' => $admin, 'supplier' => $supplier, 'paidOnTime' => $paidOnTime, 'overdue' => $overdue] = createStatementFixture();

    $from = now()->subDays(30)->toDateString();
    $to = now()->toDateString();

    $this->actingAs($admin)
        ->get(route('admin.reports.index', [
            'supplier_id' => $supplier->id,
            'from_date' => $from,
            'to_date' => $to,
        ]))
        ->assertOk()
        ->assertSee('Supplier Statement')
        ->assertSee('Statement Feeds Ltd')
        ->assertSee($paidOnTime->po_number)
        ->assertSee($overdue->po_number)
        ->assertSee('On time')
        ->assertSee('Overdue');
});

test('supplier statement service calculates payment timeliness', function () {
    ['supplier' => $supplier, 'paidOnTime' => $paidOnTime, 'overdue' => $overdue] = createStatementFixture();

    $statement = app(SupplierStatementService::class)->generate(
        $supplier,
        now()->subDays(30)->toDateString(),
        now()->toDateString(),
    );

    expect($statement['lines'])->toHaveCount(2)
        ->and($statement['summary']['total_invoiced'])->toEqual(15000.0)
        ->and($statement['summary']['total_paid'])->toEqual(10000.0)
        ->and($statement['summary']['total_outstanding'])->toEqual(5000.0)
        ->and($statement['summary']['on_time_count'])->toBe(1)
        ->and($statement['summary']['overdue_count'])->toBe(1);

    $paidLine = $statement['lines']->firstWhere('purchase_order.id', $paidOnTime->id);
    $overdueLine = $statement['lines']->firstWhere('purchase_order.id', $overdue->id);

    expect($paidLine['timeliness']['label'])->toBe('On time')
        ->and($overdueLine['timeliness']['label'])->toBe('Overdue');
});

test('supplier statement validation requires valid date range', function () {
    ['admin' => $admin, 'supplier' => $supplier] = createStatementFixture();

    $this->actingAs($admin)
        ->get(route('admin.reports.index', [
            'supplier_id' => $supplier->id,
            'from_date' => now()->toDateString(),
            'to_date' => now()->subDays(5)->toDateString(),
        ]))
        ->assertSessionHasErrors('to_date');
});

test('guest cannot access reports page', function () {
    $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
});
