<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500">
                    <a href="{{ route('admin.users.index') }}" class="link-primary">Users</a>
                </p>
                <h2 class="font-semibold text-xl text-gray-800">{{ $user->name }}</h2>
            </div>
            <x-status-badge :color="$user->status->color()">{{ $user->status->label() }}</x-status-badge>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-start gap-4">
                    <x-user-avatar :user="$user" size="lg" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm flex-1">
                        <div><span class="text-gray-500">Email</span><p>{{ $user->email }}</p></div>
                        <div><span class="text-gray-500">Role</span><p>{{ $user->role->label() }}</p></div>
                        <div><span class="text-gray-500">Account Status</span><p>{{ $user->status->label() }}</p></div>
                        <div><span class="text-gray-500">Joined</span><p>{{ $user->created_at->format('M d, Y g:i A') }}</p></div>
                        @if ($user->supplier)
                            <div class="sm:col-span-2">
                                <span class="text-gray-500">Supplier Profile</span>
                                <p>
                                    <a href="{{ route('admin.suppliers.show', $user->supplier) }}" class="link-primary">
                                        {{ $user->supplier->company_name }}
                                    </a>
                                    · {{ $user->supplier->status->label() }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($user->isSuspended())
                        <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                            @csrf
                            <button type="submit" class="btn-primary text-sm">Activate User</button>
                        </form>
                    @elseif (! auth()->user()->is($user))
                        <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm">Suspend User</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="font-semibold mb-1">Reset Password</h3>
                <p class="text-sm text-gray-500 mb-4">Set a new password for this user. They will use it on their next login.</p>

                <form method="POST" action="{{ route('admin.users.password.update', $user) }}" class="space-y-4 max-w-md">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="password" value="New Password" />
                        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" value="Confirm Password" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                    </div>
                    <x-primary-button>Reset Password</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
