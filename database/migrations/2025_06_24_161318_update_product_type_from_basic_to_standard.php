<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 更新现有商品的类型从 basic 改为 standard
        DB::table('products')
            ->where('type', 'basic')
            ->update(['type' => 'standard']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滚：将 standard 改回 basic
        DB::table('products')
            ->where('type', 'standard')
            ->update(['type' => 'basic']);
    }
};
