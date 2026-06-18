<x-admin-layout>
    @php
        $currentStatus = request('status');
    @endphp

    <div class="py-8">
        <div class="page-container">
            <x-flash />

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
                <div>
                    <nav class="flex items-center gap-2 text-outline text-label-caps text-[10px] uppercase mb-2">
                        <a class="hover:text-primary" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                        <span class="text-primary">Suppliers</span>
                    </nav>
                    <h1 class="font-heading text-headline-sm text-primary">Suppliers</h1>
                    <p class="text-secondary text-body-md mt-1">Manage and track your global feed supplier network.</p>
                </div>
                <a
                    href="{{ route('admin.suppliers.create') }}"
                    class="btn-primary inline-flex items-center justify-center gap-2 shrink-0 w-full sm:w-auto px-6 py-3 text-body-md"
                >
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Add Supplier
                </a>
            </div>

            {{-- Filter bar --}}
            <div class="grid grid-cols-12 gap-gutter mb-8">
                <div class="col-span-12 lg:col-span-8 glass-card border border-outline-variant p-4 mt-2 rounded-xl flex flex-wrap items-center gap-4">
                    <div class="flex flex-wrap items-center gap-2 bg-surface p-1 rounded-lg mt-2 border border-outline-variant/30">
                        <a
                            href="{{ route('admin.suppliers.index') }}"
                            class="px-4 py-1.5 rounded-md font-semibold text-label-caps text-[11px] uppercase {{ ! $currentStatus ? 'bg-primary-fixed text-on-primary-fixed shadow-sm' : 'text-secondary hover:text-primary transition-colors' }}"
                        >All Suppliers</a>
                        @foreach ($statuses as $status)
                            <a
                                href="{{ route('admin.suppliers.index', ['status' => $status->value]) }}"
                                class="px-4 py-1.5 rounded-md font-semibold text-label-caps text-[11px] uppercase {{ $currentStatus === $status->value ? 'bg-primary-fixed text-on-primary-fixed shadow-sm' : 'text-secondary hover:text-primary transition-colors' }}"
                            >{{ $status->label() }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="col-span-12 mt-2 lg:col-span-4 glass-card border border-outline-variant p-4 rounded-xl flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-label-caps uppercase font-semibold text-outline">Total Suppliers</span>
                        <span class="font-heading text-headline-sm text-primary">{{ number_format($suppliers->total()) }}</span>
                    </div>
                    <div class="w-12 h-12 bg-primary-fixed/30 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">groups</span>
                    </div>
                </div>
            </div>

            {{-- Suppliers table --}}
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden mt-4
            shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="px-6 py-4 text-label-caps uppercase font-semibold text-secondary">Company</th>
                                <th class="px-6 py-4 text-label-caps uppercase font-semibold text-secondary">Contact</th>
                                <th class="px-6 py-4 text-label-caps uppercase font-semibold text-secondary">Status</th>
                                <th class="px-6 py-4 text-label-caps uppercase font-semibold text-secondary"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse ($suppliers as $supplier)
                                <tr class="{{ $loop->even ? 'bg-surface-container-lowest/50' : '' }} hover:bg-surface-container-low transition-colors group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded bg-surface-container-high flex items-center justify-center font-bold text-outline text-[10px]">
                                                {{ strtoupper(substr($supplier->company_name, 0, 2)) }}
                                            </div>
                                            <a href="{{ route('admin.suppliers.show', $supplier) }}" class="font-medium text-on-surface link-primary">{{ $supplier->company_name }}</a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-on-surface">{{ $supplier->contact_name }}</span>
                                        <br>
                                        <span class="text-secondary text-body-sm">{{ $supplier->email }}</span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <x-status-badge :color="$supplier->status->color()">{{ $supplier->status->label() }}</x-status-badge>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <a
                                            href="{{ route('admin.suppliers.show', $supplier) }}"
                                            class="p-2 hover:bg-surface-container-high rounded-full opacity-0 group-hover:opacity-100 transition-opacity inline-flex"
                                            title="View"
                                        >
                                            <span class="material-symbols-outlined text-outline">more_vert</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-secondary text-body-sm">No suppliers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($suppliers->hasPages() || $suppliers->total() > 0)
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-outline-variant">
                        <span class="text-body-sm text-secondary">
                            Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} results
                        </span>
                        <div>{{ $suppliers->links() }}</div>
                    </div>
                @endif
            </div>

            {{-- Featured supplier showcase --}}
            @if ($suppliers->count() > 0)
                <div class="mt-12 grid grid-cols-12 gap-gutter">
                    <div class="col-span-12 lg:col-span-8 bg-surface border border-outline-variant rounded-2xl p-6 relative overflow-hidden group">
                        <div class="relative z-10">
                            <span class="text-label-caps uppercase font-semibold text-zesty-orange mb-2 block">Spotlight</span>
                            <h3 class="font-heading text-headline-sm text-primary mb-4">Active Supply Partners</h3>
                            <div class="flex flex-wrap gap-4 mt-6">
                                @foreach ($suppliers->take(2) as $partner)
                                    <a
                                        href="{{ route('admin.suppliers.show', $partner) }}"
                                        class="bg-white p-4 rounded-xl border border-outline-variant shadow-sm w-full md:w-56 hover:-translate-y-1 transition-transform"
                                    >
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-outline text-[10px]">
                                                {{ strtoupper(substr($partner->company_name, 0, 2)) }}
                                            </div>
                                            <span class="font-bold text-body-md text-on-surface">{{ $partner->company_name }}</span>
                                        </div>
                                        <p class="text-[11px] text-secondary leading-tight">{{ $partner->contact_name }} — {{ $partner->email }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 lg:col-span-4 bg-primary text-white rounded-2xl p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="font-heading text-headline-sm mb-2">Pending Applications</h3>
                            <p class="text-primary-fixed-dim text-body-sm">Review supplier onboarding requests awaiting approval.</p>
                        </div>
                        <div class="mt-8">
                            <a
                                href="{{ route('admin.suppliers.index', ['status' => 'pending']) }}"
                                class="w-full py-3 bg-white text-primary rounded-lg font-bold text-body-md hover:bg-primary-fixed transition-colors flex items-center justify-center gap-2"
                            >
                                <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                                Review Pending
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
