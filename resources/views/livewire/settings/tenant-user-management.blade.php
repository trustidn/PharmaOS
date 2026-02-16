<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Manajemen User') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Kelola user apotek: tambah, ubah role, dan aktif/nonaktif.') }}</flux:text>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif
    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari nama atau email...') }}" icon="magnifying-glass" />
        </div>
        @if ($canAddUser)
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                {{ __('Tambah User') }}
            </flux:button>
        @else
            <flux:badge color="amber" size="sm">{{ __('Batas user paket tercapai') }}</flux:badge>
        @endif
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Nama') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($users as $user)
                <flux:table.row wire:key="tu-{{ $user->id }}">
                    <flux:table.cell class="font-medium">{{ $user->name }}</flux:table.cell>
                    <flux:table.cell>{{ $user->email }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc">{{ $user->role->label() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($user->is_active)
                            <flux:badge color="green" size="sm">{{ __('Aktif') }}</flux:badge>
                        @else
                            <flux:badge color="red" size="sm">{{ __('Nonaktif') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEditModal({{ $user->id }})">
                                {{ __('Ubah') }}
                            </flux:button>
                            @if ($user->id !== auth()->id())
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    :icon="$user->is_active ? 'eye-slash' : 'eye'"
                                    wire:click="toggleActive({{ $user->id }})"
                                    wire:confirm="{{ $user->is_active ? __('Nonaktifkan user ini?') : __('Aktifkan user ini?') }}"
                                >
                                    {{ $user->is_active ? __('Nonaktifkan') : __('Aktifkan') }}
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-8">
                        <flux:text>{{ __('Tidak ada user.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">{{ $users->links() }}</div>

    {{-- Modal Tambah User --}}
    <flux:modal wire:model="showFormModal" class="max-w-md">
        <flux:heading size="lg">{{ __('Tambah User') }}</flux:heading>
        <form wire:submit="saveNewUser" class="mt-4 space-y-4">
            <flux:field>
                <flux:label>{{ __('Nama') }}</flux:label>
                <flux:input wire:model="name" placeholder="{{ __('Nama lengkap') }}" />
                <flux:error name="name" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input type="email" wire:model="email" placeholder="email@apotek.com" />
                <flux:error name="email" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Password') }}</flux:label>
                <flux:input type="password" wire:model="password" placeholder="{{ __('Min. 8 karakter') }}" />
                <flux:error name="password" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Role') }}</flux:label>
                <flux:select wire:model="role" class="w-full">
                    <flux:select.option value="{{ \App\Enums\UserRole::Pharmacist->value }}">{{ \App\Enums\UserRole::Pharmacist->label() }}</flux:select.option>
                    <flux:select.option value="{{ \App\Enums\UserRole::Cashier->value }}">{{ \App\Enums\UserRole::Cashier->label() }}</flux:select.option>
                </flux:select>
                <flux:error name="role" />
            </flux:field>
            <div class="flex gap-2 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
                <flux:button type="button" variant="ghost" wire:click="closeModals">{{ __('Batal') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal Ubah User --}}
    <flux:modal wire:model="showEditModal" class="max-w-md">
        <flux:heading size="lg">{{ __('Ubah User') }}</flux:heading>
        <form wire:submit="updateUser" class="mt-4 space-y-4">
            <flux:field>
                <flux:label>{{ __('Nama') }}</flux:label>
                <flux:input wire:model="name" placeholder="{{ __('Nama lengkap') }}" />
                <flux:error name="name" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input type="email" wire:model="email" />
                <flux:error name="email" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Password baru (kosongkan jika tidak diubah)') }}</flux:label>
                <flux:input type="password" wire:model="password" placeholder="{{ __('Opsional') }}" />
                <flux:error name="password" />
            </flux:field>
            @if ($editingUserId !== auth()->id())
                <flux:field>
                    <flux:label>{{ __('Role') }}</flux:label>
                    <flux:select wire:model="role" class="w-full">
                        <flux:select.option value="{{ \App\Enums\UserRole::Owner->value }}">{{ \App\Enums\UserRole::Owner->label() }}</flux:select.option>
                        <flux:select.option value="{{ \App\Enums\UserRole::Pharmacist->value }}">{{ \App\Enums\UserRole::Pharmacist->label() }}</flux:select.option>
                        <flux:select.option value="{{ \App\Enums\UserRole::Cashier->value }}">{{ \App\Enums\UserRole::Cashier->label() }}</flux:select.option>
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>
                <flux:field>
                    <flux:checkbox wire:model.live="is_active" />
                    <flux:label>{{ __('Aktif') }}</flux:label>
                    <flux:error name="is_active" />
                </flux:field>
            @endif
            <div class="flex gap-2 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
                <flux:button type="button" variant="ghost" wire:click="closeModals">{{ __('Batal') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
