<x-admin-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ isset($feedType) ? 'Edit' : 'Create' }} Feed Type</h2></x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ isset($feedType) ? route('admin.feed-types.update', $feedType) : route('admin.feed-types.store') }}" class="space-y-4">
                    @csrf
                    @isset($feedType) @method('PUT') @endisset
                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $feedType->name ?? '')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $feedType->description ?? '') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="sort_order" value="Sort Order" />
                        <x-text-input id="sort_order" name="sort_order" type="number" class="block mt-1 w-full" :value="old('sort_order', $feedType->sort_order ?? 0)" />
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $feedType->is_active ?? true)) class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                    <div class="flex gap-3">
                        <x-primary-button>Save</x-primary-button>
                        <a href="{{ route('admin.feed-types.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-sm">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
