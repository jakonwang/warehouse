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
            // 添加series_code字段
            $table->string('series_code')->after('stock_in_record_id');
            $table->index('series_code');
            
            // 如果存在price_series_id外键，先删除
            if (Schema::hasColumn('stock_in_details', 'price_series_id')) {
                $table->dropForeign(['price_series_id']);
                $table->dropColumn('price_series_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_in_details', function (Blueprint $table) {
            $table->dropIndex(['series_code']);
            $table->dropColumn('series_code');
            
            // 恢复price_series_id字段
            $table->foreignId('price_series_id')->after('stock_in_record_id')->constrained();
        });
    }
};
