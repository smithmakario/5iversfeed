<x-mail::message>
# Application received

Hello {{ $supplier->contact_name }},

Thank you for applying to join {{ config('app.name') }} as a supplier.

We have received your application for **{{ $supplier->company_name }}** and our team will review it shortly. You will receive another email once a decision has been made.

In the meantime, you can sign in to check your application status.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
