<nav x-data="{ open: false }" class="bg-inverse-surface border-b border-on-surface/20">
    <div class="page-container">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-inverse-on-surface font-heading font-bold text-lg">
                        5ivers Feed
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex items-center">
                    @php
                        $catalogActive = request()->routeIs('admin.feed-types.*', 'admin.brands.*');
                    @endphp

                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="text-inverse-on-surface/80 hover:text-inverse-on-surface">
                        Dashboard
                    </x-nav-link>
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center gap-1 px-1 pt-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out {{ $catalogActive ? 'border-indigo-400 text-inverse-on-surface' : 'border-transparent text-inverse-on-surface/80 hover:text-inverse-on-surface' }}">
                                Catalog
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link
                                :href="route('admin.feed-types.index')"
                                class="{{ request()->routeIs('admin.feed-types.*') ? 'bg-gray-100 font-semibold text-primary' : '' }}"
                            >Feed Types</x-dropdown-link>
                            <x-dropdown-link
                                :href="route('admin.brands.index')"
                                class="{{ request()->routeIs('admin.brands.*') ? 'bg-gray-100 font-semibold text-primary' : '' }}"
                            >Brands</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                    <x-nav-link :href="route('admin.suppliers.index')" :active="request()->routeIs('admin.suppliers.*')" class="text-inverse-on-surface/80 hover:text-inverse-on-surface">
                        Suppliers
                    </x-nav-link>
                    <x-nav-link :href="route('admin.purchase-orders.index')" :active="request()->routeIs('admin.purchase-orders.*')" class="text-inverse-on-surface/80 hover:text-inverse-on-surface">
                        Purchase Orders
                    </x-nav-link>
                    <x-nav-link :href="route('admin.settings.credit-timelines.index')" :active="request()->routeIs('admin.settings.*')" class="text-inverse-on-surface/80 hover:text-inverse-on-surface">
                        Settings
                    </x-nav-link>
                    <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')" class="text-inverse-on-surface/80 hover:text-inverse-on-surface">
                        Reports
                    </x-nav-link>
                    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="text-inverse-on-surface/80 hover:text-inverse-on-surface">
                        Users
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md text-inverse-on-surface/80 hover:text-inverse-on-surface">
                            <x-user-avatar :user="Auth::user()" size="sm" />
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
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
