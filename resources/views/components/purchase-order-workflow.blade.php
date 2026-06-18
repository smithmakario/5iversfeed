@props(['purchaseOrder'])

@php
    $steps = [
        ['key' => 'submitted', 'label' => 'Issued'],
        ['key' => 'confirmed', 'label' => 'Accepted'],
        ['key' => 'dispatched', 'label' => 'Dispatched'],
        ['key' => 'received', 'label' => 'Received'],
    ];
    $current = $purchaseOrder->status->value;
    $stepOrder = array_column($steps, 'key');
    $currentIndex = array_search($current, $stepOrder, true);
    if ($current === 'rejected' || $current === 'cancelled' || $current === 'draft') {
        $currentIndex = false;
    }
@endphp

<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Order Progress</h3>
    <div class="flex items-center gap-1">
        @foreach ($steps as $index => $step)
            @php
                $isComplete = $currentIndex !== false && $index < $currentIndex;
                $isCurrent = $current === $step['key'];
            @endphp
            <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                <div @class([
                    'flex items-center justify-center w-8 h-8 rounded-full text-xs font-semibold shrink-0',
                    'bg-green-600 text-white' => $isComplete,
                    'bg-indigo-600 text-white ring-2 ring-indigo-200' => $isCurrent,
                    'bg-gray-200 text-gray-500' => ! $isComplete && ! $isCurrent,
                ])>
                    @if ($isComplete)
                        ✓
                    @else
                        {{ $index + 1 }}
                    @endif
                </div>
                <span @class(['ml-2 text-xs whitespace-nowrap', 'font-semibold text-indigo-700' => $isCurrent, 'text-gray-500' => ! $isCurrent])>{{ $step['label'] }}</span>
                @if (! $loop->last)
                    <div @class(['flex-1 h-0.5 mx-2', 'bg-green-500' => $isComplete, 'bg-gray-200' => ! $isComplete])></div>
                @endif
            </div>
        @endforeach
    </div>
    @if ($current === 'submitted')
        <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2 mt-3">Accept this order first, then you can mark it as dispatched.</p>
    @elseif ($purchaseOrder->canSupplierDispatch())
        <p class="text-sm text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md px-3 py-2 mt-3">Order accepted — mark as dispatched to start the credit repayment period.</p>
    @elseif ($current === 'dispatched')
        <p class="text-sm text-purple-700 bg-purple-50 border border-purple-200 rounded-md px-3 py-2 mt-3">Dispatched — awaiting buyer receipt confirmation.</p>
    @endif
</div>
