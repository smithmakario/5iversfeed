<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Brands</h2>
            <a href="{{ route('admin.brands.create') }}" class="btn-primary text-sm">Add Brand</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="page-container">
            <x-flash />
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($brands as $brand)
                            <tr>
                                <td class="px-6 py-4">{{ $brand->name }}</td>
                                <td class="px-6 py-4"><x-status-badge :color="$brand->is_active ? 'green' : 'gray'">{{ $brand->is_active ? 'Active' : 'Inactive' }}</x-status-badge></td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="link-primary">Edit</a>
                                    <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="inline" onsubmit="return confirm('Delete this brand?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $brands->links() }}</div>
        </div>
    </div>
</x-admin-layout>
