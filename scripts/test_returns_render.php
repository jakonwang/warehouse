<?php

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== 退货页面渲染测试 ===\n\n";

try {
    // 模拟用户登录
    $user = \App\Models\User::first();
    auth()->login($user);
    
    echo "测试用户: {$user->username}\n";
    echo "用户角色: " . ($user->role ? $user->role->name : '无角色') . "\n\n";
    
    // 模拟mobileCreate方法
    $stores = $user->getAccessibleStores()->where('is_active', true)->values();
    $storeId = request('store_id') ?? session('current_store_id');
    
    // 如果没有选择仓库，使用第一个可访问的仓库
    if (!$storeId && $stores->count() > 0) {
        $storeId = $stores->first()->id;
    }
    
    // 获取当前仓库分配的商品
    $products = collect();
    if ($storeId) {
        $currentStore = $stores->where('id', $storeId)->first();
        if ($currentStore) {
            $products = $currentStore->availableStandardProducts()->get();
        }
    }
    
    echo "渲染数据:\n";
    echo "  storeId: " . ($storeId ?? 'null') . "\n";
    echo "  products->count(): " . $products->count() . "\n";
    echo "  stores->count(): " . $stores->count() . "\n";
    echo "  storeId存在: " . ($storeId ? 'true' : 'false') . "\n";
    echo "  products->count() > 0: " . ($products->count() > 0 ? 'true' : 'false') . "\n";
    echo "  显示条件: " . ($storeId && $products->count() > 0 ? 'true' : 'false') . "\n\n";
    
    // 模拟Blade模板渲染逻辑
    echo "Blade模板渲染逻辑:\n";
    
    if ($storeId && $products->count() > 0) {
        echo "  显示: 商品列表\n";
        echo "  商品数量: " . $products->count() . "\n";
        foreach ($products as $product) {
            echo "    - {$product->name} (价格: ¥{$product->price})\n";
        }
    } elseif ($storeId && $products->count() == 0) {
        echo "  显示: 该仓库暂无可退货的商品\n";
        echo "  仓库ID: {$storeId}\n";
    } else {
        echo "  显示: 请选择仓库以查看可退货的商品\n";
        echo "  当前仓库ID: " . ($storeId ?? '未选择') . "\n";
        echo "  商品数量: {$products->count()}\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    
    if ($storeId && $products->count() > 0) {
        echo "✅ 商品应该正常显示\n";
        echo "✅ 渲染逻辑正确\n";
    } else {
        echo "⚠️  当前没有可显示的商品\n";
        echo "⚠️  请检查仓库配置和商品分配\n";
    }
    
} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
} 