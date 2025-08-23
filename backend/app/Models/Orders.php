<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'status',
        'payment_method',
        'payment_status',
        'total_price',
        'discount_price',
        'final_price',
        'coupon_id',
        'note',
        'tracking_code',
        'cancel_reason',
        'return_status',
        'reject_reason',
        'return_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupons::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(Orders_detail::class, 'order_id');
    }
}
