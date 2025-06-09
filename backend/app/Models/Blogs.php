<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'status',
        'published_at',
        'author_id',
        'category_id',
        'tags',
        'meta_title',
        'meta_description',
        'views_count'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tags' => 'array',
        'views_count' => 'integer'
    ];

    protected $dates = [
        'published_at',
        'deleted_at'
    ];

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Accessors
    public function getExcerptAttribute($value)
    {
        return $value ?: \Str::limit(strip_tags($this->content), 150);
    }

    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return ceil($wordCount / 200); // Average reading speed 200 words per minute
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = \Str::slug($blog->title);
            }
        });
    }
}