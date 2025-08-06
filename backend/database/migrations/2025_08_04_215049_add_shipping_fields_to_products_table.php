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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('weight')->default(500)->comment('Cân nặng sản phẩm (gram)');
            $table->integer('length')->default(20)->comment('Chiều dài sản phẩm (cm)');
            $table->integer('width')->default(20)->comment('Chiều rộng sản phẩm (cm)');
            $table->integer('height')->default(20)->comment('Chiều cao sản phẩm (cm)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight', 'length', 'width', 'height']);
        });
    }
};
