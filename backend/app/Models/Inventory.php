<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = ['variant_id', 'quantity'];

    public $timestamps = true;

    public function variant()
    {
        return $this->belongsTo(Variants::class, 'variant_id');
    }
}
