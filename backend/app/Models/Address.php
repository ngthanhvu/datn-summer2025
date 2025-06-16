<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'full_name',
        'phone',
        'province',
        'district',
        'ward',
        'street',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
