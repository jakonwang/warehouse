<?php

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== 退货路由重定向测试 ===\n\n";

// 模拟用户登录
$user = \App\Models\User::first();
auth()->login($user);

// 测试mobileCreate方法
$controller = new \App\Http\Controllers\ReturnController();

try {
    echo "1. 测试mobileCreate方法重定向...\n";
    $response = $controller->mobileCreate();
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "✅ mobileCreate方法正确重定向\n";
        echo "   重定向URL: " . $response->getTargetUrl() . "\n";
        echo "   状态码: " . $response->getStatusCode() . "\n";
    } else {
        echo "❌ mobileCreate方法未重定向\n";
    }
} catch (Exception $e) {
    echo "❌ mobileCreate方法测试失败: " . $e->getMessage() . "\n";
}

// 测试路由
echo "\n2. 测试路由配置...\n";
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$returnRoutes = collect($routes)->filter(function($route) {
    return str_contains($route->uri(), 'mobile/returns');
});

echo "找到 " . $returnRoutes->count() . " 个移动端退货相关路由:\n";
foreach ($returnRoutes as $route) {
    $methods = implode('|', $route->methods());
    $uri = $route->uri();
    $action = $route->getActionName();
    echo "  $methods $uri -> $action\n";
}

// 测试路由名称
echo "\n3. 测试路由名称...\n";
$routeNames = [
    'mobile.returns.index',
    'mobile.returns.create',
    'mobile.returns.debug',
    'mobile.returns.test',
    'mobile.returns.simple'
];

foreach ($routeNames as $name) {
    try {
        $url = route($name);
        echo "✅ $name -> $url\n";
    } catch (Exception $e) {
        echo "❌ $name -> 路由不存在\n";
    }
}

echo "\n=== 测试完成 ===\n"; 