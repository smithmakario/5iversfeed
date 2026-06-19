<x-mail::message>
# Application update

Hello {{ $supplier->contact_name }},

Thank you for your interest in joining {{ config('app.name') }} as a supplier.

After reviewing your application for **{{ $supplier->company_name }}**, we are unable to approve your account at this time.

@if ($supplier->admin_notes)
**Note from our team:**  
{{ $supplier->admin_notes }}
@endif

If you have questions about this decision, please contact our support team.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
