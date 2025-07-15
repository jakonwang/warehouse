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
        Schema::create('unified_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade')->comment('仓库ID');
            $table->enum('inventory_type', ['product_variant', 'price_series'])->comment('库存类型');
            $table->bigInteger('reference_id')->comment('关联ID (product_variant_id 或 price_series.id)');
            $table->string('reference_code', 100)->comment('关联编码 (variant_sku 或 series_code)');
            $table->integer('quantity')->default(0)->comment('库存数量');
            $table->integer('reserved_quantity')->default(0)->comment('预留数量');
            $table->integer('available_quantity')->storedAs('quantity - reserved_quantity')->comment('可用数量');
            $table->integer('min_quantity')->default(10)->comment('最小库存');
            $table->integer('max_quantity')->default(1000)->comment('最大库存');
            $table->timestamp('last_check_at')->nullable()->comment('最后盘点时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            
            // 唯一约束：每个仓库中每种类型的每个商品只能有一条库存记录
            $table->unique(['store_id', 'inventory_type', 'reference_id'], 'uk_store_type_ref');
            
            // 索引
            $table->index(['inventory_type', 'reference_id']);
            $table->index(['store_id', 'inventory_type']);
            $table->index('reference_code');
            $table->index(['store_id', 'quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unified_inventories');
    }
};
