@php
    use App\Enums\PurchaseOrderStatus;
    $canAccept = $purchaseOrder->status === PurchaseOrderStatus::Submitted;
    $canDispatch = $purchaseOrder->status === PurchaseOrderStatus::Confirmed;
@endphp
<x-supplier-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800">{{ $purchaseOrder->po_number }}</h2>
            <div class="flex flex-wrap items-center gap-3">
                <x-status-badge :color="$purchaseOrder->status->color()">{{ $purchaseOrder->status->label() }}</x-status-badge>
                @if ($canAccept)
                    <form method="POST" action="{{ route('supplier.purchase-orders.confirm', $purchaseOrder) }}">
                        @csrf
                        <button type="submit" class="btn-primary">Accept Order</button>
                    </form>
                @endif
                @if ($canDispatch)
                    <form method="POST" action="{{ route('supplier.purchase-orders.dispatch', $purchaseOrder) }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <x-input-label for="header_dispatched_at" value="Dispatch date" />
                            <x-text-input id="header_dispatched_at" name="dispatched_at" type="date" class="block mt-1" :value="old('dispatched_at', now()->toDateString())" max="{{ now()->toDateString() }}" :min="$purchaseOrder->order_date->toDateString()" required />
                            <x-input-error :messages="$errors->get('dispatched_at')" class="mt-2" />
                        </div>
                        <button type="submit" class="btn-primary">Mark as Dispatched</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
                    <div class="text-sm space-y-1 mb-4">
                        <p><span class="text-gray-500">Order Date:</span> {{ $purchaseOrder->order_date->format('M d, Y') }}</p>
                        <p><span class="text-gray-500">Expected Delivery:</span> {{ $purchaseOrder->expected_delivery_date?->format('M d, Y') ?? '—' }}</p>
                        @if ($purchaseOrder->dispatched_at)
                            <p><span class="text-gray-500">Dispatched:</span> {{ $purchaseOrder->dispatched_at->format('M d, Y') }}</p>
                        @endif
                        @if ($purchaseOrder->received_at)
                            <p><span class="text-gray-500">Received by buyer:</span> {{ $purchaseOrder->received_at->format('M d, Y') }}</p>
                        @endif
                    </div>

                    <x-purchase-order-workflow :purchase-order="$purchaseOrder" />

                    <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <h3 class="font-semibold text-sm mb-3">Your Actions</h3>
                        @if ($canDispatch)
                            <p class="text-sm text-gray-600 mb-3">This order is accepted. Mark it as dispatched to start the credit repayment countdown.</p>
                            <form method="POST" action="{{ route('supplier.purchase-orders.dispatch', $purchaseOrder) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <div>
                                    <x-input-label for="dispatched_at" value="Dispatch date" />
                                    <x-text-input id="dispatched_at" name="dispatched_at" type="date" class="block mt-1" :value="old('dispatched_at', now()->toDateString())" max="{{ now()->toDateString() }}" :min="$purchaseOrder->order_date->toDateString()" required />
                                    <x-input-error :messages="$errors->get('dispatched_at')" class="mt-2" />
                                </div>
                                <button type="submit" class="btn-primary text-base px-6 py-3">Mark as Dispatched</button>
                            </form>
                        @elseif ($canAccept)
                            <form method="POST" action="{{ route('supplier.purchase-orders.confirm', $purchaseOrder) }}" class="mb-4">
                                @csrf
                                <button type="submit" class="btn-primary">Accept Order</button>
                                <p class="text-xs text-gray-500 mt-2">Accept this order first — the dispatch button appears after acceptance.</p>
                            </form>
                            <form method="POST" action="{{ route('supplier.purchase-orders.reject', $purchaseOrder) }}" class="p-4 border border-red-200 rounded-lg bg-white">
                                @csrf
                                <x-input-label for="rejection_reason" value="Reject Order (provide reason)" />
                                <textarea id="rejection_reason" name="rejection_reason" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('rejection_reason') }}</textarea>
                                <x-input-error :messages="$errors->get('rejection_reason')" class="mt-2" />
                                <button type="submit" class="mt-2 px-4 py-2 bg-red-600 text-white rounded-md text-sm">Reject Order</button>
                            </form>
                        @elseif ($purchaseOrder->status === PurchaseOrderStatus::Dispatched)
                            <p class="text-sm text-purple-700">This order has been dispatched. Awaiting buyer receipt confirmation.</p>
                        @elseif ($purchaseOrder->status === PurchaseOrderStatus::Received)
                            <p class="text-sm text-green-700">This order has been received by the buyer.</p>
                        @else
                            <p class="text-sm text-gray-500">No actions available for this order at this stage.</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-lg text-sm">
                        <div>
                            <h3 class="font-semibold mb-2">Payment Terms</h3>
                            <p><span class="text-gray-500">Option:</span> {{ $purchaseOrder->payment_option->label() }}</p>
                            @if ($purchaseOrder->payment_option->requiresUpfrontAmount())
                                <p><span class="text-gray-500">Upfront:</span> ₦{{ number_format($purchaseOrder->upfront_amount, 2) }}</p>
                            @endif
                            @if ($purchaseOrder->creditRepaymentTimeline)
                                <p><span class="text-gray-500">Credit Timeline:</span> {{ $purchaseOrder->creditRepaymentTimeline->displayLabel() }}</p>
                            @endif
                            @if ($creditAmount > 0)
                                <p><span class="text-gray-500">Credit Amount:</span> ₦{{ number_format($creditAmount, 2) }}</p>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold mb-2">Payment Tracking</h3>
                            <p class="flex items-center gap-2">
                                <span class="text-gray-500">Status:</span>
                                <x-status-badge :color="$purchaseOrder->payment_status->color()">{{ $purchaseOrder->payment_status->label() }}</x-status-badge>
                            </p>
                            <p><span class="text-gray-500">Total:</span> ₦{{ number_format($purchaseOrder->total, 2) }}</p>
                            <p><span class="text-gray-500">Paid:</span> ₦{{ number_format($purchaseOrder->amount_paid, 2) }}</p>
                            <p><span class="text-gray-500">Outstanding:</span> ₦{{ number_format($amountOutstanding, 2) }}</p>
                            @if ($purchaseOrder->payment_due_date)
                                <p><span class="text-gray-500">Due Date:</span> {{ $purchaseOrder->payment_due_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>

                    <table class="min-w-full text-sm mb-4">
                        <thead><tr><th class="text-left py-2">Product</th><th class="text-right py-2">Qty</th><th class="text-right py-2">Unit Price</th><th class="text-right py-2">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($purchaseOrder->items as $item)
                                <tr class="border-t"><td class="py-2">{{ $item->product_name }}</td><td class="text-right">{{ $item->quantity }}</td><td class="text-right">₦{{ number_format($item->unit_price, 2) }}</td><td class="text-right">₦{{ number_format($item->subtotal, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr class="border-t font-semibold"><td colspan="3" class="text-right py-2">Total</td><td class="text-right">₦{{ number_format($purchaseOrder->total, 2) }}</td></tr></tfoot>
                    </table>

                    <form method="POST" action="{{ route('supplier.purchase-orders.notes', $purchaseOrder) }}">
                        @csrf
                        <x-input-label for="supplier_notes" value="Your Notes" />
                        <textarea id="supplier_notes" name="supplier_notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('supplier_notes', $purchaseOrder->supplier_notes) }}</textarea>
                        <x-primary-button class="mt-2">Save Notes</x-primary-button>
                    </form>
                </div>

                <div class="lg:col-span-1">
                    <x-purchase-order-activity-timeline :activities="$purchaseOrder->activities" />
                </div>
            </div>
        </div>
    </div>
</x-supplier-layout>
