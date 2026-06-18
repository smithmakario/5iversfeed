<x-supplier-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Supplier Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="page-container space-y-6">
            <x-flash />

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold">{{ $supplier->company_name }}</h3>
                <p class="text-gray-600 mt-1">{{ $supplier->contact_name }} · {{ $supplier->email }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ([
                    ['label' => 'Total Orders', 'value' => $stats['total_orders']],
                    ['label' => 'Pending Orders', 'value' => $stats['pending_orders']],
                    ['label' => 'Received Orders', 'value' => $stats['received_orders']],
                    ['label' => 'Total Value', 'value' => '₦'.number_format($stats['total_value'], 2)],
                ] as $card)
                    <div class="bg-white rounded-lg shadow p-5">
                        <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <x-payment-dashboard
                :summary="$paymentSummary"
                :due-orders="$paymentDueOrders"
                order-route-name="supplier.purchase-orders.show"
            />

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Purchase Orders</h3>
                <div class="space-y-3">
                    @forelse ($recentOrders as $order)
                        <div class="flex justify-between items-center border-b pb-2">
                            <a href="{{ route('supplier.purchase-orders.show', $order) }}" class="text-slate-700 font-medium">{{ $order->po_number }}</a>
                            <x-status-badge :color="$order->status->color()">{{ $order->status->label() }}</x-status-badge>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No purchase orders assigned yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-supplier-layout>
