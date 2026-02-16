<?php

namespace App\Models;

use App\Concerns\HasMoneyAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory, HasMoneyAttributes;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'order_unit_name',
        'conversion_factor',
        'batch_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conversion_factor' => 'integer',
        ];
    }

    /**
     * Quantity in base unit (for stock). quantity = order quantity, conversion_factor = base per order unit.
     */
    public function quantityInBaseUnit(): int
    {
        return $this->quantity * $this->conversion_factor;
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
