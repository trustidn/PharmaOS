<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Menu Utama')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @if (auth()->user()->isTenantUser())
                        <flux:sidebar.item icon="shopping-cart" :href="route('pos.cashier')" :current="request()->routeIs('pos.*')" wire:navigate>
                            {{ __('Kasir / POS') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @if (auth()->user()->isTenantUser())
                    <flux:sidebar.group :heading="__('Inventaris')" class="grid">
                        <flux:sidebar.item icon="cube" :href="route('inventory.products')" :current="request()->routeIs('inventory.*')" wire:navigate>
                            {{ __('Produk / Obat') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="truck" :href="route('suppliers.index')" :current="request()->routeIs('suppliers.*')" wire:navigate>
                            {{ __('Supplier') }}
                        </flux:sidebar.item>
                        @if (app(\App\Services\PlanLimitService::class)->hasFeature('supplier_management'))
                            <flux:sidebar.item icon="document-text" :href="route('purchase-orders.index')" :current="request()->routeIs('purchase-orders.*')" wire:navigate>
                                {{ __('Purchase Order') }}
                            </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>

                    @if (auth()->user()->isOwner())
                        <flux:sidebar.group :heading="__('Laporan')" class="grid">
                            <flux:sidebar.item icon="chart-bar" :href="route('reports.sales')" :current="request()->routeIs('reports.sales')" wire:navigate>
                                {{ __('Penjualan') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="archive-box" :href="route('reports.stock')" :current="request()->routeIs('reports.stock')" wire:navigate>
                                {{ __('Stok') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="clock" :href="route('reports.expiry')" :current="request()->routeIs('reports.expiry')" wire:navigate>
                                {{ __('Kadaluarsa') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                        <flux:sidebar.group :heading="__('Pengaturan Apotek')" class="grid">
                            <flux:sidebar.item icon="paint-brush" :href="route('settings.white-label')" :current="request()->routeIs('settings.white-label')" wire:navigate>
                                {{ __('White Label') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="users" :href="route('settings.users')" :current="request()->routeIs('settings.users')" wire:navigate>
                                {{ __('Manajemen User') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif
                @endif

                @if (auth()->user()->isSuperAdmin())
                    <flux:sidebar.group :heading="__('Admin')" class="grid">
                        <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.settings')" :current="request()->routeIs('admin.settings')" wire:navigate>
                            {{ __('Pengaturan Aplikasi') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-office" :href="route('admin.tenants')" :current="request()->routeIs('admin.tenants*')" wire:navigate>
                            {{ __('Kelola Tenant') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chart-pie" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            {{ __('System Dashboard') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate text-zinc-500 dark:text-zinc-400">{{ auth()->user()->role->label() }}</flux:text>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
