<?php

use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\FeedType;
use App\Models\Formulation;
use App\Models\Supplier;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->supplier = Supplier::query()->create([
        'company_name' => 'Test Feeds Co',
        'contact_name' => 'Jane Supplier',
        'email' => 'supplier-products@example.test',
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $this->admin->id,
    ]);

    $this->otherSupplier = Supplier::query()->create([
        'company_name' => 'Other Feeds Co',
        'contact_name' => 'Other Contact',
        'email' => 'other-supplier@example.test',
        'status' => SupplierStatus::Approved,
        'approved_at' => now(),
        'approved_by' => $this->admin->id,
    ]);

    $feedType = FeedType::query()->create(['name' => 'Poultry', 'slug' => 'poultry']);
    $brand = Brand::query()->create(['name' => 'Test Brand', 'slug' => 'test-brand']);

    $this->product = Formulation::query()->create([
        'supplier_id' => $this->supplier->id,
        'feed_type_id' => $feedType->id,
        'brand_id' => $brand->id,
        'name' => 'Starter Mash',
        'sku' => 'SKU-001',
        'price_per_unit' => 1000,
    ]);

    $this->otherProduct = Formulation::query()->create([
        'supplier_id' => $this->otherSupplier->id,
        'feed_type_id' => $feedType->id,
        'brand_id' => $brand->id,
        'name' => 'Other Mash',
        'sku' => 'SKU-001',
        'price_per_unit' => 2000,
    ]);
});

it('allows admins to create a product under a supplier', function () {
    $feedType = FeedType::query()->first();
    $brand = Brand::query()->first();

    $response = $this->actingAs($this->admin)->post(route('admin.suppliers.products.store', $this->supplier), [
        'feed_type_id' => $feedType->id,
        'brand_id' => $brand->id,
        'name' => 'Grower Mash',
        'sku' => 'SKU-002',
        'unit' => 'bag',
        'price_per_unit' => 1500,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.suppliers.products.index', $this->supplier));

    $this->assertDatabaseHas('formulations', [
        'supplier_id' => $this->supplier->id,
        'sku' => 'SKU-002',
        'name' => 'Grower Mash',
    ]);
});

it('allows duplicate skus across different suppliers', function () {
    expect($this->product->sku)->toBe($this->otherProduct->sku);
});

it('rejects purchase order line items from another supplier', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.purchase-orders.store'), [
        'supplier_id' => $this->supplier->id,
        'status' => 'draft',
        'order_date' => now()->toDateString(),
        'payment_option' => 'one_off',
        'items' => [
            [
                'formulation_id' => $this->otherProduct->id,
                'quantity' => 10,
                'unit_price' => 2000,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('items.0.formulation_id');
});

it('creates purchase orders with products from the selected supplier', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.purchase-orders.store'), [
        'supplier_id' => $this->supplier->id,
        'status' => 'draft',
        'order_date' => now()->toDateString(),
        'payment_option' => 'one_off',
        'items' => [
            [
                'formulation_id' => $this->product->id,
                'quantity' => 5,
                'unit_price' => 1000,
            ],
        ],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('purchase_order_items', [
        'formulation_id' => $this->product->id,
        'quantity' => 5,
    ]);
});

it('scopes nested product routes to the supplier', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.suppliers.products.show', [
        $this->supplier,
        $this->otherProduct,
    ]));

    $response->assertNotFound();
});
