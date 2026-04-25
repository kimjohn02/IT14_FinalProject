<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $fillable = [
        'sale_id', 
        'payment_date',
        'payment_method',
        'amount_tendered',
        'change_given',
        'reference_no'
    ];
    
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
