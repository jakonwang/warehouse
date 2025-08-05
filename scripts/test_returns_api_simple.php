<?php

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== 简单API测试 ===\n\n";

// 模拟用户登录
$user = \App\Models\User::first();
auth()->login($user);

// 测试API方法
$controller = new \App\Http\Controllers\ReturnController();
$request = new \Illuminate\Http\Request();
$request->merge(['store_id' => 1]);

try {
    $response = $controller->getStoreProducts($request);
    $content = $response->getContent();
    $data = json_decode($content, true);
    
    echo "API调用成功！\n";
    echo "响应状态: " . $response->getStatusCode() . "\n";
    echo "响应内容: " . $content . "\n";
    
    if (isset($data['success']) && $data['success']) {
        echo "商品数量: " . count($data['products']) . "\n";
        foreach ($data['products'] as $product) {
            echo "- {$product['name']} (价格: ¥{$product['price']})\n";
        }
    }
} catch (Exception $e) {
    echo "API调用失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== 测试完成 ===\n"; 