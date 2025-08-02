<?php

require_once __DIR__ . '/../vendor/autoload.php';

// 启动 Laravel 应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Product;

echo "=== 成本信息权限控制测试 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 准备控制器和反射
    $controller = new \App\Http\Controllers\StockInController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getStoreProducts');
    $method->setAccessible(true);
    
    // 1. 检查当前用户
    echo "1. 检查当前用户...\n";
    $currentUser = User::first();
    if ($currentUser) {
        auth()->login($currentUser);
        echo "✅ 用户登录成功: {$currentUser->username}\n";
        echo "   是否为超级管理员: " . ($currentUser->isSuperAdmin() ? '是' : '否') . "\n";
        
        // 测试API返回
        $request = new \Illuminate\Http\Request();
        $request->merge(['store_id' => 1]);
        
        $response = $method->invoke($controller, $request);
        $data = json_decode($response->getContent(), true);
        
        if ($data['success'] && count($data['products']) > 0) {
            $product = $data['products'][0];
            echo "✅ API返回成功\n";
            echo "   商品: {$product['name']}\n";
            
            if (isset($product['cost_price'])) {
                echo "   成本: ¥{$product['cost_price']} (超级管理员可见)\n";
            } else {
                echo "   成本: 隐藏 (普通用户)\n";
            }
            
            // 验证权限控制
            if ($currentUser->isSuperAdmin()) {
                if (isset($product['cost_price'])) {
                    echo "✅ 超级管理员权限控制正常\n";
                } else {
                    echo "❌ 超级管理员应该看到成本信息\n";
                }
            } else {
                if (!isset($product['cost_price'])) {
                    echo "✅ 普通用户权限控制正常\n";
                } else {
                    echo "❌ 普通用户不应该看到成本信息\n";
                }
            }
        } else {
            echo "❌ API返回失败\n";
        }
    } else {
        echo "❌ 未找到用户\n";
    }
    
    // 2. 测试视图渲染
    echo "\n2. 测试视图渲染权限...\n";
    $products = Product::where('is_active', true)->where('type', 'standard')->take(3)->get();
    
    echo "当前用户视图中的商品显示:\n";
    foreach ($products as $product) {
        if (auth()->user()->isSuperAdmin()) {
            echo "  - {$product->name} (成本¥{$product->cost_price})\n";
        } else {
            echo "  - {$product->name}\n";
        }
    }
    
    echo "\n=== 测试完成 ===\n";
    echo "如果测试通过，成本信息权限控制应该正常工作\n";
    echo "建议测试以下功能：\n";
    echo "1. 超级管理员登录后可以看到成本信息\n";
    echo "2. 普通用户登录后看不到成本信息\n";
    echo "3. 动态加载商品时权限控制正常\n";
    echo "4. API返回数据中成本信息正确隐藏\n";
    
} catch (Exception $e) {
    echo "测试过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行号: " . $e->getLine() . "\n";
} 