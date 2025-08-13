<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'admin_reply',
        'replied_at'
    ];

    protected $casts = [
        'replied_at' => 'datetime'
    ];

    public function scopeByStatus($query, $status)
    {
        switch ($status) {
            case 'replied':
                return $query->whereNotNull('admin_reply');
            case 'unreplied':
                return $query->whereNull('admin_reply');
            default:
                return $query;
        }
    }

    public function scopeSearch($query, $search)
    {
        if ($search && trim($search) !== '') {
            $searchTerm = trim($search);
            return $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('phone', 'like', "%{$searchTerm}%")
                    ->orWhere('message', 'like', "%{$searchTerm}%");
            });
        }
        return $query;
    }
}
