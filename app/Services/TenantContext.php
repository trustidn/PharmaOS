<?php

namespace App\Services;

use App\Models\Tenant;

/**
 * Singleton service that holds the current tenant context.
 *
 * Bound as a singleton in AppServiceProvider and populated
 * by the SetTenantContext middleware on each request.
 */
class TenantContext
{
    private ?Tenant $tenant = null;

    /**
     * Set the current tenant.
     */
    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the current tenant.
     */
    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the current tenant ID.
     */
    public function getTenantId(): ?int
    {
        return $this->tenant?->id;
    }

    /**
     * Check if a tenant is currently set.
     */
    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Clear the current tenant context.
     */
    public function clear(): void
    {
        $this->tenant = null;
    }
}
