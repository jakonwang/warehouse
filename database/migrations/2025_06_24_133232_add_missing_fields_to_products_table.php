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
            // 添加缺失的字段
            $table->decimal('price', 10, 2)->default(0)->after('cost_price');
            $table->string('type')->default('basic')->after('price');
            $table->integer('stock')->default(0)->after('type');
            $table->integer('alert_stock')->default(0)->after('stock');
            $table->integer('sort_order')->default(0)->after('alert_stock');
            $table->string('image')->nullable()->after('sort_order');
            
            // 删除不需要的外键约束（如果使用code而不是category_id）
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 移除添加的字段
            $table->dropColumn([
                'price',
                'type', 
                'stock',
                'alert_stock',
                'sort_order',
                'image'
            ]);
            
            // 恢复category_id字段
            $table->foreignId('category_id')->constrained()->after('code');
        });
    }
};
