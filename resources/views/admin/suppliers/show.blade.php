<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ $supplier->company_name }}</h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.suppliers.products.create', $supplier) }}" class="btn-primary text-sm">Add Product</a>
                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="link-primary">Edit</a>
            </div>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-start gap-6">
                    @if ($supplier->user)
                        <x-user-avatar :user="$supplier->user" size="lg" class="mt-1" />
                    @endif
                    <div class="grid grid-cols-2 gap-4 text-sm flex-1">
                        <div><span class="text-gray-500">Contact</span><p>{{ $supplier->contact_name }}</p></div>
                        <div><span class="text-gray-500">Email</span><p>{{ $supplier->email }}</p></div>
                        <div><span class="text-gray-500">Phone</span><p>{{ $supplier->phone ?? '—' }}</p></div>
                        <div><span class="text-gray-500">Tax ID</span><p>{{ $supplier->tax_id ?? '—' }}</p></div>
                        <div class="col-span-2"><span class="text-gray-500">Address</span><p>{{ collect([$supplier->address, $supplier->city, $supplier->state, $supplier->country])->filter()->join(', ') ?: '—' }}</p></div>
                    </div>
                    <x-status-badge :color="$supplier->status->color()">{{ $supplier->status->label() }}</x-status-badge>
                </div>
                @if ($supplier->admin_notes)<p class="mt-4 text-sm text-gray-600"><strong>Admin notes:</strong> {{ $supplier->admin_notes }}</p>@endif
                @if ($supplier->status->value === 'pending')
                    <div class="mt-6 flex gap-3">
                        <form method="POST" action="{{ route('admin.suppliers.approve', $supplier) }}">@csrf<button class="btn-primary text-sm">Approve</button></form>
                        <form method="POST" action="{{ route('admin.suppliers.reject', $supplier) }}" class="flex gap-2 items-center">@csrf<input type="text" name="admin_notes" placeholder="Rejection reason" class="border-gray-300 rounded-md text-sm"><button class="px-4 py-2 bg-red-600 text-white rounded-md text-sm">Reject</button></form>
                    </div>
                @elseif ($supplier->status->value === 'approved')
                    <form method="POST" action="{{ route('admin.suppliers.suspend', $supplier) }}" class="mt-6">@csrf<button class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm">Suspend</button></form>
                @endif
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold">Products ({{ $supplier->products->count() }})</h3>
                    <a href="{{ route('admin.suppliers.products.index', $supplier) }}" class="link-primary text-sm">View all</a>
                </div>
                @forelse ($supplier->products->take(5) as $product)
                    <div class="flex justify-between border-b py-2 text-sm">
                        <div>
                            <a href="{{ route('admin.suppliers.products.show', [$supplier, $product]) }}" class="link-primary">{{ $product->name }}</a>
                            <p class="text-gray-500">{{ $product->sku }} · {{ $product->feedType->name }} / {{ $product->brand->name }}</p>
                        </div>
                        <span>₦{{ number_format($product->price_per_unit, 2) }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No products yet.</p>
                @endforelse
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="font-semibold mb-3">Purchase Orders ({{ $supplier->purchaseOrders->count() }})</h3>
                @forelse ($supplier->purchaseOrders->take(5) as $order)
                    <div class="flex justify-between border-b py-2 text-sm">
                        <a href="{{ route('admin.purchase-orders.show', $order) }}" class="link-primary">{{ $order->po_number }}</a>
                        <x-status-badge :color="$order->status->color()">{{ $order->status->label() }}</x-status-badge>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No purchase orders.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
