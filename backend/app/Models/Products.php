<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Products extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'original_price',
        'discount_price',
        'quantity',
        'slug',
        'categories_id',
        'brand_id',
    ];

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
        return $this->hasMany(Variants::class);
    }
}
