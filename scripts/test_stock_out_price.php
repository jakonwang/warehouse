<?php

/**
 * 测试出库创建页面的商品单价显示功能
 * 使用方法: php scripts/test_stock_out_price.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Store;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

echo "=== 测试出库创建页面的商品单价显示功能 ===\n\n";

try {
    // 1. 检查商品数据
    echo "1. 检查商品数据:\n";
    $products = Product::where('type', 'standard')->take(5)->get();
    foreach ($products as $product) {
        echo "   - {$product->name} (编码: {$product->code}): 售价¥{$product->price}, 成本¥{$product->cost_price}\n";
    }
    echo "\n";

    // 2. 检查仓库数据
    echo "2. 检查仓库数据:\n";
    $stores = Store::where('is_active', true)->take(3)->get();
    foreach ($stores as $store) {
        echo "   - {$store->name} (ID: {$store->id})\n";
    }
    echo "\n";

    // 3. 检查库存数据
    echo "3. 检查库存数据:\n";
    $inventories = Inventory::with(['product', 'store'])->take(5)->get();
    foreach ($inventories as $inventory) {
        echo "   - {$inventory->product->name} 在 {$inventory->store->name}: {$inventory->quantity}个\n";
    }
    echo "\n";

    // 4. 测试API接口
    echo "4. 测试API接口:\n";
    
    // 模拟用户认证
    $user = \App\Models\User::first();
    if ($user) {
        auth()->login($user);
        echo "   - 用户认证成功: {$user->real_name}\n";
        
        // 测试第一个仓库的商品获取
        $firstStore = $stores->first();
        if ($firstStore) {
            echo "   - 测试仓库: {$firstStore->name}\n";
            
            // 检查用户权限
            if ($user->canAccessStore($firstStore->id)) {
                echo "   - 用户有权限访问该仓库\n";
                
                // 获取仓库商品
                $storeProducts = $firstStore->availableStandardProducts()->get();
                echo "   - 仓库商品数量: {$storeProducts->count()}\n";
                
                foreach ($storeProducts->take(3) as $product) {
                    echo "     * {$product->name} (编码: {$product->code}): 售价¥{$product->price}\n";
                }
                
            } else {
                echo "   - 用户无权限访问该仓库\n";
            }
        }
        
        auth()->logout();
    } else {
        echo "   - 无法找到用户进行测试\n";
    }
    
    echo "\n";

    // 5. 检查视图文件
    echo "5. 检查视图文件:\n";
    $viewPath = resource_path('views/stock-out/create.blade.php');
    if (file_exists($viewPath)) {
        $viewContent = file_get_contents($viewPath);
        
        // 检查关键字段
        $checks = [
            'products[0][id]' => '商品ID字段',
            'products[0][quantity]' => '数量字段',
            'products[0][unit_price]' => '单价字段',
            'updateProductPrice' => '价格更新函数',
            'data-price' => '价格数据属性'
        ];
        
        foreach ($checks as $pattern => $description) {
            if (strpos($viewContent, $pattern) !== false) {
                echo "   ✅ {$description}: 已修复\n";
            } else {
                echo "   ❌ {$description}: 未找到\n";
            }
        }
        
    } else {
        echo "   ❌ 视图文件不存在\n";
    }
    
    echo "\n";

    // 6. 检查控制器
    echo "6. 检查控制器:\n";
    $controllerPath = app_path('Http/Controllers/StockOutController.php');
    if (file_exists($controllerPath)) {
        $controllerContent = file_get_contents($controllerPath);
        
        $checks = [
            'getStoreProducts' => '获取仓库商品方法',
            'products.*.unit_price' => '单价验证规则',
            'unit_price' => '单价字段处理'
        ];
        
        foreach ($checks as $pattern => $description) {
            if (strpos($controllerContent, $pattern) !== false) {
                echo "   ✅ {$description}: 已实现\n";
            } else {
                echo "   ❌ {$description}: 未实现\n";
            }
        }
        
    } else {
        echo "   ❌ 控制器文件不存在\n";
    }
    
    echo "\n";

    // 7. 修复建议
    echo "7. 修复建议:\n";
    echo "   - 确保商品选择器的data-price属性正确设置\n";
    echo "   - 验证updateProductPrice函数能正确更新单价字段\n";
    echo "   - 检查API返回的商品数据包含价格信息\n";
    echo "   - 确保表单字段名称与控制器验证规则一致\n";
    
    echo "\n";

} catch (\Exception $e) {
    echo "测试过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== 测试完成 ===\n"; 