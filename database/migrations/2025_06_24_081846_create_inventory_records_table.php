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
        Schema::create('inventory_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->onDelete('cascade');
            $table->integer('quantity')->comment('数量变化，正数为增加，负数为减少');
            $table->decimal('unit_price', 10, 2)->default(0)->comment('单价');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('总金额');
            $table->enum('type', ['in', 'out', 'adjust', 'check'])->comment('记录类型：入库、出库、调整、盘点');
            $table->string('reference_type')->nullable()->comment('关联类型');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('关联ID');
            $table->text('note')->nullable()->comment('备注');
            $table->timestamps();
            
            $table->index(['inventory_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_records');
    }
};
