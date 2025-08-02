<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// 启动 Laravel 应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "开始修复分类表中的空 slug 值...\n";

try {
    // 查找所有空的 slug
    $categoriesWithEmptySlug = DB::table('categories')
        ->where(function($query) {
            $query->where('slug', '')
                  ->orWhereNull('slug');
        })
        ->get();

    echo "找到 " . $categoriesWithEmptySlug->count() . " 个空的 slug\n";

    if ($categoriesWithEmptySlug->count() > 0) {
        foreach ($categoriesWithEmptySlug as $category) {
            $newSlug = Str::slug($category->name);
            
            // 如果生成的 slug 为空，使用 ID 作为 slug
            if (empty($newSlug)) {
                $newSlug = 'category-' . $category->id;
            }
            
            // 检查 slug 是否已存在，如果存在则添加数字后缀
            $baseSlug = $newSlug;
            $counter = 1;
            while (DB::table('categories')->where('slug', $newSlug)->where('id', '!=', $category->id)->exists()) {
                $newSlug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            // 更新 slug
            DB::table('categories')
                ->where('id', $category->id)
                ->update(['slug' => $newSlug]);
                
            echo "更新分类 ID {$category->id} ({$category->name}) 的 slug 为: {$newSlug}\n";
        }
        
        echo "所有空的 slug 已修复完成！\n";
    } else {
        echo "没有找到空的 slug，无需修复。\n";
    }
    
    // 验证修复结果
    $remainingEmptySlugs = DB::table('categories')
        ->where(function($query) {
            $query->where('slug', '')
                  ->orWhereNull('slug');
        })
        ->count();
        
    echo "修复后剩余的空 slug 数量: {$remainingEmptySlugs}\n";
    
} catch (Exception $e) {
    echo "修复过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行号: " . $e->getLine() . "\n";
} 