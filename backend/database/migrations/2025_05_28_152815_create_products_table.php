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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price');
            $table->integer('original_price')->nullable();
            $table->integer('discount_price')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('slug')->unique();
            $table->foreignId('categories_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
