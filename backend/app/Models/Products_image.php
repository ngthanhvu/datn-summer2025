<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Products_image extends Model
{
    use HasFactory;

    protected $table = 'products_images';

    protected $fillable = [
        'is_main',
        'sub_image',
        'product_id',
    ];
}
