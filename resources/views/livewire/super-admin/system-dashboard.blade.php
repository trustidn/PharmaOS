<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('System Dashboard') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Ringkasan sistem PharmaOS.') }}</flux:text>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                    <flux:icon name="building-office" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:text size="sm">{{ __('Total Tenant') }}</flux:text>
                    <div class="text-2xl font-bold">{{ number_format($totalTenants) }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="h-6 w-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:text size="sm">{{ __('Tenant Aktif') }}</flux:text>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($activeTenants) }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                    <flux:icon name="users" class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <flux:text size="sm">{{ __('Total Users') }}</flux:text>
                    <div class="text-2xl font-bold">{{ number_format($totalUsers) }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30">
                    <flux:icon name="shopping-cart" class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:text size="sm">{{ __('Transaksi Hari Ini') }}</flux:text>
                    <div class="text-2xl font-bold">{{ number_format($totalTransactionsToday) }}</div>
                </div>
            </div>
        </flux:card>
    </div>
</div>
