<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Concerns\HasMoneyAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use BelongsToTenant, HasFactory, HasMoneyAttributes;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'invoice_number',
        'total_amount',
        'notes',
        'ordered_at',
        'received_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ordered_at' => 'date',
            'received_at' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isReceived(): bool
    {
        return $this->received_at !== null;
    }

    public static function generateInvoiceNumber(int $tenantId): string
    {
        $date = now()->format('Ymd');
        $count = static::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->count() + 1;

        return 'PO-'.$date.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
