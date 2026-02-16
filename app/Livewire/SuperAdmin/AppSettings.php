<?php

namespace App\Livewire\SuperAdmin;

use App\Services\AppSettingsService;
use Livewire\Component;
use Livewire\WithFileUploads;

class AppSettings extends Component
{
    use WithFileUploads;

    public string $app_name = '';

    public string $tagline = '';

    public $logo;

    public $favicon;

    public ?string $current_logo_url = null;

    public ?string $current_favicon_url = null;

    public function mount(AppSettingsService $settings): void
    {
        $this->app_name = $settings->getAppName();
        $this->tagline = (string) $settings->getTagline();
        $this->current_logo_url = $settings->getLogoUrl();
        $this->current_favicon_url = $settings->getFaviconUrl();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
        ];
    }

    public function save(AppSettingsService $settings): void
    {
        $this->validate();

        $settings->set('app_name', $this->app_name);
        $settings->set('tagline', $this->tagline ?: null);

        if ($this->logo) {
            $path = $this->logo->store('app-assets', 'public');
            $settings->set('logo_path', $path);
        }

        if ($this->favicon) {
            $path = $this->favicon->store('app-assets', 'public');
            $settings->set('favicon_path', $path);
        }

        session()->flash('success', __('Pengaturan aplikasi berhasil disimpan.'));
    }

    public function render()
    {
        return view('livewire.super-admin.app-settings')
            ->layout('layouts.app', ['title' => __('Pengaturan Aplikasi')]);
    }
}
