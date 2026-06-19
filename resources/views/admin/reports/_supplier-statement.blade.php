@if ($statement)
    <div id="supplier-statement" class="bg-white shadow rounded-lg p-6 print:shadow-none print:border print:border-gray-300">
        <div class="flex flex-wrap justify-between items-start gap-4 mb-6 print:mb-4">
            <div>
                <h3 class="font-semibold text-lg">Supplier Statement</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $statement['supplier']->company_name }}</p>
                <p class="text-sm text-gray-500">
                    Period: {{ \Illuminate\Support\Carbon::parse($statement['from_date'])->format('M d, Y') }}
                    – {{ \Illuminate\Support\Carbon::parse($statement['to_date'])->format('M d, Y') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">Generated {{ $statement['generated_at']->format('M d, Y g:i A') }}</p>
            </div>
            <button type="button" onclick="window.print()" class="btn-primary text-sm print:hidden">Print Statement</button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 uppercase">Total Invoiced</p>
                <p class="text-lg font-semibold">₦{{ number_format($statement['summary']['total_invoiced'], 2) }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 uppercase">Total Paid</p>
                <p class="text-lg font-semibold text-green-700">₦{{ number_format($statement['summary']['total_paid'], 2) }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 uppercase">Outstanding</p>
                <p class="text-lg font-semibold text-red-700">₦{{ number_format($statement['summary']['total_outstanding'], 2) }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 uppercase">Payment Compliance</p>
                <p class="text-sm mt-1">
                    <span class="text-green-700">{{ $statement['summary']['on_time_count'] }} on time</span> ·
                    <span class="text-red-700">{{ $statement['summary']['late_count'] }} late</span> ·
                    <span class="text-red-600">{{ $statement['summary']['overdue_count'] }} overdue</span> ·
                    <span class="text-amber-600">{{ $statement['summary']['pending_count'] }} pending</span>
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Terms</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Payment</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">vs Due Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($statement['lines'] as $line)
                        @php
                            $order = $line['purchase_order'];
                        @endphp
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $order->order_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <a href="{{ route('admin.purchase-orders.show', $order) }}" class="link-primary print:no-underline print:text-gray-900">{{ $order->po_number }}</a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $order->status->label() }}</td>
                            <td class="px-4 py-3">
                                {{ $order->payment_option->label() }}
                                @if ($order->creditRepaymentTimeline)
                                    <span class="text-gray-500">({{ $order->creditRepaymentTimeline->displayLabel() }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">₦{{ number_format($line['total'], 2) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">₦{{ number_format($line['amount_paid'], 2) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">₦{{ number_format($line['outstanding'], 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $line['payment_due_date']?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $line['last_payment_date']?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-status-badge :color="$line['timeliness']['color']">{{ $line['timeliness']['label'] }}</x-status-badge>
                                <p class="text-xs text-gray-500 mt-1">{{ $line['timeliness']['detail'] }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-gray-500">No purchase orders found for this supplier in the selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($statement['lines']->isNotEmpty())
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right">Totals</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($statement['summary']['total_invoiced'], 2) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($statement['summary']['total_paid'], 2) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($statement['summary']['total_outstanding'], 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endif
