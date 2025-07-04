<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->foreignId('variant_id')->constrained('variants')->onDelete('cascade');
        });
        Schema::table('stock_movement_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->foreignId('variant_id')->constrained('variants')->onDelete('cascade');
            $table->integer('unit_price')->default(0);
        });
    }
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        });
        Schema::table('stock_movement_items', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
            $table->dropColumn('unit_price');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        });
    }
};
