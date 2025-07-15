<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('price_series_id')->constrained();
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('stock_in_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->text('remark')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_in_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_in_record_id')->constrained('stock_in_records');
            $table->foreignId('price_series_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();
        });

        Schema::create('stock_out_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('customer')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->text('remark')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_out_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_out_record_id')->constrained('stock_out_records');
            $table->foreignId('price_series_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();
        });

        Schema::create('inventory_check_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('status')->default('pending')->comment('状态：pending=待确认，confirmed=已确认');
            $table->text('remark')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_check_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_check_record_id')->constrained('inventory_check_records');
            $table->foreignId('price_series_id')->constrained();
            $table->integer('system_quantity');
            $table->integer('actual_quantity');
            $table->integer('difference');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_check_details');
        Schema::dropIfExists('inventory_check_records');
        Schema::dropIfExists('stock_out_details');
        Schema::dropIfExists('stock_out_records');
        Schema::dropIfExists('stock_in_details');
        Schema::dropIfExists('stock_in_records');
        Schema::dropIfExists('inventories');
    }
}; 