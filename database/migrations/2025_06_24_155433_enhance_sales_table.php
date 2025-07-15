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
        Schema::table('sales', function (Blueprint $table) {
            // 添加销售类型字段
            $table->enum('sale_type', ['standard', 'blind_bag', 'mixed'])->default('standard')->after('store_id')->comment('销售类型');
            
            // 添加索引
            $table->index('sale_type');
            $table->index(['store_id', 'sale_type']);
            $table->index(['created_at', 'sale_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // 删除索引和字段
            $table->dropIndex(['sales_sale_type_index']);
            $table->dropIndex(['sales_store_id_sale_type_index']);
            $table->dropIndex(['sales_created_at_sale_type_index']);
            $table->dropColumn('sale_type');
        });
    }
};
