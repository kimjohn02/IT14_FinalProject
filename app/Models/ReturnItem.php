<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_return_id',
        'product_id',
        'sale_item_id',
        'quantity_returned',
        'refunded_price_per_unit',
        'total_line_refund',
        'inventory_adjusted'
    ];

    protected $casts = [
        'quantity_returned' => 'integer',
        'refunded_price_per_unit' => 'decimal:2',
        'total_line_refund' => 'decimal:2',
        'inventory_adjusted' => 'boolean'
    ];

    /**
     * Get the return that this item belongs to
     */
    public function productReturn(): BelongsTo
    {
        return $this->belongsTo(ProductReturn::class, 'product_return_id');
    }

    /**
     * Get the product that was returned
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the original sale item
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * Scope to get resaleable returns only
     */
    public function scopeResaleable($query)
    {
        return $query->where('inventory_adjusted', true);
    }

    /**
     * Scope to get damaged returns only
     */
    public function scopeDamaged($query)
    {
        return $query->where('inventory_adjusted', false);
    }

    /**
     * Check if this item is resaleable
     */
    public function isResaleable(): bool
    {
        return $this->inventory_adjusted === true;
    }

    /**
     * Check if this item is damaged
     */
    public function isDamaged(): bool
    {
        return $this->inventory_adjusted === false;
    }
}