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
        Schema::create('blind_bag_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blind_bag_sale_id')->constrained('blind_bag_sales')->onDelete('cascade')->comment('盲袋销售ID');
            $table->string('price_series_code', 50)->comment('实际发货的价格系列编码');
            $table->integer('quantity')->comment('数量');
            $table->decimal('unit_cost', 10, 2)->comment('单位成本');
            $table->decimal('total_cost', 12, 2)->comment('总成本');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            
            // 外键约束
            $table->foreign('price_series_code')->references('code')->on('price_series')->onDelete('cascade');
            
            // 索引
            $table->index('blind_bag_sale_id');
            $table->index('price_series_code');
            $table->index(['blind_bag_sale_id', 'price_series_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blind_bag_details');
    }
};
