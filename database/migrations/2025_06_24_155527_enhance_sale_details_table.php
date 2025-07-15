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
        Schema::table('sale_details', function (Blueprint $table) {
            // 添加product_variant_id字段 (标品销售关联变体)
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->onDelete('cascade')->comment('商品变体ID');
            
            // 添加新字段
            $table->string('sku', 100)->nullable()->after('product_variant_id')->comment('商品SKU');
            $table->text('remark')->nullable()->after('profit')->comment('备注');
            
            // 修改现有字段精度
            $table->decimal('price', 10, 2)->change()->comment('单价');
            $table->decimal('cost', 10, 2)->nullable()->change()->comment('单位成本');
            $table->decimal('total', 12, 2)->change()->comment('总金额');
            $table->decimal('profit', 12, 2)->nullable()->change()->comment('利润');
            
            // 添加索引
            $table->index('product_variant_id');
            $table->index('sku');
            $table->index(['sale_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            // 删除索引
            $table->dropIndex(['sale_details_product_variant_id_index']);
            $table->dropIndex(['sale_details_sku_index']);
            $table->dropIndex(['sale_details_sale_id_product_variant_id_index']);
            
            // 删除外键约束
            $table->dropForeign(['product_variant_id']);
            
            // 删除字段
            $table->dropColumn(['product_variant_id', 'sku', 'remark']);
            
            // 恢复字段的原始属性
            $table->decimal('price', 8, 2)->change();
            $table->decimal('cost', 8, 2)->nullable()->change();
            $table->decimal('total', 10, 2)->change();
            $table->decimal('profit', 10, 2)->nullable()->change();
        });
    }
};
