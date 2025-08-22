<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'products';
    protected $fillable = [
        'name',
        'description',
        'price',
        'discount_price',
        'slug',
        'sold_count',
        'categories_id',
        'brand_id',
        'is_active',
        'weight',
        'length',
        'width',
        'height',
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
        return $this->hasMany(Variants::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(Images::class, 'product_id');
    }

    public function mainImage()
    {
        return $this->hasOne(Images::class, 'product_id')->where('is_main', true);
    }
}
