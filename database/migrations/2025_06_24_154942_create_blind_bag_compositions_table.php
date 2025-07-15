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
        Schema::create('blind_bag_compositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->comment('盲袋商品ID');
            $table->string('price_series_code', 50)->comment('价格系列编码');
            $table->decimal('probability', 5, 2)->default(0)->comment('出现概率(%)');
            $table->integer('min_quantity')->default(1)->comment('最小数量');
            $table->integer('max_quantity')->default(1)->comment('最大数量');
            $table->text('description')->nullable()->comment('说明');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->timestamps();
            
            // 外键约束
            $table->foreign('price_series_code')->references('code')->on('price_series')->onDelete('cascade');
            
            // 唯一约束
            $table->unique(['product_id', 'price_series_code'], 'uk_product_series');
            
            // 索引
            $table->index('product_id');
            $table->index('price_series_code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blind_bag_compositions');
    }
};
