<?php

namespace App\Livewire\Settings;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;

class TenantUserManagement extends Component
{
    use WithPagination;

    public bool $showFormModal = false;

    public bool $showEditModal = false;

    public ?int $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = '';

    public bool $is_active = true;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $user = User::where('id', $userId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->is_active = $user->is_active;
        $this->password = '';
        $this->showEditModal = true;
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showEditModal = false;
        $this->editingUserId = null;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = UserRole::Cashier->value;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function saveNewUser(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'string', 'in:'.implode(',', [UserRole::Pharmacist->value, UserRole::Cashier->value])],
        ]);

        $planLimit = app(PlanLimitService::class);
        if (! $planLimit->canAddUser()) {
            session()->flash('error', 'Batas jumlah user untuk paket Anda telah tercapai.');

            return;
        }

        User::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => UserRole::from($this->role),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        session()->flash('success', 'User berhasil ditambahkan.');
        $this->closeModals();
    }

    public function updateUser(): void
    {
        $user = User::where('id', $this->editingUserId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string', 'in:'.implode(',', [UserRole::Owner->value, UserRole::Pharmacist->value, UserRole::Cashier->value])],
            'is_active' => ['boolean'],
        ];

        if ($this->password !== '') {
            $rules['password'] = ['string', Password::defaults()];
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($user->id !== auth()->id()) {
            $data['role'] = UserRole::from($this->role);
            $data['is_active'] = $this->is_active;
        }

        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);

        session()->flash('success', 'User berhasil diperbarui.');
        $this->closeModals();
    }

    public function toggleActive(int $userId): void
    {
        $user = User::where('id', $userId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        session()->flash('success', $user->is_active ? 'User diaktifkan.' : 'User dinonaktifkan.');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return view('livewire.settings.tenant-user-management', ['users' => collect(), 'canAddUser' => false]);
        }

        $users = User::query()
            ->where('tenant_id', $tenantId)
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->orderBy('name')
            ->paginate(15);

        $canAddUser = app(PlanLimitService::class)->canAddUser();

        return view('livewire.settings.tenant-user-management', [
            'users' => $users,
            'canAddUser' => $canAddUser,
        ]);
    }
}
