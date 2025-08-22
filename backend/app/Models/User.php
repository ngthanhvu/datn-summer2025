<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'oauth_provider',
        'oauth_id',
        'otp',
        'otp_expires_at',
        'phone',
        'avatar',
        'gender',
        'dateOfBirth',
        'ip_user',
        'status',
    ];
    protected $hidden = [
        'password',
        'otp',
    ];
    protected $casts = [
        'otp_expires_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function hasValidOtp($otp)
    {
        return $this->otp === $otp &&
            $this->otp_expires_at &&
            Carbon::now()->isBefore($this->otp_expires_at);
    }

    public function isOtpExpired()
    {
        return !$this->otp_expires_at || Carbon::now()->isAfter($this->otp_expires_at);
    }

    public function clearOtp()
    {
        return $this->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    public function setOtp($otp, $expiresInMinutes = 10)
    {
        return $this->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
        ]);
    }

    public function generateOtp($expiresInMinutes = 10)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->setOtp($otp, $expiresInMinutes);
        return $otp;
    }

    public function hasActiveOtp()
    {
        return $this->otp && $this->otp_expires_at && !$this->isOtpExpired();
    }

    public function getOtpRemainingMinutes()
    {
        if (!$this->otp_expires_at) {
            return null;
        }

        $remaining = Carbon::now()->diffInMinutes($this->otp_expires_at, false);
        return $remaining > 0 ? $remaining : 0;
    }

    public function scopeWithValidOtp($query)
    {
        return $query->whereNotNull('otp')
            ->whereNotNull('otp_expires_at')
            ->where('otp_expires_at', '>', Carbon::now());
    }

    public function scopeWithExpiredOtp($query)
    {
        return $query->whereNotNull('otp')
            ->whereNotNull('otp_expires_at')
            ->where('otp_expires_at', '<=', Carbon::now());
    }

    public function isGoogleUser()
    {
        return $this->oauth_provider === 'google';
    }

    public function isOAuthUser()
    {
        return !empty($this->oauth_provider);
    }

    public function canLoginWithPassword()
    {
        return !$this->isOAuthUser() || !empty($this->password);
    }

    public function isHybridUser()
    {
        return !empty($this->password) && $this->isOAuthUser();
    }

    public function isOAuthOnlyUser()
    {
        return $this->isOAuthUser() && empty($this->password);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
    public function coupons()
    {
        return $this->belongsToMany(Coupons::class, 'coupon_user', 'user_id', 'coupon_id')
            ->withPivot('claimed_at', 'used_at', 'status')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Orders::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function favoriteProducts()
    {
        return $this->hasMany(FavoriteProduct::class);
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }
}
