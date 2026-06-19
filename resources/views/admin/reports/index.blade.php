<x-admin-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Reports & Analytics</h2></x-slot>
    <div class="py-8">
        <div class="page-container space-y-6">
            <div class="bg-white shadow rounded-lg p-6 print:hidden">
                <h3 class="font-semibold mb-1">Supplier Statement</h3>
                <p class="text-sm text-gray-500 mb-4">Generate a statement by supplier and order date range. Shows payment due dates and whether each order was paid on time.</p>

                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="min-w-[220px]">
                        <x-input-label value="Supplier" />
                        <select name="supplier_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                            <option value="">Select supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) ($statementFilters['supplier_id'] ?? '') === (string) $supplier->id)>
                                    {{ $supplier->company_name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="from_date" value="From Date" />
                        <x-text-input id="from_date" name="from_date" type="date" class="block mt-1" :value="$statementFilters['from_date'] ?? ''" required />
                        <x-input-error :messages="$errors->get('from_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="to_date" value="To Date" />
                        <x-text-input id="to_date" name="to_date" type="date" class="block mt-1" :value="$statementFilters['to_date'] ?? ''" required />
                        <x-input-error :messages="$errors->get('to_date')" class="mt-2" />
                    </div>
                    <x-primary-button type="submit">Generate Statement</x-primary-button>
                </form>
            </div>

            @include('admin.reports._supplier-statement')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 print:hidden">
                <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Total Spend (Received)</p><p class="text-2xl font-semibold">₦{{ number_format($totalSpend, 2) }}</p></div>
                <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Average Order Value</p><p class="text-2xl font-semibold">₦{{ number_format($averageOrderValue ?? 0, 2) }}</p></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 print:hidden">
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
