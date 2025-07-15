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
        Schema::create('return_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_record_id')->constrained('return_records')->onDelete('cascade');
            $table->string('series_code');
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();
            
            $table->foreign('series_code')->references('code')->on('price_series')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_details');
    }
};
