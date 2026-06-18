<x-admin-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Reports & Analytics</h2></x-slot>
    <div class="py-8">
        <div class="page-container space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Total Spend (Received)</p><p class="text-2xl font-semibold">₦{{ number_format($totalSpend, 2) }}</p></div>
                <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Average Order Value</p><p class="text-2xl font-semibold">₦{{ number_format($averageOrderValue ?? 0, 2) }}</p></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold mb-4">Orders by Status</h3>
                    @forelse ($ordersByStatus as $status => $count)
                        <div class="flex justify-between border-b py-2 text-sm"><span>{{ ucfirst($status) }}</span><span class="font-medium">{{ $count }}</span></div>
                    @empty
                        <p class="text-gray-500 text-sm">No data yet.</p>
                    @endforelse
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold mb-4">Monthly Spend (Received)</h3>
                    @forelse ($spendByMonth as $row)
                        <div class="flex justify-between border-b py-2 text-sm"><span>{{ $row->month }}</span><span>₦{{ number_format($row->total_spend, 2) }} ({{ $row->order_count }} orders)</span></div>
                    @empty
                        <p class="text-gray-500 text-sm">No received orders yet.</p>
                    @endforelse
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold mb-4">Top Suppliers by Spend</h3>
                    @forelse ($topSuppliers as $supplier)
                        <div class="flex justify-between border-b py-2 text-sm">
                            <span>{{ $supplier->company_name }} ({{ $supplier->purchase_orders_count }} POs)</span>
                            <span>₦{{ number_format($supplier->total_spend ?? 0, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No supplier data yet.</p>
                    @endforelse
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold mb-4">Top Products by Units Ordered</h3>
                    @forelse ($topFormulations as $formulation)
                        <div class="flex justify-between border-b py-2 text-sm">
                            <span>{{ $formulation->name }}</span>
                            <span>{{ number_format($formulation->units_ordered ?? 0) }} units</span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No formulation data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
