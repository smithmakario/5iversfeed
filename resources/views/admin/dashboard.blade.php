<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-heading text-headline-sm text-on-surface">Admin Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="page-container space-y-6">
            <x-flash />

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ([
                    ['label' => 'Feed Types', 'value' => $stats['feed_types']],
                    ['label' => 'Brands', 'value' => $stats['brands']],
                    ['label' => 'Products', 'value' => $stats['products']],
                    ['label' => 'Approved Suppliers', 'value' => $stats['suppliers']],
                    ['label' => 'Pending Suppliers', 'value' => $stats['pending_suppliers']],
                    ['label' => 'Purchase Orders', 'value' => $stats['purchase_orders']],
                    ['label' => 'Open Orders', 'value' => $stats['open_orders']],
                    ['label' => 'Total Spend', 'value' => '₦'.number_format($stats['total_spend'], 2)],
                ] as $card)
                    <div class="card p-5">
                        <p class="text-label-caps text-on-surface-variant">{{ $card['label'] }}</p>
                        <p class="font-heading text-2xl font-semibold text-on-surface mt-1">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <x-payment-dashboard
                :summary="$paymentSummary"
                :due-orders="$paymentDueOrders"
                :show-supplier="true"
                order-route-name="admin.purchase-orders.show"
            />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card p-6">
                    <h3 class="font-heading text-headline-sm mb-4">Recent Purchase Orders</h3>
                    <div class="space-y-3">
                        @forelse ($recentOrders as $order)
                            <div class="flex justify-between items-center border-b pb-2">
                                <div>
                                    <a href="{{ route('admin.purchase-orders.show', $order) }}" class="link-primary">{{ $order->po_number }}</a>
                                    <p class="text-body-sm text-on-surface-variant">{{ $order->supplier->company_name }}</p>
                                </div>
                                <x-status-badge :color="$order->status->color()">{{ $order->status->label() }}</x-status-badge>
                            </div>
                        @empty
                            <p class="text-on-surface-variant text-body-sm">No purchase orders yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="font-heading text-headline-sm mb-4">Pending Supplier Applications</h3>
                    <div class="space-y-3">
                        @forelse ($pendingSuppliers as $supplier)
                            <div class="flex justify-between items-center border-b pb-2">
                                <div>
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}" class="link-primary">{{ $supplier->company_name }}</a>
                                    <p class="text-body-sm text-on-surface-variant">{{ $supplier->contact_name }} — {{ $supplier->email }}</p>
                                </div>
                                <x-status-badge color="yellow">Pending</x-status-badge>
                            </div>
                        @empty
                            <p class="text-on-surface-variant text-body-sm">No pending applications.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
