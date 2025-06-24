<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReviewImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'image_path'
    ];

    public function review()
    {
        return $this->belongsTo(ProductReview::class, 'review_id');
    }
}