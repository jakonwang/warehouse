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
        Schema::create('blind_bag_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade')->comment('销售记录ID');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->comment('盲袋商品ID');
            $table->integer('quantity')->comment('盲袋数量');
            $table->decimal('unit_price', 10, 2)->comment('盲袋单价');
            $table->decimal('total_amount', 12, 2)->comment('总金额');
            $table->decimal('total_cost', 12, 2)->default(0)->comment('实际总成本');
            $table->decimal('profit', 12, 2)->default(0)->comment('利润');
            $table->decimal('profit_rate', 5, 2)->default(0)->comment('利润率');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            
            // 索引
            $table->index('sale_id');
            $table->index('product_id');
            $table->index(['sale_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blind_bag_sales');
    }
};
