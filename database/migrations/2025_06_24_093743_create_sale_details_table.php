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
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('product_code')->nullable(); // 产品代码，用于统计
            $table->integer('quantity');
            $table->decimal('price', 8, 2);
            $table->decimal('cost', 8, 2)->nullable();
            $table->decimal('cost_price', 8, 2)->nullable(); // 成本价格，用于统计
            $table->decimal('total', 10, 2);
            $table->decimal('profit', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
