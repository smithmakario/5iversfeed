<x-admin-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ isset($timeline) ? 'Edit' : 'Create' }} Credit Repayment Timeline</h2></x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ isset($timeline) ? route('admin.settings.credit-timelines.update', $timeline) : route('admin.settings.credit-timelines.store') }}" class="space-y-4">
                    @csrf
                    @isset($timeline) @method('PUT') @endisset
                    <div>
                        <x-input-label for="label" value="Label" />
                        <x-text-input id="label" name="label" class="block mt-1 w-full" :value="old('label', $timeline->label ?? '')" placeholder="e.g. 30 Days" required />
                        <x-input-error :messages="$errors->get('label')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="days" value="Repayment Period (days)" />
                        <x-text-input id="days" name="days" type="number" min="1" max="365" class="block mt-1 w-full" :value="old('days', $timeline->days ?? '')" required />
                        <x-input-error :messages="$errors->get('days')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="sort_order" value="Sort Order" />
                        <x-text-input id="sort_order" name="sort_order" type="number" class="block mt-1 w-full" :value="old('sort_order', $timeline->sort_order ?? 0)" />
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $timeline->is_active ?? true)) class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                    <div class="flex gap-3">
                        <x-primary-button>Save</x-primary-button>
                        <a href="{{ route('admin.settings.credit-timelines.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-sm">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
