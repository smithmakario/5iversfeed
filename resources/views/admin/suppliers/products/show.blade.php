<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500">
                    <a href="{{ route('admin.suppliers.show', $supplier) }}" class="link-primary">{{ $supplier->company_name }}</a>
                    ·
                    <a href="{{ route('admin.suppliers.products.index', $supplier) }}" class="link-primary">Products</a>
                </p>
                <h2 class="font-semibold text-xl text-gray-800">{{ $product->name }}</h2>
            </div>
            <a href="{{ route('admin.suppliers.products.edit', [$supplier, $product]) }}" class="link-primary">Edit</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">SKU</span><p class="font-medium">{{ $product->sku }}</p></div>
                    <div><span class="text-gray-500">Price</span><p class="font-medium">₦{{ number_format($product->price_per_unit, 2) }} / {{ $product->unit }}</p></div>
                    <div><span class="text-gray-500">Feed Type</span><p>{{ $product->feedType->name }}</p></div>
                    <div><span class="text-gray-500">Brand</span><p>{{ $product->brand->name }}</p></div>
                    <div><span class="text-gray-500">Composition</span><p>Protein {{ $product->protein_percentage ?? '—' }}% · Fiber {{ $product->fiber_percentage ?? '—' }}%</p></div>
                    <div><span class="text-gray-500">Status</span><p>{{ $product->is_active ? 'Active' : 'Inactive' }}</p></div>
                </div>
                @if ($product->description)<p class="text-gray-700">{{ $product->description }}</p>@endif
                @if ($product->ingredients)
                    <div>
                        <h4 class="font-medium mb-1">Ingredients</h4>
                        <p class="text-gray-600 whitespace-pre-line">{{ $product->ingredients }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
