<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Categories extends Model
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

    /**
     * Get the products for the Categories.
     */
    // public function products(): HasMany
    // {
    //     return $this->hasMany(Product::class);
    // }

    /**
     * Get the parent Categories.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    /**
     * Get the direct children categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }

    /**
     * Get all descendant categories.
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get root categories (categories without parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
