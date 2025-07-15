<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('price_series', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('price_series_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_series_id')->constrained();
            $table->decimal('cost', 10, 2);
            $table->date('effective_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_series_costs');
        Schema::dropIfExists('price_series');
    }
}; 