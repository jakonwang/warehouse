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
        Schema::table('inventories', function (Blueprint $table) {
            // 添加series_code字段（如果不存在）
            if (!Schema::hasColumn('inventories', 'series_code')) {
                $table->string('series_code')->nullable()->after('store_id');
                $table->index('series_code');
            }
            
            // 将product_id设为可空，因为我们将使用series_code
            if (Schema::hasColumn('inventories', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            if (Schema::hasColumn('inventories', 'series_code')) {
                $table->dropIndex(['series_code']);
                $table->dropColumn('series_code');
            }
            
            // 恢复product_id为非空
            if (Schema::hasColumn('inventories', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable(false)->change();
            }
        });
    }
};
