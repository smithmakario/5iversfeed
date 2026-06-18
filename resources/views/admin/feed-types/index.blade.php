<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Feed Types</h2>
            <a href="{{ route('admin.feed-types.create') }}" class="btn-primary text-sm">Add Feed Type</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="page-container">
            <x-flash />
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($feedTypes as $feedType)
                            <tr>
                                <td class="px-6 py-4">{{ $feedType->name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $feedType->slug }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :color="$feedType->is_active ? 'green' : 'gray'">{{ $feedType->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.feed-types.edit', $feedType) }}" class="link-primary">Edit</a>
                                    <form action="{{ route('admin.feed-types.destroy', $feedType) }}" method="POST" class="inline" onsubmit="return confirm('Delete this feed type?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $feedTypes->links() }}</div>
        </div>
    </div>
</x-admin-layout>
