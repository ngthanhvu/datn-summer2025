<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variants extends Model
{
    use HasFactory;
    protected $fillable = [
        'color',
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
