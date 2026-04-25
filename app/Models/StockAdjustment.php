<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'adjustment_date',
        'adjustment_type',
        'reason_notes',
        'processed_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'adjustment_date' => 'datetime',
    ];

    /**
     * Get the user who processed this adjustment.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    /**
     * Get the items for this adjustment.
     */
    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }
}