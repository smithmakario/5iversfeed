<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Application Status</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($supplier?->status->value === 'pending')
                    <p class="text-gray-700">Your supplier application for <strong>{{ $supplier->company_name }}</strong> is pending admin review. You will be able to access the supplier portal once approved.</p>
                @elseif ($supplier?->status->value === 'rejected')
                    <p class="text-gray-700">Your supplier application was not approved.@if($supplier->admin_notes) Reason: {{ $supplier->admin_notes }}@endif</p>
                @else
                    <p class="text-gray-700">Your supplier account is not active. Please contact support.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
