<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'user_id',
        'refund_payment_id',
        'total_refund_amount',
        'return_reason',
        'notes'
    ];

    protected $casts = [
        'total_refund_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'return_reason' => 'string', 
    ];

    /**
     * Get the sale that this return belongs to
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the user (staff) who processed the return
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the refund payment record
     */
    public function refundPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'refund_payment_id');
    }

    /**
     * Get the items included in this return
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'product_return_id');
    }

    /**
     * Scope to get returns within a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get returns by sale ID
     */
    public function scopeForSale($query, $saleId)
    {
        return $query->where('sale_id', $saleId);
    }
}