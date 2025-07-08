<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;
use App\Models\Categories;
use App\Models\Brands;
use App\Models\Variants;

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
    public function categories()
    {
        return $this->belongsTo(Categories::class, 'categories_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brands::class, 'brand_id');
    }

    public function variants()
    {
        return $this->hasMany(Variants::class, 'product_id');
    }
}
