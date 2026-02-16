<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Concerns\HasMoneyAttributes;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use BelongsToTenant, HasFactory, HasMoneyAttributes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'invoice_number',
        'type',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'payment_method',
        'amount_paid',
        'change_amount',
        'notes',
        'buyer_name',
        'buyer_phone',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'payment_method' => PaymentMethod::class,
            'completed_at' => 'datetime',
        ];
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::Completed;
    }

    public function isVoided(): bool
    {
        return $this->status === TransactionStatus::Voided;
    }

    /**
     * Generate a unique invoice number for the tenant.
     */
    public static function generateInvoiceNumber(int $tenantId): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $count = static::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }
}
