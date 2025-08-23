<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('product_slug');
            $table->foreign('product_slug')
                ->references('slug')
                ->on('products')
                ->onDelete('cascade');
            $table->integer('rating')->nullable();
            $table->text('content');
            $table->foreignId('parent_id')->nullable()->constrained('product_reviews')->onDelete('cascade');
            $table->boolean('is_admin_reply')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
