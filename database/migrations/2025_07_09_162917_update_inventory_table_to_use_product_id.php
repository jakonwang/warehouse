<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added DB facade import

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            // 检查并添加新列
            if (!Schema::hasColumn('inventory', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('id');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('inventory', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->after('product_id');
                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            }
        });

        // 数据迁移：将series_code映射到product_id
        DB::transaction(function () {
            // 获取所有库存记录
            $inventories = DB::table('inventory')->get();
            
            foreach ($inventories as $inventory) {
                if (!empty($inventory->series_code)) {
                    // 根据series_code查找对应的product
                    $product = DB::table('products')
                        ->where('name', 'LIKE', '%' . $inventory->series_code . '%')
                        ->orWhere('code', $inventory->series_code)
                        ->first();
                    
                    if ($product) {
                        DB::table('inventory')
                            ->where('id', $inventory->id)
                            ->update([
                                'product_id' => $product->id,
                                'store_id' => 1, // 默认门店ID
                            ]);
                    }
                }
            }
        });

        // 删除旧列
        Schema::table('inventory', function (Blueprint $table) {
            if (Schema::hasColumn('inventory', 'series_code')) {
                $table->dropColumn('series_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            // 恢复旧结构
            $table->string('series_code')->nullable();
            
            if (Schema::hasColumn('inventory', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            
            if (Schema::hasColumn('inventory', 'store_id')) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            }
        });
    }
};
