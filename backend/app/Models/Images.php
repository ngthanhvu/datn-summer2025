<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Images extends Model
{
    use HasFactory;
    protected $fillable = [
        'image_path',
        'is_main',
        'product_id',
        'variant_id'
    ];

    protected $appends = ['image_url'];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(Variants::class, 'variant_id');
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            // Đảm bảo đường dẫn bắt đầu với storage/
            $imagePath = $this->image_path;
            if (!str_starts_with($imagePath, 'storage/')) {
                $imagePath = 'storage/' . ltrim($imagePath, '/');
            }
            return url($imagePath);
        }
        return null;
    }
}
