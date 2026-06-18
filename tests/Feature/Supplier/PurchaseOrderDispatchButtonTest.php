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
use Illuminate\Support\Facades\Hash;

function createDispatchButtonFixture(PurchaseOrderStatus $status): array
{
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin-dispatch@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Admin,
        'email_verified_at' => now(),
    ]);

    $supplierUser = User::query()->create([
        'name' => 'Supplier User',
        'email' => 'supplier-dispatch@example.com',
        'password' => Hash::make('password'),
        'role' => UserRole::Supplier,
        'email_verified_at' => now(),
    ]);

    $supplier = Supplier::query()->create([
        'user_id' => $supplierUser->id,
        'company_name' => 'Dispatch Feeds Ltd',
        'contact_name' => 'Supplier Contact',
        'email' => 'supplier-dispatch@example.com',
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $feedType = FeedType::query()->create(['name' => 'Poultry', 'slug' => 'poultry-dispatch']);
    $brand = Brand::query()->create(['name' => 'Brand', 'slug' => 'brand-dispatch']);
    $formulation = Formulation::query()->create([
        'feed_type_id' => $feedType->id,
        'brand_id' => $brand->id,
        'supplier_id' => $supplier->id,
        'name' => 'Starter Mash',
        'sku' => 'SKU-DISPATCH-001',
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

    return compact('supplierUser', 'purchaseOrder');
}

test('confirmed purchase order shows dispatch button for owning supplier', function () {
    ['supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createDispatchButtonFixture(PurchaseOrderStatus::Confirmed);

    $this->actingAs($supplierUser)
        ->get(route('supplier.purchase-orders.show', $purchaseOrder))
        ->assertOk()
        ->assertSee('Mark as Dispatched', false)
        ->assertSee('Your Actions', false);
});

test('submitted purchase order shows accept button not dispatch', function () {
    ['supplierUser' => $supplierUser, 'purchaseOrder' => $purchaseOrder] = createDispatchButtonFixture(PurchaseOrderStatus::Submitted);

    $this->actingAs($supplierUser)
        ->get(route('supplier.purchase-orders.show', $purchaseOrder))
        ->assertOk()
        ->assertSee('Accept Order', false)
        ->assertDontSee('Mark as Dispatched');
});
