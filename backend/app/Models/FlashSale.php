<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FlashSaleProduct;

class FlashSale extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'start_time', 'end_time', 'repeat', 'repeat_minutes', 'auto_increase', 'active'
    ];

    public function products()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }
    public function mainImage()
    {
        return $this->hasOne(Images::class, 'product_id')->where('is_main', true);
    }
} 