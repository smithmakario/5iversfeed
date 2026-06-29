<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Awaiting Approval — {{ config('app.name') }}</title>
    @include('layouts.partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface">
    <div class="min-h-screen flex flex-col">
        <header class="page-container py-6 flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('assets/logo/5ivers-food-logo.jpg') }}" alt="{{ config('app.name') }}" class="h-12 w-auto rounded-lg">
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-body-sm text-on-surface-variant hover:text-primary transition-colors">
                    Sign out
                </button>
            </form>
        </header>

        <main class="flex-1 flex items-center justify-center px-4 pb-16">
            <div class="w-full max-w-xl">
                @php
                    $status = $supplier?->status;
                    $isPending = $status?->value === 'pending';
                    $isRejected = $status?->value === 'rejected';
                @endphp

                <div class="card rounded-2xl p-8 sm:p-10 text-center shadow-lg border border-card-border/60">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full {{ $isPending ? 'bg-amber-50' : ($isRejected ? 'bg-red-50' : 'bg-surface-container') }}">
                        @if ($isPending)
                            <span class="relative flex h-12 w-12">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-30"></span>
                                <span class="relative inline-flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                            </span>
                        @elseif ($isRejected)
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>
                        @else
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-surface-container text-on-surface-variant">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            </span>
                        @endif
                    </div>

                    @if ($isPending)
                        <p class="text-label-caps text-amber-700 mb-2">Application under review</p>
                        <h1 class="font-heading text-headline-sm sm:text-headline-md text-on-surface">
                            Awaiting {{ config('app.name') }} approval
                        </h1>
                        <p class="mt-4 text-body-md text-on-surface-variant leading-relaxed">
                            Thanks for applying, <strong class="text-on-surface">{{ $supplier?->contact_name }}</strong>.
                            Your application for <strong class="text-on-surface">{{ $supplier?->company_name }}</strong> is with our team.
                            We will email you once your supplier account is approved.
                        </p>
                    @elseif ($isRejected)
                        <p class="text-label-caps text-red-700 mb-2">Application not approved</p>
                        <h1 class="font-heading text-headline-sm sm:text-headline-md text-on-surface">
                            Application update from {{ config('app.name') }}
                        </h1>
                        <p class="mt-4 text-body-md text-on-surface-variant leading-relaxed">
                            Your application for <strong class="text-on-surface">{{ $supplier?->company_name }}</strong> was not approved at this time.
                        </p>
                        @if ($supplier?->admin_notes)
                            <div class="mt-6 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-left text-body-sm text-red-800">
                                <p class="font-medium mb-1">Note from our team</p>
                                <p>{{ $supplier->admin_notes }}</p>
                            </div>
                        @endif
                    @else
                        <p class="text-label-caps text-on-surface-variant mb-2">Account inactive</p>
                        <h1 class="font-heading text-headline-sm sm:text-headline-md text-on-surface">
                            Supplier access unavailable
                        </h1>
                        <p class="mt-4 text-body-md text-on-surface-variant">
                            Your supplier account is not active. Please contact {{ config('app.name') }} support for assistance.
                        </p>
                    @endif

                    @if ($isPending)
                        <div class="mt-8 rounded-xl bg-surface-container/80 p-5 text-left">
                            <p class="text-label-caps text-on-surface-variant mb-4">What happens next</p>
                            <ol class="space-y-4">
                                <li class="flex gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-inverse-on-surface text-xs font-semibold">1</span>
                                    <div>
                                        <p class="text-body-sm font-medium text-on-surface">Application submitted</p>
                                        <p class="text-body-sm text-on-surface-variant">We received your company details and profile.</p>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 text-xs font-semibold ring-2 ring-amber-300">2</span>
                                    <div>
                                        <p class="text-body-sm font-medium text-on-surface">Team review in progress</p>
                                        <p class="text-body-sm text-on-surface-variant">Our admin team is verifying your supplier application.</p>
                                    </div>
                                </li>
                                <li class="flex gap-3 opacity-60">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-surface-container-high text-on-surface-variant text-xs font-semibold">3</span>
                                    <div>
                                        <p class="text-body-sm font-medium text-on-surface">Portal access unlocked</p>
                                        <p class="text-body-sm text-on-surface-variant">Manage products, purchase orders, and payments.</p>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        <p class="mt-6 text-body-sm text-on-surface-variant">
                            Submitted {{ $supplier?->created_at?->format('M d, Y') ?? 'recently' }}
                            · Status: <span class="font-medium text-amber-700">{{ $supplier?->status?->label() }}</span>
                        </p>
                    @endif
                </div>

                <p class="mt-6 text-center text-body-sm text-on-surface-variant">
                    Need help? Contact us at <span class="text-on-surface">{{ $supplier?->email }}</span>
                </p>
            </div>
        </main>
    </div>
</body>
</html>
