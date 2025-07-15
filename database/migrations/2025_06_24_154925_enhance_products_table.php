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
        Schema::table('products', function (Blueprint $table) {
            // 添加商品分类字段
            $table->string('category', 100)->nullable()->after('type')->comment('商品分类');
            
            // 修改cost_price允许为空（盲袋商品可以不设置固定成本）
            $table->decimal('cost_price', 10, 2)->nullable()->change();
            
            // 添加索引优化查询
            $table->index('type');
            $table->index('category');
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 删除添加的字段和索引
            $table->dropIndex(['products_type_index']);
            $table->dropIndex(['products_category_index']);
            $table->dropIndex(['products_type_is_active_index']);
            $table->dropColumn('category');
            
            // 恢复cost_price为非空
            $table->decimal('cost_price', 10, 2)->nullable(false)->change();
        });
    }
};
