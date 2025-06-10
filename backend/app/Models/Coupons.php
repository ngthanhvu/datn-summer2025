<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupons extends Model
{
    use HasFactory;
    
    protected $table = 'coupons';
    
    protected $fillable = [
        'name',                  // Tên giảm giá
        'code',                  // Mã giảm giá
        'type',                  // Loại giảm giá (percent/fixed)
        'value',                 // Giá trị giảm
        'min_order_value',       // Giá trị đơn hàng tối thiểu
        'max_discount_value',    // Giảm giá tối đa
        'usage_limit',           // Giới hạn số lần sử dụng
        'used_count',            // Số lần đã sử dụng
        'start_date',            // Ngày bắt đầu
        'end_date',              // Ngày kết thúc
        'is_active',             // Trạng thái
    ];
    
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'min_order_value' => 'decimal:2',
        'max_discount_value' => 'decimal:2',
        'value' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Kiểm tra xem mã giảm giá có còn hiệu lực không
    public function isValid()
    {
        $now = now();
        return $this->is_active &&
               $now->between($this->start_date, $this->end_date) &&
               ($this->usage_limit === null || $this->used_count < $this->usage_limit);
    }
}
