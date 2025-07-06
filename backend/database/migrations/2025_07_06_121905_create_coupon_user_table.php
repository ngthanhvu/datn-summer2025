<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupon_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->timestamp('claimed_at')->nullable();  // Thời gian user lưu coupon
            $table->timestamp('used_at')->nullable();     // Thời gian user sử dụng coupon
            $table->enum('status', ['claimed', 'used'])->default('claimed');
            $table->timestamps();

            $table->unique(['user_id', 'coupon_id']); // 1 user không lưu 1 coupon 2 lần
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_user');
    }
};
