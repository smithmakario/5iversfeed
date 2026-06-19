<x-mail::message>
# Account approved

Hello {{ $supplier->contact_name }},

Great news! Your supplier application for **{{ $supplier->company_name }}** has been approved.

You can now sign in and access your supplier dashboard to manage products, view purchase orders, and track payments.

<x-mail::button :url="$actionUrl">
Sign in to your account
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
