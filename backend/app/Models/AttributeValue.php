<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'attribute_id'
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attribute_values');
    }
} 