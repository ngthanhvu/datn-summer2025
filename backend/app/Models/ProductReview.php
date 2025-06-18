<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_slug',
        'rating',
        'content',
        'parent_id',
        'is_admin_reply',
        'is_approved',
        'is_hidden'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_slug', 'slug');
    }

    public function parent()
    {
        return $this->belongsTo(ProductReview::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(ProductReview::class, 'parent_id');
    }

    public function images()
    {
        return $this->hasMany(ReviewImage::class, 'review_id');
    }
}