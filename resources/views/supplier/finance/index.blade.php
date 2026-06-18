<x-supplier-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Finance</h2></x-slot>
    <div class="py-8">
        <div class="page-container space-y-6">
            <x-flash />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase">Total Outstanding</p>
                    <p class="text-2xl font-semibold mt-1">₦{{ number_format($summary['total_outstanding'], 2) }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase">On Credit</p>
                    <p class="text-2xl font-semibold mt-1">{{ $summary['on_credit_count'] }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase">Overdue</p>
                    <p class="text-2xl font-semibold mt-1 text-red-600">{{ $summary['overdue_count'] }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase">Due Within 7 Days</p>
                    <p class="text-2xl font-semibold mt-1 text-amber-600">{{ $summary['due_soon_count'] }}</p>
                </div>
            </div>

            <form method="GET" class="flex flex-wrap gap-3 items-end bg-white shadow rounded-lg p-4">
                <div>
                    <x-input-label value="Payment Type" />
                    <select name="payment_option" class="border-gray-300 rounded-md shadow-sm text-sm mt-1">
                        <option value="">All types</option>
                        @foreach ($paymentOptions as $option)
                            <option value="{{ $option->value }}" @selected(request('payment_option') === $option->value)>{{ $option->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Payment Status" />
                    <select name="payment_status" class="border-gray-300 rounded-md shadow-sm text-sm mt-1">
                        <option value="">All statuses</option>
                        @foreach ($paymentStatuses as $status)
                            <option value="{{ $status->value }}" @selected(request('payment_status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 pb-2">
                    <input type="checkbox" name="credit_only" value="1" @checked(request()->boolean('credit_only')) class="rounded border-gray-300">
                    Credit orders only
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 pb-2">
                    <input type="checkbox" name="overdue_only" value="1" @checked(request()->boolean('overdue_only')) class="rounded border-gray-300">
                    Overdue only
                </label>
                <x-primary-button type="submit">Filter</x-primary-button>
                @if (request()->hasAny(['payment_option', 'payment_status', 'credit_only', 'overdue_only']))
                    <a href="{{ route('supplier.finance.index') }}" class="text-sm text-gray-500 hover:underline pb-2">Clear</a>
                @endif
            </form>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credit Terms</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dispatched</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            @php
                                $outstanding = max(0, (float) $order->total - (float) $order->amount_paid);
                            @endphp
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="{{ route('supplier.purchase-orders.show', $order) }}" class="text-slate-700 hover:underline">{{ $order->po_number }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $order->payment_option->label() }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($order->creditRepaymentTimeline)
                                        {{ $order->creditRepaymentTimeline->displayLabel() }}
                                    @elseif ($order->payment_option->requiresCreditTimeline())
                                        <span class="text-gray-400">—</span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $order->dispatched_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($order->payment_due_date)
                                        <span @class(['font-medium' => $order->payment_status === \App\Enums\PaymentStatus::Overdue, 'text-red-600' => $order->payment_status === \App\Enums\PaymentStatus::Overdue])>
                                            {{ $order->payment_due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-right">₦{{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-right">₦{{ number_format($order->amount_paid, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-right font-medium">₦{{ number_format($outstanding, 2) }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :color="$order->payment_status->color()">{{ $order->payment_status->label() }}</x-status-badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">No financial records match your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $orders->links() }}</div>

            <p class="text-xs text-gray-500">Credit due dates are calculated from the dispatch date based on agreed repayment terms.</p>
        </div>
    </div>
</x-supplier-layout>
