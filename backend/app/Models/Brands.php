<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brands extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'description',
        'image',
        'slug',
        'parent_id',
        'is_active',
    ];

    public function products()
    {
        return $this->hasMany(Products::class, 'brand_id');
    }

    public function parent()
    {
        return $this->belongsTo(Brands::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Brands::class, 'parent_id');
    }
}
