<?php

namespace App\Models;

use App\Concerns\HasMoneyAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionItem extends Model
{
    use HasFactory, HasMoneyAttributes;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'unit_name',
        'conversion_factor',
        'quantity',
        'unit_price',
        'discount_amount',
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

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batchDeductions(): HasMany
    {
        return $this->hasMany(BatchDeduction::class);
    }
}
