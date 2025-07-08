<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;

class FlashSaleProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'flash_sale_id',
        'product_id',
        'flash_price',
        'quantity',
        'sold'
    ];

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class);
    }
    public function mainImage()
    {
        return $this->hasOne(Images::class, 'product_id')->where('is_main', true);
    }
}
