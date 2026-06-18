<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Credit Repayment Timelines</h2>
            <a href="{{ route('admin.settings.credit-timelines.create') }}" class="btn-primary text-sm">Add Timeline</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="page-container">
            <x-flash />
            <p class="text-sm text-gray-600 mb-4">Configure credit repayment options available when creating purchase orders with credit terms.</p>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Label</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($timelines as $timeline)
                            <tr>
                                <td class="px-6 py-4">{{ $timeline->label }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $timeline->days }} days</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :color="$timeline->is_active ? 'green' : 'gray'">{{ $timeline->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.settings.credit-timelines.edit', $timeline) }}" class="link-primary">Edit</a>
                                    <form action="{{ route('admin.settings.credit-timelines.destroy', $timeline) }}" method="POST" class="inline" onsubmit="return confirm('Delete this timeline?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No credit repayment timelines configured yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $timelines->links() }}</div>
        </div>
    </div>
</x-admin-layout>
