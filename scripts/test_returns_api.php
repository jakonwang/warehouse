<?php

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== 退货API测试 ===\n\n";

// 测试1: 检查商品数据
echo "1. 检查商品数据:\n";
$products = \App\Models\Product::where('type', 'standard')->where('is_active', true)->get();
echo "   - 标品商品总数: " . $products->count() . "\n";
foreach ($products as $product) {
    echo "   - {$product->name} (ID: {$product->id})\n";
}

// 测试2: 检查仓库数据
echo "\n2. 检查仓库数据:\n";
$stores = \App\Models\Store::where('is_active', true)->get();
echo "   - 活跃仓库总数: " . $stores->count() . "\n";
foreach ($stores as $store) {
    $productCount = $store->availableStandardProducts()->count();
    echo "   - {$store->name} (ID: {$store->id}) - 商品数: {$productCount}\n";
}

// 测试3: 检查StoreProduct关联
echo "\n3. 检查仓库商品关联:\n";
$storeProducts = \App\Models\StoreProduct::with(['store', 'product'])->get();
echo "   - 仓库商品关联总数: " . $storeProducts->count() . "\n";
foreach ($storeProducts as $sp) {
    echo "   - {$sp->store->name} -> {$sp->product->name} (活跃: " . ($sp->is_active ? '是' : '否') . ")\n";
}

// 测试4: 模拟API调用
echo "\n4. 模拟API调用:\n";
foreach ($stores as $store) {
    echo "   仓库 {$store->name} (ID: {$store->id}):\n";
    $products = $store->availableStandardProducts()->get(['id', 'name', 'code', 'price', 'cost_price']);
    if ($products->count() > 0) {
        foreach ($products as $product) {
            echo "     - {$product->name} (价格: ¥{$product->price})\n";
        }
    } else {
        echo "     - 无商品\n";
    }
    echo "\n";
}

echo "=== 测试完成 ===\n"; 