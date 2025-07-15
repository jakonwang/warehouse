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
        Schema::table('stock_in_records', function (Blueprint $table) {
            // 添加supplier字段，允许为空，放在store_id之后
            $table->string('supplier')->nullable()->after('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_in_records', function (Blueprint $table) {
            // 删除supplier字段
            $table->dropColumn('supplier');
        });
    }
};
