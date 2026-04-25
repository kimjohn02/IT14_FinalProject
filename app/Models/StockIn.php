<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $fillable = [
        'stock_in_date',
        'reference_no',
        'received_by_user_id',
        'status'
    ];

    protected $casts = [
        'stock_in_date' => 'datetime'
    ];

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id')->withDefault([
            'f_name' => 'Unknown',
            'm_name' => null,
            'l_name' => 'User',
            'full_name' => 'Unknown User'
        ]);
    }

    public function items()
    {
        return $this->hasMany(StockInItem::class, 'stock_in_id');
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->count();
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity_received');
    }

    public function getTotalCostAttribute()
    {
        return $this->items->sum(function($item) {
            return $item->quantity_received * $item->actual_unit_cost;
        });
    }

    
}
