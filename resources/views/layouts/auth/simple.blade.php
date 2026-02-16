@php
    $appSettings = app(\App\Services\AppSettingsService::class);
    $appName = $appSettings->getAppName();
    $appLogoUrl = $appSettings->getLogoUrl();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet" />
        <style>
            .auth-page { font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif; }
            .auth-card { background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); }
            .auth-page.dark .auth-card { background: rgba(30, 41, 59, 0.5); }
            .pharma-bg { background-color: #0d9488; }
            .pharma-bg:hover { background-color: #0f766e; }
            .auth-card button[type="submit"] { background-color: #0d9488 !important; }
            .auth-card button[type="submit"]:hover { background-color: #0f766e !important; }
        </style>
    </head>
    <body class="auth-page min-h-screen bg-slate-50 text-slate-800 antialiased dark:bg-slate-900 dark:text-slate-100">
        <header class="absolute top-0 right-0 w-full py-4 px-6 flex justify-end">
            <a href="{{ route('home') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition" wire:navigate>
                ← {{ __('Beranda') }}
            </a>
        </header>
        <div class="flex min-h-svh flex-col items-center justify-center p-6 md:p-10">
            <div class="w-full max-w-md">
                <div class="auth-card rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xl p-8 md:p-10 dark:bg-slate-800/50">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 mb-6" wire:navigate>
                        @if (!empty($appLogoUrl))
                            <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-14 w-auto object-contain" />
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl pharma-bg text-white">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </div>
                        @endif
                        <span class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ $appName }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
