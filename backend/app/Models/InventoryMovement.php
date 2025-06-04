<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'variant_id',
        'type',        // 'import' | 'export' | 'adjustment'
        'quantity',
        'note',
        'user_id',
    ];

    public $timestamps = true;

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variants::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
