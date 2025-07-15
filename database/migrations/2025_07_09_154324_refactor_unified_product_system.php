<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 第1步：重构products表，支持统一商品管理
        Schema::table('products', function (Blueprint $table) {
            // 确保products表有完整的字段
            if (!Schema::hasColumn('products', 'type')) {
                $table->enum('type', ['standard', 'blind_bag'])->default('standard')->after('code');
            }
            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category', 100)->nullable()->after('cost_price');
            }
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (!Schema::hasColumn('products', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });

        // 第2步：将price_series数据迁移到products表
        $this->migratePriceSeriesToProducts();

        // 第3步：重构库存表，基于product_id而非reference_code
        if (Schema::hasTable('unified_inventories')) {
            Schema::table('unified_inventories', function (Blueprint $table) {
                // 添加product_id字段（如果不存在）
                if (!Schema::hasColumn('unified_inventories', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->nullable()->after('store_id');
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                }
            });
        } else {
            // 创建简化的库存表
            Schema::create('inventories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('product_id');
                $table->integer('quantity')->default(0);
                $table->integer('alert_quantity')->default(10);
                $table->timestamp('last_stock_in_at')->nullable();
                $table->timestamp('last_stock_out_at')->nullable();
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->unique(['store_id', 'product_id']);
            });
        }

        // 第4步：迁移库存数据从series_code到product_id
        $this->migrateInventoryData();

        // 第5步：创建盲袋发货明细表
        if (!Schema::hasTable('blind_bag_deliveries')) {
            Schema::create('blind_bag_deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id');
                $table->unsignedBigInteger('blind_bag_product_id'); // 盲袋商品ID
                $table->unsignedBigInteger('delivery_product_id');  // 实际发货商品ID
                $table->integer('quantity');                       // 发货数量
                $table->decimal('unit_cost', 10, 2);              // 单位成本
                $table->decimal('total_cost', 10, 2);             // 总成本
                $table->string('remark')->nullable();             // 备注
                $table->timestamps();

                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
                $table->foreign('blind_bag_product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('delivery_product_id')->references('id')->on('products')->onDelete('cascade');
                
                $table->index(['sale_id', 'blind_bag_product_id']);
            });
        }

        // 第6步：更新sales表，添加sale_type字段（如果不存在）
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'sale_type')) {
                $table->enum('sale_type', ['standard', 'blind_bag', 'mixed'])->default('standard')->after('store_id');
            }
        });

        // 第7步：清理旧的price_series相关数据
        $this->cleanupOldData();
    }

    /**
     * 将price_series数据迁移到products表
     */
    private function migratePriceSeriesToProducts(): void
    {
        // 检查price_series表是否存在
        if (!Schema::hasTable('price_series')) {
            return;
        }

        $priceSeries = DB::table('price_series')->where('is_active', true)->get();
        
        foreach ($priceSeries as $series) {
            // 检查是否已经存在对应的商品
            $existingProduct = DB::table('products')
                ->where('code', $series->code)
                ->orWhere('name', $series->name . '商品')
                ->first();

            if (!$existingProduct) {
                // 获取最新的成本价格
                $latestCost = DB::table('price_series_costs')
                    ->where('price_series_id', $series->id)
                    ->orderBy('effective_date', 'desc')
                    ->first();
                
                $costPrice = $latestCost ? $latestCost->cost : 0;

                // 创建新的标准商品
                DB::table('products')->insert([
                    'name' => $series->name . '商品',
                    'code' => 'P' . $series->code,
                    'type' => 'standard',
                    'price' => $series->price ?? floatval($series->code), // 使用price或code作为价格
                    'cost_price' => $costPrice,
                    'category' => 'standard_product',
                    'description' => $series->description ?? ($series->name . '标准商品'),
                    'is_active' => $series->is_active,
                    'sort_order' => 0,
                    'created_at' => $series->created_at,
                    'updated_at' => $series->updated_at,
                ]);
            }
        }
    }

    /**
     * 迁移库存数据
     */
    private function migrateInventoryData(): void
    {
        // 检查unified_inventories表是否存在
        if (Schema::hasTable('unified_inventories')) {
            // 获取所有price_series类型的库存记录
            $inventories = DB::table('unified_inventories')
                ->where('inventory_type', 'price_series')
                ->whereNull('product_id')
                ->get();

            foreach ($inventories as $inventory) {
                // 根据reference_code找到对应的product
                $product = DB::table('products')
                    ->where('code', 'P' . $inventory->reference_code)
                    ->orWhere('price', $inventory->reference_code)
                    ->first();

                if ($product) {
                    // 更新库存记录的product_id
                    DB::table('unified_inventories')
                        ->where('id', $inventory->id)
                        ->update([
                            'product_id' => $product->id,
                            'updated_at' => now(),
                        ]);
                }
            }
        } else {
            // 如果没有unified_inventories表，从price_series数据创建库存记录
            $this->createInventoryFromPriceSeries();
        }
    }

    /**
     * 从price_series数据创建库存记录
     */
    private function createInventoryFromPriceSeries(): void
    {
        if (!Schema::hasTable('price_series')) {
            return;
        }

        $products = DB::table('products')->where('type', 'standard')->get();
        $stores = DB::table('stores')->get();

        foreach ($products as $product) {
            foreach ($stores as $store) {
                // 为每个仓库的每个商品创建库存记录
                DB::table('inventories')->insertOrIgnore([
                    'store_id' => $store->id,
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'alert_quantity' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * 清理旧数据
     */
    private function cleanupOldData(): void
    {
        // 在数据迁移完成后，可以选择删除series_code字段
        // 这里先保留，以防需要回滚
        
        // 备份旧的price_series表（重命名）
        if (Schema::hasTable('price_series')) {
            // Schema::rename('price_series', 'price_series_backup');
        }
        
        // 注释：可以根据需要清理旧的字段和表
        // Schema::table('inventories', function (Blueprint $table) {
        //     $table->dropColumn('series_code');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滚操作：恢复原始结构
        Schema::dropIfExists('blind_bag_deliveries');

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'sale_type')) {
                $table->dropColumn('sale_type');
            }
        });

        Schema::table('inventories', function (Blueprint $table) {
            if (Schema::hasColumn('inventories', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('inventories', 'alert_quantity')) {
                $table->dropColumn('alert_quantity');
            }
            if (Schema::hasColumn('inventories', 'last_stock_in_at')) {
                $table->dropColumn('last_stock_in_at');
            }
            if (Schema::hasColumn('inventories', 'last_stock_out_at')) {
                $table->dropColumn('last_stock_out_at');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('products', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
            if (Schema::hasColumn('products', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('products', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });

        // 恢复price_series表
        // if (Schema::hasTable('price_series_backup')) {
        //     Schema::rename('price_series_backup', 'price_series');
        // }
    }
};
