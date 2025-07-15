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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->comment('商品ID');
            $table->string('sku', 100)->unique()->comment('SKU编码');
            $table->string('variant_name')->nullable()->comment('变体名称(如:红色、蓝色、L码)');
            $table->decimal('price', 10, 2)->comment('售价');
            $table->decimal('cost_price', 10, 2)->comment('成本价');
            $table->integer('stock')->default(0)->comment('库存数量');
            $table->integer('alert_stock')->default(10)->comment('警戒库存');
            $table->boolean('is_default')->default(false)->comment('是否默认变体');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->string('image')->nullable()->comment('变体图片');
            $table->json('attributes')->nullable()->comment('变体属性(颜色、尺寸等)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            
            // 索引
            $table->index('product_id');
            $table->index('status');
            $table->index(['product_id', 'status']);
            $table->index(['product_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
