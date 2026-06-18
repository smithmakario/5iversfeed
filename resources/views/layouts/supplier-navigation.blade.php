<nav x-data="{ open: false }" class="bg-inverse-surface border-b border-on-surface/20">
    <div class="page-container">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('supplier.dashboard') }}" class="text-inverse-on-surface font-heading font-bold text-lg">
                        5ivers Feed — Supplier
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('supplier.dashboard')" :active="request()->routeIs('supplier.dashboard')" class="text-inverse-on-surface/80 hover:text-inverse-on-surface">
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('supplier.purchase-orders.index')" :active="request()->routeIs('supplier.purchase-orders.*')" class="text-inverse-on-surface/80 hover:text-inverse-on-surface">
                        Purchase Orders
                    </x-nav-link>
                    <x-nav-link :href="route('supplier.finance.index')" :active="request()->routeIs('supplier.finance.*')" class="text-inverse-on-surface/80 hover:text-inverse-on-surface">
                        Finance
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md text-inverse-on-surface/80 hover:text-inverse-on-surface">
                            <x-user-avatar :user="Auth::user()" size="sm" />
                            <div>{{ Auth::user()->supplier?->company_name ?? Auth::user()->name }}</div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
