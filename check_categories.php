<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;

echo "检查分类数据:\n";
echo "总分类数: " . Category::count() . "\n";
echo "活跃分类数: " . Category::where('is_active', true)->count() . "\n";

echo "\n所有分类:\n";
Category::all()->each(function($cat) {
    echo "- ID: {$cat->id}, 名称: {$cat->name}, 活跃: " . ($cat->is_active ? '是' : '否') . "\n";
});

echo "\n活跃分类:\n";
Category::where('is_active', true)->get()->each(function($cat) {
    echo "- {$cat->name}\n";
}); 