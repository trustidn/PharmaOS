<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Concerns\HasMoneyAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant, HasFactory, HasMoneyAttributes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'unit_id',
        'base_unit',
        'sku',
        'barcode',
        'name',
        'generic_name',
        'description',
        'selling_price',
        'min_stock',
        'requires_prescription',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class);
    }

    /**
     * Additional sellable units (conversion_factor > 1) with their own prices.
     * Base unit price is product.selling_price.
     */
    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the total available stock across all active, non-expired batches.
     */
    public function totalStock(): int
    {
        return $this->batches()
            ->where('is_active', true)
            ->where('expired_at', '>', now())
            ->where('quantity_remaining', '>', 0)
            ->sum('quantity_remaining');
    }

    /**
     * Check if the product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->totalStock() <= $this->min_stock;
    }

    public function formattedSellingPrice(): string
    {
        return $this->formatMoney($this->selling_price);
    }
}
