<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ $purchaseOrder->po_number }}</h2>
            @if (! $purchaseOrder->status->isTerminal() && $purchaseOrder->status !== \App\Enums\PurchaseOrderStatus::Dispatched)
                <a href="{{ route('admin.purchase-orders.edit', $purchaseOrder) }}" class="link-primary">Edit</a>
            @endif
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div><span class="text-gray-500">Supplier</span><p>{{ $purchaseOrder->supplier->company_name }}</p></div>
                            <div><span class="text-gray-500">Order Date</span><p>{{ $purchaseOrder->order_date->format('M d, Y') }}</p></div>
                            <div><span class="text-gray-500">Expected Delivery</span><p>{{ $purchaseOrder->expected_delivery_date?->format('M d, Y') ?? '—' }}</p></div>
                            <div><span class="text-gray-500">Dispatched</span><p>{{ $purchaseOrder->dispatched_at?->format('M d, Y') ?? '—' }}</p></div>
                            <div><span class="text-gray-500">Received</span><p>{{ $purchaseOrder->received_at?->format('M d, Y') ?? '—' }}</p></div>
                            <div><span class="text-gray-500">Created By</span><p>{{ $purchaseOrder->creator->name }}</p></div>
                        </div>
                        <x-status-badge :color="$purchaseOrder->status->color()">{{ $purchaseOrder->status->label() }}</x-status-badge>
                    </div>

                    <x-purchase-order-workflow :purchase-order="$purchaseOrder" />

                    <div class="flex flex-wrap gap-3 mb-6">
                        @if ($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Draft || $purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Rejected)
                            <form method="POST" action="{{ route('admin.purchase-orders.issue', $purchaseOrder) }}">
                                @csrf
                                <x-primary-button>{{ $purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Rejected ? 'Re-issue to Supplier' : 'Issue to Supplier' }}</x-primary-button>
                            </form>
                        @endif
                        @if (in_array($purchaseOrder->status, [\App\Enums\PurchaseOrderStatus::Draft, \App\Enums\PurchaseOrderStatus::Submitted, \App\Enums\PurchaseOrderStatus::Confirmed]))
                            <form method="POST" action="{{ route('admin.purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Cancel this purchase order?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm">Cancel PO</button>
                            </form>
                        @endif
                        @if ($purchaseOrder->canAdminReceive())
                            <form method="POST" action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}">
                                @csrf
                                <x-primary-button>Acknowledge Receipt</x-primary-button>
                            </form>
                        @endif
                    </div>

                    @if ($purchaseOrder->rejection_reason)
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm">
                            <strong class="text-red-800">Rejection reason:</strong>
                            <p class="mt-1 text-red-700">{{ $purchaseOrder->rejection_reason }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-semibold text-sm mb-3">Payment Terms</h3>
                            <dl class="text-sm space-y-2">
                                <div class="flex justify-between"><dt class="text-gray-500">Option</dt><dd>{{ $purchaseOrder->payment_option->label() }}</dd></div>
                                @if ($purchaseOrder->payment_option->requiresUpfrontAmount())
                                    <div class="flex justify-between"><dt class="text-gray-500">Upfront Amount</dt><dd>₦{{ number_format($purchaseOrder->upfront_amount, 2) }}</dd></div>
                                @endif
                                @if ($purchaseOrder->creditRepaymentTimeline)
                                    <div class="flex justify-between"><dt class="text-gray-500">Credit Timeline</dt><dd>{{ $purchaseOrder->creditRepaymentTimeline->displayLabel() }}</dd></div>
                                @endif
                                @if ($purchaseOrder->creditAmount() > 0)
                                    <div class="flex justify-between"><dt class="text-gray-500">Credit Amount</dt><dd>₦{{ number_format($purchaseOrder->creditAmount(), 2) }}</dd></div>
                                @endif
                                @if ($purchaseOrder->payment_due_date)
                                    <div class="flex justify-between"><dt class="text-gray-500">Payment Due</dt><dd>{{ $purchaseOrder->payment_due_date->format('M d, Y') }}</dd></div>
                                @endif
                            </dl>
                            @if ($purchaseOrder->creditRepaymentTimeline)
                                <p class="text-xs text-gray-500 mt-3">Credit period starts on dispatch date.</p>
                            @endif
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-semibold text-sm mb-3">Payment Status</h3>
                            <dl class="text-sm space-y-2">
                                <div class="flex justify-between items-center">
                                    <dt class="text-gray-500">Status</dt>
                                    <dd><x-status-badge :color="$purchaseOrder->payment_status->color()">{{ $purchaseOrder->payment_status->label() }}</x-status-badge></dd>
                                </div>
                                <div class="flex justify-between"><dt class="text-gray-500">Total</dt><dd>₦{{ number_format($purchaseOrder->total, 2) }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Amount Paid</dt><dd>₦{{ number_format($purchaseOrder->amount_paid, 2) }}</dd></div>
                                <div class="flex justify-between font-semibold"><dt class="text-gray-500">Outstanding</dt><dd>₦{{ number_format($purchaseOrder->amountOutstanding(), 2) }}</dd></div>
                            </dl>
                            @if ($purchaseOrder->amountOutstanding() > 0)
                                <form method="POST" action="{{ route('admin.purchase-orders.record-payment', $purchaseOrder) }}" class="mt-4 flex gap-2 items-end">
                                    @csrf
                                    <div class="flex-1">
                                        <x-input-label for="amount" value="Record Payment (₦)" />
                                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" :max="$purchaseOrder->amountOutstanding()" class="block mt-1 w-full" required />
                                    </div>
                                    <x-primary-button type="submit">Record</x-primary-button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead><tr><th class="text-left py-2">Product</th><th class="text-right py-2">Qty</th><th class="text-right py-2">Unit Price</th><th class="text-right py-2">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($purchaseOrder->items as $item)
                                <tr class="border-t"><td class="py-2">{{ $item->product_name }}</td><td class="text-right">{{ $item->quantity }}</td><td class="text-right">₦{{ number_format($item->unit_price, 2) }}</td><td class="text-right">₦{{ number_format($item->subtotal, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t font-medium"><td colspan="3" class="text-right py-2">Subtotal</td><td class="text-right">₦{{ number_format($purchaseOrder->subtotal, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-right py-2">Tax</td><td class="text-right">₦{{ number_format($purchaseOrder->tax_amount, 2) }}</td></tr>
                            <tr class="font-semibold"><td colspan="3" class="text-right py-2">Total</td><td class="text-right">₦{{ number_format($purchaseOrder->total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                    @if ($purchaseOrder->notes)<p class="mt-4 text-sm"><strong>Notes:</strong> {{ $purchaseOrder->notes }}</p>@endif
                    @if ($purchaseOrder->supplier_notes)<p class="mt-2 text-sm"><strong>Supplier notes:</strong> {{ $purchaseOrder->supplier_notes }}</p>@endif
                </div>

                <div class="lg:col-span-1">
                    <x-purchase-order-activity-timeline :activities="$purchaseOrder->activities" />
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
