<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brands extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    
    // public function products(): HasMany
    // {
    //     return $this->hasMany(Product::class);
    // }

 
    public function parent()
    {
        return $this->belongsTo(Brands::class, 'parent_id');
    }


    public function children()
    {
        return $this->hasMany(Brands::class, 'parent_id');
    }

 
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

   
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

  
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
