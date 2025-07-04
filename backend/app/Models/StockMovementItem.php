<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovementItem extends Model
{
    use SoftDeletes;
    protected $table = 'stock_movement_items';
    protected $fillable = [
        'stock_movement_id',
        'variant_id',
        'quantity',
        'unit_price'
    ];

    public function stockMovement()
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }

    public function variant()
    {
        return $this->belongsTo(Variants::class, 'variant_id');
    }
}
