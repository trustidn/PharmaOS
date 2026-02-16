<?php

namespace App\Livewire\Settings;

use App\Services\TenantContext;
use Livewire\Component;
use Livewire\WithFileUploads;

class BrandingSettings extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $address = '';

    public string $phone = '';

    public string $website = '';

    public string $primary_color = '#3B82F6';

    public string $secondary_color = '#1E40AF';

    public $logo;

    public ?string $current_logo = null;

    public function mount(): void
    {
        $context = app(TenantContext::class);
        $tenant = $context->getTenant();

        if ($tenant) {
            $this->name = $tenant->name ?? '';
            $this->address = $tenant->address ?? '';
            $this->phone = $tenant->phone ?? '';
            $this->website = $tenant->website ?? '';
            $this->primary_color = $tenant->primary_color ?? '#3B82F6';
            $this->secondary_color = $tenant->secondary_color ?? '#1E40AF';
            $this->current_logo = $tenant->logo_path;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'url', 'max:255'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $context = app(TenantContext::class);
        $tenant = $context->getTenant();

        if (! $tenant) {
            return;
        }

        $data = [
            'name' => $this->name,
            'address' => $this->address ?: null,
            'phone' => $this->phone ?: null,
            'website' => $this->website ?: null,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
        ];

        if ($this->logo) {
            $data['logo_path'] = $this->logo->store('tenant-logos', 'public');
        }

        $tenant->update($data);

        session()->flash('success', 'Branding berhasil diperbarui. Refresh halaman untuk melihat perubahan.');
    }

    public function render()
    {
        return view('livewire.settings.branding-settings');
    }
}
