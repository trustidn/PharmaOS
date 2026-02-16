<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AppSettingsService
{
    private const CACHE_KEY = 'app_settings';

    private const KEYS = [
        'app_name' => 'PharmaOS',
        'logo_path' => null,
        'favicon_path' => null,
        'tagline' => null,
    ];

    public function get(string $key): ?string
    {
        $all = $this->all();

        return $all[$key] ?? null;
    }

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $rows = DB::table('app_settings')->pluck('value', 'key');

            return array_merge(self::KEYS, $rows->toArray());
        });
    }

    public function set(string $key, ?string $value): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
        Cache::forget(self::CACHE_KEY);
    }

    public function getAppName(): string
    {
        return $this->get('app_name') ?? config('app.name', 'PharmaOS');
    }

    public function getLogoUrl(): ?string
    {
        $path = $this->get('logo_path');

        return $path ? Storage::url($path) : null;
    }

    public function getFaviconUrl(): ?string
    {
        $path = $this->get('favicon_path');

        return $path ? Storage::url($path) : null;
    }

    public function getTagline(): ?string
    {
        return $this->get('tagline');
    }
}
