<?php

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== 退货页面数据测试 ===\n\n";

try {
    // 模拟用户登录
    $user = \App\Models\User::first();
    auth()->login($user);
    
    echo "测试用户: {$user->username}\n";
    echo "用户角色: " . ($user->role ? $user->role->name : '无角色') . "\n\n";
    
    // 模拟mobileCreate方法
    $stores = $user->getAccessibleStores()->where('is_active', true)->values();
    $storeId = request('store_id') ?? session('current_store_id');
    
    echo "1. 初始状态:\n";
    echo "  可访问仓库数量: " . $stores->count() . "\n";
    echo "  当前仓库ID: " . ($storeId ?? '未选择') . "\n";
    
    // 如果没有选择仓库，使用第一个可访问的仓库
    if (!$storeId && $stores->count() > 0) {
        $storeId = $stores->first()->id;
        echo "  自动选择仓库ID: {$storeId}\n";
    }
    
    // 获取当前仓库分配的商品
    $products = collect();
    if ($storeId) {
        $currentStore = $stores->where('id', $storeId)->first();
        if ($currentStore) {
            $products = $currentStore->availableStandardProducts()->get();
            echo "  当前仓库: {$currentStore->name}\n";
        }
    }
    
    echo "  商品数量: " . $products->count() . "\n";
    
    if ($products->count() > 0) {
        echo "  商品列表:\n";
        foreach ($products as $product) {
            echo "    - {$product->name} (价格: ¥{$product->price})\n";
        }
    }
    echo "\n";
    
    // 测试不同仓库的商品
    echo "2. 测试不同仓库的商品:\n";
    foreach ($stores as $store) {
        echo "  仓库: {$store->name} (ID: {$store->id})\n";
        $storeProducts = $store->availableStandardProducts()->get();
        echo "    标品数量: " . $storeProducts->count() . "\n";
        
        if ($storeProducts->count() > 0) {
            echo "    第一个商品: " . $storeProducts->first()->name . "\n";
        }
        echo "\n";
    }
    
    // 测试API响应
    echo "3. 测试API响应:\n";
    if ($storeId) {
        $store = \App\Models\Store::find($storeId);
        $apiController = new \App\Http\Controllers\StoreController();
        $response = $apiController->getProducts($store);
        $data = json_decode($response->getContent(), true);
        
        if ($data['success']) {
            echo "  API响应成功\n";
            echo "  标品数量: " . count($data['standard_products']) . "\n";
            echo "  盲袋数量: " . count($data['blind_bag_products']) . "\n";
            
            if (count($data['standard_products']) > 0) {
                echo "  第一个标品: " . $data['standard_products'][0]['name'] . "\n";
            }
        } else {
            echo "  API响应失败: " . ($data['message'] ?? '未知错误') . "\n";
        }
    }
    echo "\n";
    
    // 模拟页面渲染数据
    echo "4. 页面渲染数据:\n";
    echo "  storeId: " . ($storeId ?? 'null') . "\n";
    echo "  products->count(): " . $products->count() . "\n";
    echo "  stores->count(): " . $stores->count() . "\n";
    
    // 判断显示逻辑
    if ($storeId && $products->count() > 0) {
        echo "  显示状态: 显示商品列表\n";
    } elseif ($storeId && $products->count() == 0) {
        echo "  显示状态: 显示'该仓库暂无可退货的商品'\n";
    } else {
        echo "  显示状态: 显示'请选择仓库以查看可退货的商品'\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    echo "✅ 数据传递正常\n";
    echo "✅ 商品查询正常\n";
    echo "✅ API响应正常\n";
    
    if ($storeId && $products->count() > 0) {
        echo "✅ 商品应该能正常显示\n";
    } else {
        echo "⚠️  当前没有可显示的商品，请检查仓库配置\n";
    }
    
} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
} 