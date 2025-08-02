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
        // 首先修复所有空的 slug 值
        $categoriesWithEmptySlug = DB::table('categories')
            ->where(function($query) {
                $query->where('slug', '')
                      ->orWhereNull('slug');
            })
            ->get();

        foreach ($categoriesWithEmptySlug as $category) {
            $baseSlug = \Illuminate\Support\Str::slug($category->name);
            
            // 如果生成的 slug 为空，使用 ID 作为 slug
            if (empty($baseSlug)) {
                $baseSlug = 'category-' . $category->id;
            }
            
            // 检查 slug 是否已存在，如果存在则添加数字后缀
            $slug = $baseSlug;
            $counter = 1;
            while (DB::table('categories')->where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            // 更新 slug
            DB::table('categories')
                ->where('id', $category->id)
                ->update(['slug' => $slug]);
        }

        // 然后修改字段约束，确保不允许空值
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->change();
        });
    }
};
