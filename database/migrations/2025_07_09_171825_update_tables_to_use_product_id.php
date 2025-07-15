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
        // 更新stock_in_details表
        Schema::table('stock_in_details', function (Blueprint $table) {
            // 删除series_code字段（如果存在）
            if (Schema::hasColumn('stock_in_details', 'series_code')) {
                $table->dropIndex(['series_code']);
                $table->dropColumn('series_code');
            }
            
            // 添加product_id外键（如果不存在）
            if (!Schema::hasColumn('stock_in_details', 'product_id')) {
                $table->foreignId('product_id')->after('stock_in_record_id')->constrained('products');
            }
        });

        // 更新inventory_check_details表
        Schema::table('inventory_check_details', function (Blueprint $table) {
            // 删除series_code字段（如果存在）
            if (Schema::hasColumn('inventory_check_details', 'series_code')) {
                $table->dropIndex(['series_code']);
                $table->dropColumn('series_code');
            }
            
            // 删除price_series_id外键（如果存在）
            if (Schema::hasColumn('inventory_check_details', 'price_series_id')) {
                $table->dropForeign(['price_series_id']);
                $table->dropColumn('price_series_id');
            }
            
            // 添加product_id外键（如果不存在）
            if (!Schema::hasColumn('inventory_check_details', 'product_id')) {
                $table->foreignId('product_id')->after('inventory_check_record_id')->constrained('products');
            }
        });

        // 更新inventories表（如果需要）
        Schema::table('inventories', function (Blueprint $table) {
            // 删除series_code字段（如果存在）
            if (Schema::hasColumn('inventories', 'series_code')) {
                $table->dropIndex(['series_code']);
                $table->dropColumn('series_code');
            }
            
            // 删除price_series_id外键（如果存在）
            if (Schema::hasColumn('inventories', 'price_series_id')) {
                $table->dropForeign(['price_series_id']);
                $table->dropColumn('price_series_id');
            }
            
            // 确保product_id外键存在且不为空
            if (Schema::hasColumn('inventories', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable(false)->change();
                // 如果没有外键约束，添加它
                try {
                    $table->foreign('product_id')->references('id')->on('products');
                } catch (Exception $e) {
                    // 外键可能已存在，忽略错误
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滚更改
        Schema::table('stock_in_details', function (Blueprint $table) {
            if (Schema::hasColumn('stock_in_details', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            $table->string('series_code')->after('stock_in_record_id');
            $table->index('series_code');
        });

        Schema::table('inventory_check_details', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_check_details', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            $table->string('series_code')->after('inventory_check_record_id');
            $table->index('series_code');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->string('series_code')->nullable()->after('store_id');
            $table->index('series_code');
        });
    }
};
