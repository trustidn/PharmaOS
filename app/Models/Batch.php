<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Concerns\HasMoneyAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Batch extends Model
{
    use BelongsToTenant, HasFactory, HasMoneyAttributes;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'batch_number',
        'purchase_price',
        'quantity_received',
        'quantity_remaining',
        'expired_at',
        'received_at',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expired_at' => 'date',
            'received_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function batchDeductions(): HasMany
    {
        return $this->hasMany(BatchDeduction::class);
    }

    /**
     * PO item that created this batch (when received via PO). Null if batch was added manually.
     */
    public function purchaseOrderItem(): HasOne
    {
        return $this->hasOne(PurchaseOrderItem::class);
    }

    public function isExpired(): bool
    {
        return $this->expired_at->isPast();
    }

    public function isNearExpiry(int $days = 90): bool
    {
        return ! $this->isExpired() && $this->expired_at->diffInDays(now()) <= $days;
    }

    public function hasStock(): bool
    {
        return $this->quantity_remaining > 0;
    }

    /**
     * Scope for FEFO ordering: active, not expired, with remaining stock.
     */
    public function scopeAvailableForSale($query)
    {
        return $query->where('is_active', true)
            ->where('expired_at', '>', now())
            ->where('quantity_remaining', '>', 0)
            ->orderBy('expired_at', 'asc');
    }
}
