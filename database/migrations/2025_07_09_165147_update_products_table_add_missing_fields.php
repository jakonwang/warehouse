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
            // 如果字段不存在才添加
            if (!Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable()->after('code');
            }
            if (!Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('cost_price');
            }
            if (!Schema::hasColumn('products', 'type')) {
                $table->enum('type', ['standard', 'blind_bag'])->default('standard')->after('price');
            }
            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category')->nullable()->after('type');
            }
            if (!Schema::hasColumn('products', 'stock')) {
                $table->integer('stock')->default(0)->after('category');
            }
            if (!Schema::hasColumn('products', 'alert_stock')) {
                $table->integer('alert_stock')->default(10)->after('stock');
            }
            if (!Schema::hasColumn('products', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('alert_stock');
            }
            
            // 修改category_id为可空，因为我们现在使用字符串category字段
            if (Schema::hasColumn('products', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'image',
                'price', 
                'type',
                'category',
                'stock',
                'alert_stock',
                'sort_order'
            ]);
            
            // 恢复category_id为必填
            if (Schema::hasColumn('products', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable(false)->change();
            }
        });
    }
};
