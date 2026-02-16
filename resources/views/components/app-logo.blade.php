@props([
    'sidebar' => false,
])

@php
    $branding = app(\App\Services\BrandingService::class)->getBranding();
    $appName = $branding['name'];
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$appName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            @if($branding['logo_path'])
                <img src="{{ Storage::url($branding['logo_path']) }}" alt="{{ $appName }}" class="size-5 object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$appName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            @if($branding['logo_path'])
                <img src="{{ Storage::url($branding['logo_path']) }}" alt="{{ $appName }}" class="size-5 object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:brand>
@endif
