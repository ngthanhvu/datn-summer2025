<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{

    protected $table = 'product_variants';

    protected $fillable = [
        'price',
        'original_price',
        'quantity',
        'sku',
        'products_id',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'products_id');
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_values', 'product_variant_id', 'attribute_value_id');
    }
}
