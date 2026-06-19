<x-mail::message>
# Purchase order updated

Hello {{ $purchaseOrder->supplier->company_name }},

The buyer has updated purchase order **{{ $purchaseOrder->po_number }}** before you responded.

Please review the latest details before accepting or rejecting this order.

**PO Number:** {{ $purchaseOrder->po_number }}  
**Order date:** {{ $purchaseOrder->order_date->format('M d, Y') }}  
**Total:** ₦{{ number_format($purchaseOrder->total, 2) }}  
**Status:** {{ $purchaseOrder->status->label() }}

@if ($updatedByName)
**Updated by:** {{ $updatedByName }}
@endif

<x-mail::button :url="$actionUrl">
Review purchase order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
