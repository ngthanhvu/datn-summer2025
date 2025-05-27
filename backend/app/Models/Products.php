<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'original_price',
        'quantity',
        'brand_id',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brands::class);
    }

    public function images()
    {
        return $this->hasMany(Products_image::class);
    }

    public function variants()
    {
        return $this->hasMany(Products_varriant::class);
    }
}
