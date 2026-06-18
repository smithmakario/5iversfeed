<?php

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\CreditRepaymentTimeline;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('approved supplier can view finance page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $supplierUser = User::factory()->create(['role' => UserRole::Supplier]);

    $supplier = Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Finance Test Feeds',
        'contact_name' => 'Contact',
        'email' => 'finance-supplier@example.com',
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
        'dispatched_at' => now()->subDays(5),
        'payment_due_date' => now()->addDays(25)->toDateString(),
        'payment_status' => 'on_credit',
    ]);

    $this->actingAs($supplierUser)
        ->get(route('supplier.finance.index'))
        ->assertOk()
        ->assertSee('Finance')
        ->assertSee('Total Outstanding')
        ->assertSee('Full Credit')
        ->assertSee('30 Days (30 days)');
});

test('guest cannot view supplier finance page', function () {
    $this->get(route('supplier.finance.index'))
        ->assertRedirect(route('login'));
});
