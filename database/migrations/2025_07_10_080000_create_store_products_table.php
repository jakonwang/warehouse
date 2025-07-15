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
        Schema::create('store_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade')->comment('仓库ID');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->comment('商品ID');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            
            // 唯一约束：每个仓库中每个商品只能有一条分配记录
            $table->unique(['store_id', 'product_id'], 'uk_store_product');
            
            // 索引
            $table->index(['store_id', 'is_active']);
            $table->index(['product_id', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_products');
    }
}; 