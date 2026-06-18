<x-admin-layout>
    @php
        $currentRole = request('role');
        $currentStatus = request('status');
    @endphp

    <div class="py-8">
        <div class="page-container">
            <x-flash />

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
                <div>
                    <nav class="flex items-center gap-2 text-outline text-label-caps text-[10px] uppercase mb-2">
                        <a class="hover:text-primary" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                        <span class="text-primary">Users</span>
                    </nav>
                    <h1 class="font-heading text-headline-sm text-primary">Users</h1>
                    <p class="text-secondary text-body-md mt-1">Manage admin and supplier accounts.</p>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-gutter mb-8">
                <div class="col-span-12 lg:col-span-8 glass-card border border-outline-variant p-4 mt-2 rounded-xl space-y-4">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
                        <input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search name or email"
                            class="flex-1 border-outline-variant rounded-lg text-body-sm"
                        >
                        @if ($currentRole)
                            <input type="hidden" name="role" value="{{ $currentRole }}">
                        @endif
                        @if ($currentStatus)
                            <input type="hidden" name="status" value="{{ $currentStatus }}">
                        @endif
                        <button type="submit" class="btn-secondary px-4 py-2 text-body-sm">Search</button>
                    </form>

                    <div class="flex flex-wrap items-center gap-2 bg-surface p-1 rounded-lg border border-outline-variant/30">
                        <a
                            href="{{ route('admin.users.index', array_filter(['search' => request('search')])) }}"
                            class="px-4 py-1.5 rounded-md font-semibold text-label-caps text-[11px] uppercase {{ ! $currentRole ? 'bg-primary-fixed text-on-primary-fixed shadow-sm' : 'text-secondary hover:text-primary transition-colors' }}"
                        >All Roles</a>
                        @foreach ($roles as $role)
                            <a
                                href="{{ route('admin.users.index', array_filter(['role' => $role->value, 'status' => $currentStatus, 'search' => request('search')])) }}"
                                class="px-4 py-1.5 rounded-md font-semibold text-label-caps text-[11px] uppercase {{ $currentRole === $role->value ? 'bg-primary-fixed text-on-primary-fixed shadow-sm' : 'text-secondary hover:text-primary transition-colors' }}"
                            >{{ $role->label() }}</a>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-2 bg-surface p-1 rounded-lg border border-outline-variant/30">
                        <a
                            href="{{ route('admin.users.index', array_filter(['role' => $currentRole, 'search' => request('search')])) }}"
                            class="px-4 py-1.5 rounded-md font-semibold text-label-caps text-[11px] uppercase {{ ! $currentStatus ? 'bg-primary-fixed text-on-primary-fixed shadow-sm' : 'text-secondary hover:text-primary transition-colors' }}"
                        >All Statuses</a>
                        @foreach ($statuses as $status)
                            <a
                                href="{{ route('admin.users.index', array_filter(['status' => $status->value, 'role' => $currentRole, 'search' => request('search')])) }}"
                                class="px-4 py-1.5 rounded-md font-semibold text-label-caps text-[11px] uppercase {{ $currentStatus === $status->value ? 'bg-primary-fixed text-on-primary-fixed shadow-sm' : 'text-secondary hover:text-primary transition-colors' }}"
                            >{{ $status->label() }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="col-span-12 mt-2 lg:col-span-4 glass-card border border-outline-variant p-4 rounded-xl flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-label-caps uppercase font-semibold text-outline">Total Users</span>
                        <span class="font-heading text-headline-sm text-primary">{{ number_format($users->total()) }}</span>
                    </div>
                    <div class="w-12 h-12 bg-primary-fixed/30 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">manage_accounts</span>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden mt-4 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="px-6 py-4 text-label-caps uppercase font-semibold text-secondary">User</th>
                                <th class="px-6 py-4 text-label-caps uppercase font-semibold text-secondary">Role</th>
                                <th class="px-6 py-4 text-label-caps uppercase font-semibold text-secondary">Status</th>
                                <th class="px-6 py-4 text-label-caps uppercase font-semibold text-secondary">Joined</th>
                                <th class="px-6 py-4 text-label-caps uppercase font-semibold text-secondary"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse ($users as $user)
                                <tr class="{{ $loop->even ? 'bg-surface-container-lowest/50' : '' }} hover:bg-surface-container-low transition-colors group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <x-user-avatar :user="$user" size="sm" />
                                            <div>
                                                <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-on-surface link-primary">{{ $user->name }}</a>
                                                <p class="text-secondary text-body-sm">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <x-status-badge color="{{ $user->role === \App\Enums\UserRole::Admin ? 'indigo' : 'blue' }}">
                                            {{ $user->role->label() }}
                                        </x-status-badge>
                                        @if ($user->supplier)
                                            <p class="text-body-sm text-secondary mt-1">{{ $user->supplier->company_name }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5">
                                        <x-status-badge :color="$user->status->color()">{{ $user->status->label() }}</x-status-badge>
                                    </td>
                                    <td class="px-6 py-5 text-secondary text-body-sm">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <a
                                            href="{{ route('admin.users.show', $user) }}"
                                            class="p-2 hover:bg-surface-container-high rounded-full opacity-0 group-hover:opacity-100 transition-opacity inline-flex"
                                            title="Manage"
                                        >
                                            <span class="material-symbols-outlined text-outline">more_vert</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-secondary text-body-sm">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages() || $users->total() > 0)
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-outline-variant">
                        <span class="text-body-sm text-secondary">
                            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
                        </span>
                        <div>{{ $users->links() }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
