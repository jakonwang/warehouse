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
        Schema::create('store_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no')->unique()->comment('调拨单号');
            $table->unsignedBigInteger('source_store_id')->comment('源仓库ID');
            $table->unsignedBigInteger('target_store_id')->comment('目标仓库ID');
            $table->unsignedBigInteger('product_id')->comment('商品ID');
            $table->integer('quantity')->comment('调拨数量');
            $table->decimal('unit_cost', 10, 2)->comment('单位成本');
            $table->decimal('total_cost', 10, 2)->comment('总成本');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_transit', 'completed', 'cancelled'])
                  ->default('pending')->comment('调拨状态');
            $table->text('reason')->nullable()->comment('调拨原因');
            $table->text('remark')->nullable()->comment('备注');
            $table->unsignedBigInteger('requested_by')->comment('申请人ID');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('审批人ID');
            $table->timestamp('approved_at')->nullable()->comment('审批时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->timestamps();
            
            $table->foreign('source_store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('target_store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['source_store_id', 'status']);
            $table->index(['target_store_id', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_transfers');
    }
}; 