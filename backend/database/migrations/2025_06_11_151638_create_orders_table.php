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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('payment_method');
            $table->string('payment_status')->default('unpaid');
            $table->integer('total_price');
            $table->integer('discount_price')->default(0);
            $table->integer('final_price');
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->string('tracking_code')->nullable()->unique();
            $table->string('return_status')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->text('reject_reason')->nullable();
            $table->text('return_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
