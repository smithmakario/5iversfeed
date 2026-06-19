<x-mail::message>
# Payment reminder

@if ($recipientRole === 'supplier')
Hello {{ $purchaseOrder->supplier->company_name }},
@else
Hello {{ $purchaseOrder->creator?->name ?? 'Admin' }},
@endif

@if ($reminderType === 'due_soon')
Payment for the following purchase order is due in **{{ $daysToDue }} days**.
@elseif ($reminderType === 'due_today')
Payment for the following purchase order is **due today**.
@elseif ($reminderType === 'overdue')
Payment for the following purchase order is **overdue**.
@else
This is a reminder about an outstanding payment.
@endif

**PO Number:** {{ $purchaseOrder->po_number }}  
**Supplier:** {{ $purchaseOrder->supplier->company_name }}  
**Amount due:** ₦{{ number_format($amountDue, 2) }}  
@if ($purchaseOrder->payment_due_date)
**Due date:** {{ $purchaseOrder->payment_due_date->format('M d, Y') }}  
@endif
**Order total:** ₦{{ number_format($purchaseOrder->total, 2) }}  
**Amount paid:** ₦{{ number_format($purchaseOrder->amount_paid, 2) }}

<x-mail::button :url="$actionUrl">
View purchase order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
