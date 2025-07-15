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
        Schema::table('unified_inventories', function (Blueprint $table) {
            // 移除计算列
            $table->dropColumn('available_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unified_inventories', function (Blueprint $table) {
            // 恢复计算列
            $table->integer('available_quantity')->storedAs('quantity - reserved_quantity')->comment('可用数量');
        });
    }
};
