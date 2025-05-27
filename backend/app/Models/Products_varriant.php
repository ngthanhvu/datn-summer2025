<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Products_varriant extends Model
{
    use HasFactory;

    protected $fillable = [
        'size',
        'price',
        'quantity',
        'sku',
        'product_id',
    ];
    public function product()
    {
        return $this->belongsTo(Products::class);
    }
}
