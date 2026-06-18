<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500">
                    <a href="{{ route('admin.suppliers.show', $supplier) }}" class="link-primary">{{ $supplier->company_name }}</a>
                </p>
                <h2 class="font-semibold text-xl text-gray-800">Products</h2>
            </div>
            <a href="{{ route('admin.suppliers.products.create', $supplier) }}" class="btn-primary text-sm">Add Product</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="page-container">
            <x-flash />
            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type / Brand</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-6 py-4 font-mono text-sm">{{ $product->sku }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.suppliers.products.show', [$supplier, $product]) }}" class="link-primary">{{ $product->name }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $product->feedType->name }} / {{ $product->brand->name }}</td>
                                <td class="px-6 py-4">₦{{ number_format($product->price_per_unit, 2) }}</td>
                                <td class="px-6 py-4">
                                    @if ($product->is_active)
                                        <span class="text-green-700 text-sm">Active</span>
                                    @else
                                        <span class="text-gray-500 text-sm">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.suppliers.products.edit', [$supplier, $product]) }}" class="link-primary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 text-sm">No products yet. Add the first product this supplier sells.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($products->hasPages())
                <div class="mt-4">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
