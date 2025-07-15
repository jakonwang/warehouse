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
        Schema::table('stock_in_details', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->after('quantity')->comment('入库单价');
            $table->decimal('total_amount', 10, 2)->after('unit_price')->comment('入库总金额');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_in_details', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'total_amount']);
        });
    }
};
