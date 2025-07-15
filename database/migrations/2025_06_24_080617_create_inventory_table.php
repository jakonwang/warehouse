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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('series_code');
            $table->integer('quantity')->default(0);
            $table->integer('min_quantity')->default(50);
            $table->integer('max_quantity')->default(1000);
            $table->datetime('last_check_date')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            
            $table->index('series_code');
            $table->unique(['series_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
