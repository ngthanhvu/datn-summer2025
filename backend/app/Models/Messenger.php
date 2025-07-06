<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Messenger extends Model
{
    use HasFactory;
    protected $fillable = [
        'user1_id',
        'user2_id',
        'messages',
    ];

    protected $casts = [
        'messages' => 'array',
    ];

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function scopeUnread($query, $userId)
    {
        return $query->where('receiver_id', $userId)
            ->where('is_read', false);
    }
}
