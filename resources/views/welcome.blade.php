<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $appName ?? config('app.name') }}</title>
        @if (!empty($appFaviconUrl))
            <link rel="icon" href="{{ $appFaviconUrl }}" sizes="any">
            <link rel="icon" href="{{ $appFaviconUrl }}" type="image/png">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        @endif
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .welcome-page { font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif; }
            .pharma-accent { color: #0d9488; }
            .pharma-bg { background-color: #0d9488; }
            .pharma-bg:hover { background-color: #0f766e; }
        </style>
    </head>
    <body class="welcome-page min-h-screen bg-slate-50 text-slate-800 antialiased dark:bg-slate-900 dark:text-slate-100 flex flex-col">
        <header class="w-full py-4 px-6 flex justify-end border-b border-slate-200/80 dark:border-slate-700/80">
            @if (Route::has('login'))
                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition">
                            {{ __('Masuk') }}
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-lg pharma-bg px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:shadow">
                                {{ __('Daftar') }}
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="flex-1">
            {{-- Hero --}}
            <section class="py-12 sm:py-16 px-6">
                <div class="max-w-4xl mx-auto text-center">
                    @if (!empty($appLogoUrl))
                        <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="mx-auto h-16 sm:h-20 w-auto object-contain mb-6" />
                    @else
                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl pharma-bg text-white">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                    @endif
                    <h1 class="text-3xl sm:text-4xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                        {{ $appName ?? config('app.name') }}
                    </h1>
                    @if (!empty($appTagline))
                        <p class="mt-2 text-lg text-slate-600 dark:text-slate-300">
                            {{ $appTagline }}
                        </p>
                    @else
                        <p class="mt-2 text-lg text-slate-600 dark:text-slate-300">
                            {{ __('Sistem manajemen apotek') }}
                        </p>
                    @endif
                    <p class="mt-4 text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">
                        {{ __('Solusi lengkap untuk operasional apotek Anda: penjualan, inventaris, stok, laporan, dan multi-outlet dalam satu platform.') }}
                    </p>
                    @if (Route::has('login') && !auth()->check())
                        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg pharma-bg px-6 py-3 text-sm font-medium text-white shadow-sm transition hover:shadow">
                                {{ __('Masuk ke aplikasi') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 dark:border-slate-600 px-6 py-3 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                    {{ __('Daftar') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

            {{-- Fitur --}}
            <section class="py-12 px-6 bg-slate-100/80 dark:bg-slate-800/40 border-y border-slate-200/80 dark:border-slate-700/80">
                <div class="max-w-5xl mx-auto">
                    <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-100 text-center mb-8">
                        {{ __('Apa yang bisa Anda lakukan') }}
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 p-5 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg pharma-bg text-white mb-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <h3 class="font-medium text-slate-800 dark:text-slate-100">{{ __('POS & Kasir') }}</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Transaksi penjualan cepat, struk, dan riwayat penjualan.') }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 p-5 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg pharma-bg text-white mb-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="font-medium text-slate-800 dark:text-slate-100">{{ __('Inventaris & Stok') }}</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Produk, batch, unit, pergerakan stok, dan pengadaan.') }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 p-5 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg pharma-bg text-white mb-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="font-medium text-slate-800 dark:text-slate-100">{{ __('Laporan') }}</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Penjualan, stok, dan obat mendekati kadaluarsa.') }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 p-5 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg pharma-bg text-white mb-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4H9m-1 4h1" />
                                </svg>
                            </div>
                            <h3 class="font-medium text-slate-800 dark:text-slate-100">{{ __('Multi-tenant') }}</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Satu platform untuk banyak apotek dengan data terpisah.') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- CTA akhir --}}
            @if (Route::has('login') && !auth()->check())
                <section class="py-12 px-6">
                    <div class="max-w-2xl mx-auto text-center">
                        <p class="text-slate-600 dark:text-slate-300">
                            {{ __('Sudah punya akun?') }}
                            <a href="{{ route('login') }}" class="font-medium pharma-accent hover:underline">{{ __('Masuk di sini') }}</a>
                        </p>
                    </div>
                </section>
            @endif
        </main>

        <footer class="py-6 px-6 border-t border-slate-200/80 dark:border-slate-700/80 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __('Solusi lengkap untuk operasional apotek Anda.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                {{ __('Powered by CV. Terubuk Swakarsa Teruna (TRUST)') }}
            </p>
        </footer>
    </body>
</html>
