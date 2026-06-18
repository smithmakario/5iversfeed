@props(['activities'])

<div class="bg-white shadow rounded-lg p-5">
    <h3 class="font-semibold text-gray-800 mb-4">Activity History</h3>
    @if ($activities->isEmpty())
        <p class="text-sm text-gray-500">No activity recorded yet.</p>
    @else
        <ol class="relative border-s border-gray-200 ms-3 space-y-6">
            @foreach ($activities as $activity)
                <li class="ms-4">
                    <span @class([
                        'absolute -start-1.5 flex h-3 w-3 rounded-full mt-1.5',
                        'bg-blue-500' => $activity->type === 'status_changed' || $activity->type === 'order_issued',
                        'bg-green-500' => $activity->type === 'payment_recorded',
                        'bg-gray-400' => $activity->type === 'note_added' || $activity->type === 'order_updated',
                        'bg-indigo-500' => $activity->type === 'order_created',
                    ])></span>
                    <time class="text-xs text-gray-400">{{ $activity->created_at->format('M d, Y g:i A') }}</time>
                    <p class="text-sm text-gray-800 mt-0.5">{{ $activity->description }}</p>
                    @if ($activity->user)
                        <p class="text-xs text-gray-500 mt-0.5">by {{ $activity->user->name }}</p>
                    @endif
                    @if (! empty($activity->metadata['payment_due_date']))
                        <p class="text-xs text-gray-500 mt-0.5">Payment due: {{ \Illuminate\Support\Carbon::parse($activity->metadata['payment_due_date'])->format('M d, Y') }}</p>
                    @endif
                    @if (! empty($activity->metadata['rejection_reason']))
                        <p class="text-xs text-red-600 mt-0.5">Reason: {{ $activity->metadata['rejection_reason'] }}</p>
                    @endif
                    @if (! empty($activity->metadata['amount']))
                        <p class="text-xs text-gray-500 mt-0.5">Amount: ₦{{ number_format($activity->metadata['amount'], 2) }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</div>
