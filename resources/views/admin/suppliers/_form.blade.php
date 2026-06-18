@php
    $isEdit = isset($supplier);
    $formId = $isEdit ? 'supplier-edit-form' : 'supplier-create-form';
@endphp

<x-admin-layout>
    <div class="py-8">
        <div class="page-container pb-24 lg:pb-0">
            <x-flash />

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-8">
                <div>
                    <nav class="flex items-center gap-2 text-outline text-label-caps text-[10px] uppercase mb-2 flex-wrap">
                        <a class="hover:text-primary" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                        <a class="hover:text-primary" href="{{ route('admin.suppliers.index') }}">Suppliers</a>
                        @if ($isEdit)
                            <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                            <a class="hover:text-primary" href="{{ route('admin.suppliers.show', $supplier) }}">{{ $supplier->company_name }}</a>
                            <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                            <span class="text-zesty-orange">Edit</span>
                        @else
                            <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                            <span class="text-zesty-orange">Add Supplier</span>
                        @endif
                    </nav>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="font-heading text-headline-sm text-primary">{{ $isEdit ? 'Edit Supplier' : 'Add Supplier' }}</h1>
                        @if ($isEdit)
                            <x-status-badge :color="$supplier->status->color()">{{ $supplier->status->label() }}</x-status-badge>
                        @endif
                    </div>
                    <p class="text-secondary text-body-md mt-1">
                        {{ $isEdit ? 'Update company profile, contact details, and internal notes.' : 'Register a new supplier and portal login credentials.' }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <a
                        href="{{ $isEdit ? route('admin.suppliers.show', $supplier) : route('admin.suppliers.index') }}"
                        class="px-6 py-3 border-2 border-deep-charcoal text-deep-charcoal rounded-lg font-semibold text-body-md flex items-center gap-2 hover:bg-deep-charcoal hover:text-white transition-all"
                    >
                        <span class="material-symbols-outlined text-[18px]">close</span>
                        Cancel
                    </a>
                    <button
                        type="submit"
                        form="{{ $formId }}"
                        class="px-6 py-3 bg-zesty-orange text-white rounded-lg font-semibold text-body-md flex items-center gap-2 hover:shadow-lg transition-all active:scale-95"
                    >
                        <span class="material-symbols-outlined text-[18px]">{{ $isEdit ? 'save' : 'add' }}</span>
                        {{ $isEdit ? 'Save Changes' : 'Create Supplier' }}
                    </button>
                </div>
            </div>

            <form
                id="{{ $formId }}"
                method="POST"
                action="{{ $isEdit ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}"
            >
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-12 gap-gutter">
                    <div class="col-span-12 lg:col-span-8 space-y-6">
                        <section class="glass-card border border-outline-variant rounded-xl p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-lg bg-primary-fixed/40 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary">business</span>
                                </div>
                                <div>
                                    <h2 class="font-heading text-headline-sm text-on-surface">Company Information</h2>
                                    <p class="text-body-sm text-secondary">Legal and registered business details.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2">
                                    <x-input-label for="company_name" value="Company Name" />
                                    <x-text-input id="company_name" name="company_name" class="block mt-1 w-full" :value="old('company_name', $supplier->company_name ?? '')" required />
                                    <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="registration_number" value="Registration Number" />
                                    <x-text-input id="registration_number" name="registration_number" class="block mt-1 w-full" :value="old('registration_number', $supplier->registration_number ?? '')" />
                                    <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tax_id" value="Tax ID" />
                                    <x-text-input id="tax_id" name="tax_id" class="block mt-1 w-full" :value="old('tax_id', $supplier->tax_id ?? '')" />
                                    <x-input-error :messages="$errors->get('tax_id')" class="mt-2" />
                                </div>
                            </div>
                        </section>

                        <section class="glass-card border border-outline-variant rounded-xl p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-lg bg-primary-fixed/40 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary">contact_mail</span>
                                </div>
                                <div>
                                    <h2 class="font-heading text-headline-sm text-on-surface">Contact Details</h2>
                                    <p class="text-body-sm text-secondary">Primary point of contact for procurement.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <x-input-label for="contact_name" value="Contact Name" />
                                    <x-text-input id="contact_name" name="contact_name" class="block mt-1 w-full" :value="old('contact_name', $supplier->contact_name ?? '')" required />
                                    <x-input-error :messages="$errors->get('contact_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="phone" value="Phone" />
                                    <x-text-input id="phone" name="phone" type="tel" class="block mt-1 w-full" :value="old('phone', $supplier->phone ?? '')" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="email" value="Email" />
                                    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $supplier->email ?? '')" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                            </div>
                        </section>

                        @unless ($isEdit)
                            <section class="glass-card border border-outline-variant rounded-xl p-6">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-10 h-10 rounded-lg bg-primary-fixed/40 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-primary">lock</span>
                                    </div>
                                    <div>
                                        <h2 class="font-heading text-headline-sm text-on-surface">Portal Access</h2>
                                        <p class="text-body-sm text-secondary">Login credentials for the supplier portal.</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <x-input-label for="password" value="Password" />
                                        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="password_confirmation" value="Confirm Password" />
                                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                                    </div>
                                </div>
                            </section>
                        @endunless

                        <section class="glass-card border border-outline-variant rounded-xl p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-lg bg-primary-fixed/40 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary">location_on</span>
                                </div>
                                <div>
                                    <h2 class="font-heading text-headline-sm text-on-surface">Location</h2>
                                    <p class="text-body-sm text-secondary">Physical address and operating region.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2">
                                    <x-input-label for="address" value="Street Address" />
                                    <x-text-input id="address" name="address" class="block mt-1 w-full" :value="old('address', $supplier->address ?? '')" />
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="city" value="City" />
                                    <x-text-input id="city" name="city" class="block mt-1 w-full" :value="old('city', $supplier->city ?? '')" />
                                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="state" value="State / Region" />
                                    <x-text-input id="state" name="state" class="block mt-1 w-full" :value="old('state', $supplier->state ?? '')" />
                                    <x-input-error :messages="$errors->get('state')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="country" value="Country" />
                                    <x-text-input id="country" name="country" class="block mt-1 w-full" :value="old('country', $supplier->country ?? '')" />
                                    <x-input-error :messages="$errors->get('country')" class="mt-2" />
                                </div>
                            </div>
                        </section>

                        <section class="glass-card border border-outline-variant rounded-xl p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-lg bg-primary-fixed/40 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary">sticky_note_2</span>
                                </div>
                                <div>
                                    <h2 class="font-heading text-headline-sm text-on-surface">Internal Notes</h2>
                                    <p class="text-body-sm text-secondary">Visible to admins only — not shared with the supplier.</p>
                                </div>
                            </div>
                            <div>
                                <x-input-label for="admin_notes" value="Admin Notes" />
                                <textarea
                                    id="admin_notes"
                                    name="admin_notes"
                                    rows="4"
                                    class="input-field block mt-1 w-full resize-y"
                                    placeholder="Add notes about onboarding, compliance, or account history…"
                                >{{ old('admin_notes', $supplier->admin_notes ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('admin_notes')" class="mt-2" />
                            </div>
                        </section>
                    </div>

                    <aside class="col-span-12 lg:col-span-4 space-y-6">
                        @if ($isEdit)
                            <div class="glass-card border border-outline-variant rounded-xl p-6">
                                <div class="flex items-center gap-4 mb-5">
                                    @if ($supplier->user)
                                        <x-user-avatar :user="$supplier->user" size="lg" />
                                    @else
                                        <div class="h-16 w-16 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-outline text-lg shrink-0">
                                            {{ strtoupper(substr($supplier->company_name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-heading font-semibold text-on-surface truncate">{{ $supplier->company_name }}</p>
                                        <p class="text-body-sm text-secondary truncate">{{ $supplier->email }}</p>
                                    </div>
                                </div>
                                <dl class="space-y-3 text-body-sm">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-secondary">Status</dt>
                                        <dd><x-status-badge :color="$supplier->status->color()">{{ $supplier->status->label() }}</x-status-badge></dd>
                                    </div>
                                    @if ($supplier->approved_at)
                                        <div class="flex justify-between gap-4">
                                            <dt class="text-secondary">Approved</dt>
                                            <dd class="text-on-surface">{{ $supplier->approved_at->format('M d, Y') }}</dd>
                                        </div>
                                    @endif
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-secondary">Purchase Orders</dt>
                                        <dd class="text-on-surface font-medium">{{ $supplier->purchaseOrders()->count() }}</dd>
                                    </div>
                                </dl>
                                <a
                                    href="{{ route('admin.suppliers.show', $supplier) }}"
                                    class="mt-6 w-full py-3 border border-outline-variant rounded-lg font-semibold text-body-sm text-primary flex items-center justify-center gap-2 hover:bg-surface-container-low transition-colors"
                                >
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    View Profile
                                </a>
                            </div>
                        @else
                            <div class="glass-card border border-outline-variant rounded-xl p-6">
                                <h2 class="font-heading text-headline-sm text-on-surface mb-4">Account Status</h2>
                                <x-input-label for="status" value="Initial Status" />
                                <select id="status" name="status" class="input-field block mt-1 w-full" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected(old('status', 'approved') === $status->value)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                <p class="text-body-sm text-secondary mt-3">Approved suppliers can log in and receive purchase orders immediately.</p>
                            </div>
                        @endif

                        <div class="bg-surface border border-outline-variant rounded-xl p-5">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">info</span>
                                <div>
                                    <p class="font-semibold text-body-sm text-on-surface">Required fields</p>
                                    <p class="text-body-sm text-secondary mt-1">
                                        @if ($isEdit)
                                            Company name, contact name, and email are required.
                                        @else
                                            Company name, contact name, email, password, and status are required.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-surface-container-lowest border-t border-outline-variant p-4 flex gap-3">
                    <a href="{{ $isEdit ? route('admin.suppliers.show', $supplier) : route('admin.suppliers.index') }}" class="btn-secondary flex-1 text-center">Cancel</a>
                    <x-primary-button class="flex-1 justify-center">{{ $isEdit ? 'Save Changes' : 'Create Supplier' }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
