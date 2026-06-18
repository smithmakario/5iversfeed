<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @include('layouts.partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface">
    <div class="page-container py-16 lg:py-24">
        <header class="flex justify-between items-center mb-16 lg:mb-24">
            {{-- <h1 class="font-heading text-headline-sm text-primary">5ivers Feed</h1> --}}
            <img src="{{ asset('assets/logo/5ivers-food-logo.jpg') }}" alt="5ivers Feed Logo" class="h-16 w-auto">
            <div class="flex gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary text-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-primary text-body-sm hover:underline">Log in</a>
                    <a href="{{ route('supplier.apply') }}" class="btn-primary text-sm">Become a Supplier</a>
                @endauth
            </div>
        </header>

        <main class="grid lg:grid-cols-2 gap-12 lg:gap-24 items-center">
            <div>
                <h2 class="font-heading text-headline-lg-mobile lg:text-headline-lg text-on-surface">Feed management for modern agribusiness</h2>
                <p class="mt-4 text-body-lg text-on-surface-variant">Manage feed types, brands, supplier products, and purchase orders — all in one place.</p>
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="card flex items-center gap-3 p-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-container text-primary">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                        </div>
                        <p class="text-body-sm text-on-surface-variant">Supplier catalogs with nutritional product specs</p>
                    </div>
                    <div class="card flex items-center gap-3 p-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-container text-primary">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <p class="text-body-sm text-on-surface-variant">Supplier onboarding and approval workflow</p>
                    </div>
                    <div class="card flex items-center gap-3 p-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-container text-primary">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                            </svg>
                        </div>
                        <p class="text-body-sm text-on-surface-variant">Purchase order lifecycle management</p>
                    </div>
                    <div class="card flex items-center gap-3 p-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-container text-primary">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                        <p class="text-body-sm text-on-surface-variant">Reports and spend analytics</p>
                    </div>
                </div>
            </div>
            <div class="card rounded-xl p-8 lg:p-10">
                <h3 class="font-heading text-headline-sm mb-4">Get started</h3>
                <p class="text-body-md text-on-surface-variant mb-6">Admins manage the catalog and orders. Suppliers apply online and receive purchase orders once approved.</p>
                <div class="flex flex-col gap-3">
                    <a href="{{ route('login') }}" class="btn-primary text-center">Admin / Supplier Login</a>
                    <a href="{{ route('supplier.apply') }}" class="btn-secondary text-center">Apply as Supplier</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
