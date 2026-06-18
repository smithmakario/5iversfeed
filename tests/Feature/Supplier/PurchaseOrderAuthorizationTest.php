<?php

use App\Models\PurchaseOrder;
use App\Models\User;

test('supplier cannot view another suppliers purchase order', function () {
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
