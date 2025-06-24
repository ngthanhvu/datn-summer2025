<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Messenger extends Model
{
    use HasFactory;
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'attachment',
        'is_read',
        'sent_at',
        'read_at',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function scopeUnread($query, $userId)
    {
        return $query->where('receiver_id', $userId)
            ->where('is_read', false);
    }
}
