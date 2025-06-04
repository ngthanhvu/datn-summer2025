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
        'sku',
        'product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
