<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUser extends Model
{
    use HasFactory;

    protected $table = 'coupon_user';

    protected $fillable = [
        'user_id',
        'coupon_id',
        'claimed_at',
        'used_at',
        'status'
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'used_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupons::class, 'coupon_id');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    public function scopeActive($query)
    {
        return $query->whereHas('coupon', function ($q) {
            $q->where('is_active', true)
              ->where('end_date', '>', now());
        });
    }

    // Helper methods
    public function isClaimed()
    {
        return $this->status === 'claimed';
    }

    public function isUsed()
    {
        return $this->status === 'used';
    }

    public function markAsUsed($orderId = null)
    {
        $this->update([
            'status' => 'used',
            'used_at' => now()
        ]);
    }
}
