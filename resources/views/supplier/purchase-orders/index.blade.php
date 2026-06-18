<x-supplier-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Purchase Orders</h2></x-slot>
    <div class="py-8">
        <div class="page-container space-y-4">
            <x-flash />
            <form method="GET" class="flex gap-2">
                <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <x-primary-button type="submit">Filter</x-primary-button>
            </form>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($purchaseOrders as $order)
                            <tr>
                                <td class="px-6 py-4"><a href="{{ route('supplier.purchase-orders.show', $order) }}" class="text-slate-700 hover:underline">{{ $order->po_number }}</a></td>
                                <td class="px-6 py-4 text-sm">{{ $order->order_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4">₦{{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :color="$order->payment_status->color()">{{ $order->payment_status->label() }}</x-status-badge>
                                    @if ($order->payment_due_date)
                                        <span class="block text-xs text-gray-500 mt-1">Due {{ $order->payment_due_date->format('M d') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4"><x-status-badge :color="$order->status->color()">{{ $order->status->label() }}</x-status-badge></td>
                                <td class="px-6 py-4 text-right">
                                    @if ($order->status === \App\Enums\PurchaseOrderStatus::Submitted)
                                        <a href="{{ route('supplier.purchase-orders.show', $order) }}" class="text-sm link-primary">Accept</a>
                                    @elseif ($order->status === \App\Enums\PurchaseOrderStatus::Confirmed)
                                        <form method="POST" action="{{ route('supplier.purchase-orders.dispatch', $order) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-indigo-700 hover:text-indigo-900">Dispatch</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $purchaseOrders->links() }}</div>
        </div>
    </div>
</x-supplier-layout>
