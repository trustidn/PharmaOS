<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Concerns\HasMoneyAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    use BelongsToTenant, HasFactory, HasMoneyAttributes;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'name',
        'conversion_factor',
        'price_sell',
        'barcode',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conversion_factor' => 'integer',
            'price_sell' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Price in IDR string (price_sell stored in cents).
     */
    public function formattedPrice(): string
    {
        return $this->formatMoney($this->price_sell);
    }
}
