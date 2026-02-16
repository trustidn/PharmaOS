<?php

namespace App\Services;

use App\Models\Tenant;

/**
 * Service to resolve tenant branding for white-label theming.
 */
class BrandingService
{
    public function __construct(
        private TenantContext $tenantContext,
    ) {}

    /**
     * Get branding configuration for the current tenant.
     *
     * @return array{name: string, logo_path: string|null, primary_color: string, secondary_color: string, address: string|null, phone: string|null, website: string|null}
     */
    public function getBranding(): array
    {
        $tenant = $this->tenantContext->getTenant();

        if (! $tenant) {
            return $this->defaultBranding();
        }

        return [
            'name' => $tenant->name,
            'logo_path' => $tenant->logo_path,
            'primary_color' => $tenant->primary_color ?? '#3B82F6',
            'secondary_color' => $tenant->secondary_color ?? '#1E40AF',
            'address' => $tenant->address ? (string) $tenant->address : null,
            'phone' => $tenant->phone ? (string) $tenant->phone : null,
            'website' => $tenant->website ? (string) $tenant->website : null,
        ];
    }

    /**
     * @return array{name: string, logo_path: null, primary_color: string, secondary_color: string, address: null, phone: null, website: null}
     */
    private function defaultBranding(): array
    {
        return [
            'name' => config('app.name', 'PharmaOS'),
            'logo_path' => null,
            'primary_color' => '#3B82F6',
            'secondary_color' => '#1E40AF',
            'address' => null,
            'phone' => null,
            'website' => null,
        ];
    }

    /**
     * Generate CSS custom properties for the tenant branding.
     */
    public function cssVariables(): string
    {
        $branding = $this->getBranding();

        return sprintf(
            ':root { --primary-color: %s; --secondary-color: %s; }',
            $branding['primary_color'],
            $branding['secondary_color'],
        );
    }
}
