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
        Schema::table('stock_out_details', function (Blueprint $table) {
            // 添加product_id字段
            $table->foreignId('product_id')->nullable()->constrained();
            
            // 添加total_cost字段用于记录成本
            $table->decimal('total_cost', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_out_details', function (Blueprint $table) {
            // 删除product_id字段
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            
            // 删除total_cost字段
            $table->dropColumn('total_cost');
        });
    }
};
