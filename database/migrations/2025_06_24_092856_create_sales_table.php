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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('remark')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->decimal('total_profit', 10, 2)->default(0);
            $table->decimal('profit_rate', 5, 2)->default(0);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
