<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'oauth_provider',
        'oauth_id',
        'otp',
        'otp_expires_at',
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
}
