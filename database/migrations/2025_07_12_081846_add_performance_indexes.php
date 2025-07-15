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
        // 为销售表添加索引（如果不存在）
        if (!Schema::hasIndex('sales', 'sales_created_at_total_amount_index')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->index(['created_at', 'total_amount'], 'sales_created_at_total_amount_index');
            });
        }

        // 为库存表添加索引（如果不存在）
        if (!Schema::hasIndex('inventory', 'inventory_product_store_index')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->index(['product_id', 'store_id'], 'inventory_product_store_index');
            });
        }

        if (!Schema::hasIndex('inventory', 'inventory_quantity_min_index')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->index(['quantity', 'min_quantity'], 'inventory_quantity_min_index');
            });
        }

        // 为商品表添加索引（如果不存在）
        if (!Schema::hasIndex('products', 'products_active_type_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['is_active', 'type'], 'products_active_type_index');
            });
        }

        // 为库存记录表添加索引（如果不存在）
        if (!Schema::hasIndex('inventory_records', 'inventory_records_inventory_created_index')) {
            Schema::table('inventory_records', function (Blueprint $table) {
                $table->index(['inventory_id', 'created_at'], 'inventory_records_inventory_created_index');
            });
        }

        // 为用户仓库关联表添加索引（如果不存在）
        if (!Schema::hasIndex('store_user', 'store_user_user_store_index')) {
            Schema::table('store_user', function (Blueprint $table) {
                $table->index(['user_id', 'store_id'], 'store_user_user_store_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 移除销售表索引
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_created_at_total_amount_index');
        });

        // 移除库存表索引
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropIndex('inventory_product_store_index');
            $table->dropIndex('inventory_quantity_min_index');
        });

        // 移除商品表索引
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_type_index');
        });

        // 移除库存记录表索引
        Schema::table('inventory_records', function (Blueprint $table) {
            $table->dropIndex('inventory_records_inventory_created_index');
        });

        // 移除用户仓库关联表索引
        Schema::table('store_user', function (Blueprint $table) {
            $table->dropIndex('store_user_user_store_index');
        });
    }
};
