<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Orders_detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'variant_id',
        'quantity',
        'price',
        'original_price',
        'total_price',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function variant()
    {
        return $this->belongsTo(Variants::class);
    }
}
