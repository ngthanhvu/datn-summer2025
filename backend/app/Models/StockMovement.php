<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use SoftDeletes;
    protected $table = 'stock_movements';
    protected $fillable = [
        'type',
        'reference',
        'user_id',
        'note'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(StockMovementItem::class, 'stock_movement_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
