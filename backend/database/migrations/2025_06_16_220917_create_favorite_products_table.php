<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorite_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('product_slug');
            $table->foreign('product_slug')
                ->references('slug')
                ->on('products')
                ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['user_id', 'product_slug']); // tránh thêm trùng 1 sản phẩm
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_products');
    }
};
