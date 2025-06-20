<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Products;

class FavoriteProduct extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_slug'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_slug', 'slug');
    }
}
