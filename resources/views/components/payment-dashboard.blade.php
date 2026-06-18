@props([
    'summary',
    'dueOrders',
    'showSupplier' => false,
    'orderRouteName' => 'admin.purchase-orders.show',
])

<section class="space-y-6">
    <div>
        <h3 class="font-heading text-headline-sm text-on-surface mb-1">Payment Overview</h3>
        <p class="text-body-sm text-on-surface-variant">Track amounts due, upcoming deadlines, and overdue payments.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <p class="text-label-caps text-on-surface-variant">Payments Due</p>
            <p class="font-heading text-2xl font-semibold text-on-surface mt-1">{{ $summary['due_count'] }}</p>
            <p class="text-body-sm text-on-surface-variant mt-1">₦{{ number_format($summary['due_amount'], 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-label-caps text-on-surface-variant">Due Within 7 Days</p>
            <p class="font-heading text-2xl font-semibold text-amber-600 mt-1">{{ $summary['due_soon_count'] }}</p>
            <p class="text-body-sm text-on-surface-variant mt-1">Upcoming deadlines</p>
        </div>
        <div class="card p-5">
            <p class="text-label-caps text-on-surface-variant">Overdue</p>
            <p class="font-heading text-2xl font-semibold text-red-600 mt-1">{{ $summary['overdue_count'] }}</p>
            <p class="text-body-sm text-on-surface-variant mt-1">₦{{ number_format($summary['overdue_amount'], 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-label-caps text-on-surface-variant">Total Outstanding</p>
            <p class="font-heading text-2xl font-semibold text-on-surface mt-1">₦{{ number_format($summary['total_outstanding'], 2) }}</p>
            <p class="text-body-sm text-on-surface-variant mt-1">Across open POs</p>
        </div>
    </div>

    <div class="card p-6 overflow-hidden">
        <h4 class="font-heading text-headline-sm mb-4">Amount Due by Purchase Order</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-card-border text-sm">
                <thead class="bg-surface-container">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase">PO #</th>
                        @if ($showSupplier)
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase">Supplier</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase">Due Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase">Days to Due</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-on-surface-variant uppercase">Amount Due</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-card-border">
                    @forelse ($dueOrders as $row)
                        @php
                            /** @var \App\Models\PurchaseOrder $order */
                            $order = $row['purchase_order'];
                        @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route($orderRouteName, $order) }}" class="link-primary">{{ $order->po_number }}</a>
                            </td>
                            @if ($showSupplier)
                                <td class="px-4 py-3 text-on-surface-variant">{{ $order->supplier->company_name }}</td>
                            @endif
                            <td class="px-4 py-3">
                                {{ $order->payment_due_date?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($row['days_to_due'] !== null)
                                    @if ($row['is_overdue'])
                                        <span class="text-red-600 font-medium">{{ abs($row['days_to_due']) }} days overdue</span>
                                    @elseif ($row['days_to_due'] === 0)
                                        <span class="text-amber-600 font-medium">Due today</span>
                                    @else
                                        <span class="text-on-surface">{{ $row['days_to_due'] }} days</span>
                                    @endif
                                @else
                                    <span class="text-on-surface-variant">Awaiting dispatch</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($row['amount_due'], 2) }}</td>
                            <td class="px-4 py-3">
                                <x-status-badge :color="$order->payment_status->color()">{{ $order->payment_status->label() }}</x-status-badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showSupplier ? 6 : 5 }}" class="px-4 py-8 text-center text-on-surface-variant">
                                No outstanding payments on active purchase orders.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
