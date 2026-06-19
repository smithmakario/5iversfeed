<x-mail::message>
# New supplier application

Hello Admin,

A new supplier application has been submitted and is awaiting review.

**Company:** {{ $supplier->company_name }}  
**Contact:** {{ $supplier->contact_name }}  
**Email:** {{ $supplier->email }}  
@if ($supplier->phone)
**Phone:** {{ $supplier->phone }}  
@endif
**Submitted:** {{ $supplier->created_at->format('M d, Y g:i A') }}

<x-mail::button :url="$actionUrl">
Review application
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
