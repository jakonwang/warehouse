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
        // 退货记录表索引
        if (!Schema::hasIndex('return_records', 'return_records_created_at_index')) {
            Schema::table('return_records', function (Blueprint $table) {
                $table->index('created_at', 'return_records_created_at_index');
            });
        }

        if (!Schema::hasIndex('return_records', 'return_records_store_id_index')) {
            Schema::table('return_records', function (Blueprint $table) {
                $table->index('store_id', 'return_records_store_id_index');
            });
        }

        // 入库记录表索引
        if (!Schema::hasIndex('stock_in_records', 'stock_in_records_created_at_index')) {
            Schema::table('stock_in_records', function (Blueprint $table) {
                $table->index('created_at', 'stock_in_records_created_at_index');
            });
        }

        if (!Schema::hasIndex('stock_in_records', 'stock_in_records_store_id_index')) {
            Schema::table('stock_in_records', function (Blueprint $table) {
                $table->index('store_id', 'stock_in_records_store_id_index');
            });
        }

        // 出库记录表索引
        if (!Schema::hasIndex('stock_out_records', 'stock_out_records_created_at_index')) {
            Schema::table('stock_out_records', function (Blueprint $table) {
                $table->index('created_at', 'stock_out_records_created_at_index');
            });
        }

        if (!Schema::hasIndex('stock_out_records', 'stock_out_records_store_id_index')) {
            Schema::table('stock_out_records', function (Blueprint $table) {
                $table->index('store_id', 'stock_out_records_store_id_index');
            });
        }

        // 活动记录表索引
        if (!Schema::hasIndex('activities', 'activities_created_at_index')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->index('created_at', 'activities_created_at_index');
            });
        }

        if (!Schema::hasIndex('activities', 'activities_user_id_index')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->index('user_id', 'activities_user_id_index');
            });
        }

        // 盲袋发货明细表索引
        if (!Schema::hasIndex('blind_bag_deliveries', 'blind_bag_deliveries_sale_id_index')) {
            Schema::table('blind_bag_deliveries', function (Blueprint $table) {
                $table->index('sale_id', 'blind_bag_deliveries_sale_id_index');
            });
        }

        // 销售详情表索引
        if (!Schema::hasIndex('sale_details', 'sale_details_sale_id_index')) {
            Schema::table('sale_details', function (Blueprint $table) {
                $table->index('sale_id', 'sale_details_sale_id_index');
            });
        }

        // 库存记录表索引
        if (!Schema::hasIndex('inventory_records', 'inventory_records_inventory_id_index')) {
            Schema::table('inventory_records', function (Blueprint $table) {
                $table->index('inventory_id', 'inventory_records_inventory_id_index');
            });
        }

        // 用户仓库关联表索引
        if (!Schema::hasIndex('store_user', 'store_user_user_id_index')) {
            Schema::table('store_user', function (Blueprint $table) {
                $table->index('user_id', 'store_user_user_id_index');
            });
        }

        if (!Schema::hasIndex('store_user', 'store_user_store_id_index')) {
            Schema::table('store_user', function (Blueprint $table) {
                $table->index('store_id', 'store_user_store_id_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 删除退货记录表索引
        Schema::table('return_records', function (Blueprint $table) {
            $table->dropIndex('return_records_created_at_index');
            $table->dropIndex('return_records_store_id_index');
        });

        // 删除入库记录表索引
        Schema::table('stock_in_records', function (Blueprint $table) {
            $table->dropIndex('stock_in_records_created_at_index');
            $table->dropIndex('stock_in_records_store_id_index');
        });

        // 删除出库记录表索引
        Schema::table('stock_out_records', function (Blueprint $table) {
            $table->dropIndex('stock_out_records_created_at_index');
            $table->dropIndex('stock_out_records_store_id_index');
        });

        // 删除活动记录表索引
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('activities_created_at_index');
            $table->dropIndex('activities_user_id_index');
        });

        // 删除盲袋发货明细表索引
        Schema::table('blind_bag_deliveries', function (Blueprint $table) {
            $table->dropIndex('blind_bag_deliveries_sale_id_index');
        });

        // 删除销售详情表索引
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropIndex('sale_details_sale_id_index');
        });

        // 删除库存记录表索引
        Schema::table('inventory_records', function (Blueprint $table) {
            $table->dropIndex('inventory_records_inventory_id_index');
        });

        // 删除用户仓库关联表索引
        Schema::table('store_user', function (Blueprint $table) {
            $table->dropIndex('store_user_user_id_index');
            $table->dropIndex('store_user_store_id_index');
        });
    }
}; 