<?php

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== JavaScript修复验证 ===\n\n";

// 模拟用户登录
$user = \App\Models\User::first();
auth()->login($user);

// 获取视图数据
$stores = $user->getAccessibleStores()->where('is_active', true)->values();
$storeId = 1;
$products = collect();
$currentStore = $stores->where('id', $storeId)->first();
if ($currentStore) {
    $products = $currentStore->availableStandardProducts()->get();
}

// 渲染视图并检查HTML
ob_start();
extract(compact('stores', 'products', 'storeId'));
include __DIR__ . '/../resources/views/mobile/returns/index.blade.php';
$html = ob_get_clean();

// 检查是否还有x-model指令
if (strpos($html, 'x-model=') !== false) {
    echo "❌ 发现x-model指令，需要修复\n";
    preg_match_all('/x-model="[^"]*"/', $html, $matches);
    foreach ($matches[0] as $match) {
        echo "  - $match\n";
    }
} else {
    echo "✅ 未发现x-model指令\n";
}

// 检查是否还有@input指令
if (strpos($html, '@input=') !== false) {
    echo "❌ 发现@input指令，需要修复\n";
    preg_match_all('/@input="[^"]*"/', $html, $matches);
    foreach ($matches[0] as $match) {
        echo "  - $match\n";
    }
} else {
    echo "✅ 未发现@input指令\n";
}

// 检查是否有product-quantity类
if (strpos($html, 'product-quantity') !== false) {
    echo "✅ 发现product-quantity类，事件监听器已配置\n";
} else {
    echo "❌ 未发现product-quantity类\n";
}

// 检查是否有data-product-id属性
if (strpos($html, 'data-product-id') !== false) {
    echo "✅ 发现data-product-id属性，数据绑定已配置\n";
} else {
    echo "❌ 未发现data-product-id属性\n";
}

echo "\n=== 验证完成 ===\n"; 