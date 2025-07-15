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
        Schema::table('return_details', function (Blueprint $table) {
            // 添加product_id字段
            $table->unsignedBigInteger('product_id')->nullable()->after('return_record_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // 迁移现有数据（如果有的话）
        $this->migrateExistingData();

        // 删除旧的series_code字段和外键约束
        Schema::table('return_details', function (Blueprint $table) {
            $table->dropForeign(['series_code']);
            $table->dropColumn('series_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_details', function (Blueprint $table) {
            // 恢复series_code字段
            $table->string('series_code')->after('return_record_id');
            $table->foreign('series_code')->references('code')->on('price_series')->onDelete('cascade');
        });

        // 删除product_id字段
        Schema::table('return_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }

    /**
     * 迁移现有数据
     */
    private function migrateExistingData(): void
    {
        // 检查是否有现有数据需要迁移
        $existingDetails = DB::table('return_details')->whereNotNull('series_code')->get();
        
        foreach ($existingDetails as $detail) {
            // 尝试根据series_code找到对应的product
            $product = DB::table('products')
                ->where('code', 'P' . $detail->series_code)
                ->orWhere('name', 'like', '%' . $detail->series_code . '%')
                ->first();
            
            if ($product) {
                DB::table('return_details')
                    ->where('id', $detail->id)
                    ->update(['product_id' => $product->id]);
            }
        }
    }
};
