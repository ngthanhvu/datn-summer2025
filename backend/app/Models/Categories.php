<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categories extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'image',
        'slug',
        'parent_id',
        'is_active',
    ];

    public function parent()
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Products::class, 'categories_id');
    }

    public function blogs()
    {
        return $this->hasMany(Blogs::class, 'category_id');
    }
}
