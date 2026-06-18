<x-mail::message>
# Purchase order status update

@if ($recipientRole === 'supplier')
Hello {{ $purchaseOrder->supplier->company_name }},
@else
Hello {{ $purchaseOrder->creator?->name ?? 'Admin' }},
@endif

@if ($newStatus === \App\Enums\PurchaseOrderStatus::Submitted)
A new purchase order has been issued and requires your attention.
@elseif ($newStatus === \App\Enums\PurchaseOrderStatus::Confirmed)
The supplier has accepted this purchase order.
@elseif ($newStatus === \App\Enums\PurchaseOrderStatus::Dispatched)
The supplier has dispatched this purchase order. Please prepare to receive goods.
@elseif ($newStatus === \App\Enums\PurchaseOrderStatus::Received)
This purchase order has been marked as received.
@elseif ($newStatus === \App\Enums\PurchaseOrderStatus::Rejected)
The supplier has rejected this purchase order.
@elseif ($newStatus === \App\Enums\PurchaseOrderStatus::Cancelled)
This purchase order has been cancelled.
@else
The purchase order status has been updated.
@endif

**PO Number:** {{ $purchaseOrder->po_number }}  
**Supplier:** {{ $purchaseOrder->supplier->company_name }}  
**Previous status:** {{ $previousStatus->label() }}  
**New status:** {{ $newStatus->label() }}  
**Order date:** {{ $purchaseOrder->order_date->format('M d, Y') }}  
**Total:** ₦{{ number_format($purchaseOrder->total, 2) }}

@if ($newStatus === \App\Enums\PurchaseOrderStatus::Rejected && $purchaseOrder->rejection_reason)
**Rejection reason:** {{ $purchaseOrder->rejection_reason }}
@endif

@if ($changedByName)
**Updated by:** {{ $changedByName }}
@endif

<x-mail::button :url="$actionUrl">
View purchase order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
