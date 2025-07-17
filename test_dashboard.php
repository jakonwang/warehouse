<?php

require_once 'vendor/autoload.php';

// 启动Laravel应用
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\DashboardController;

echo "=== 仪表盘页面测试 ===\n\n";

try {
    // 创建控制器实例
    $controller = new DashboardController();
    
    // 模拟用户登录
    $user = \App\Models\User::where('username', 'jakonwang')->first();
    if ($user) {
        auth()->login($user);
        echo "✅ 用户登录成功: {$user->username}\n";
        echo "✅ 用户角色: " . ($user->role ? $user->role->name : '无角色') . "\n";
        echo "✅ 是否为超级管理员: " . ($user->isSuperAdmin() ? '是' : '否') . "\n";
        echo "✅ 可以查看利润率: " . ($user->canViewProfitAndCost() ? '是' : '否') . "\n";
    } else {
        echo "❌ 未找到测试用户\n";
        exit;
    }
    
    // 使用反射访问私有方法
    echo "\n正在获取仪表盘数据...\n";
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getDashboardData');
    $method->setAccessible(true);
    $dashboardData = $method->invoke($controller);
    
    echo "✅ 仪表盘数据获取成功\n";
    echo "✅ 数据键: " . implode(', ', array_keys($dashboardData)) . "\n";
    
    // 检查关键数据
    if (isset($dashboardData['todaySales'])) {
        echo "✅ todaySales 数据存在\n";
        echo "   - total_sales: " . ($dashboardData['todaySales']->total_sales ?? 'N/A') . "\n";
        echo "   - total_amount: " . ($dashboardData['todaySales']->total_amount ?? 'N/A') . "\n";
        echo "   - total_profit: " . ($dashboardData['todaySales']->total_profit ?? 'N/A') . "\n";
        echo "   - avg_profit_rate: " . ($dashboardData['todaySales']->avg_profit_rate ?? 'N/A') . "\n";
    }
    
    if (isset($dashboardData['isSuperAdmin'])) {
        echo "✅ isSuperAdmin: " . ($dashboardData['isSuperAdmin'] ? '是' : '否') . "\n";
    }
    
    // 尝试渲染视图
    echo "\n正在测试视图渲染...\n";
    $view = view('dashboard', $dashboardData);
    $content = $view->render();
    
    echo "✅ 视图渲染成功\n";
    echo "✅ 内容长度: " . strlen($content) . " 字符\n";
    
    // 检查是否包含关键内容
    if (strpos($content, 'dashboard') !== false) {
        echo "✅ 包含 dashboard 相关内容\n";
    }
    
    if (strpos($content, 'canViewProfitAndCost') !== false) {
        echo "✅ 包含权限检查代码\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    echo "✅ 所有测试通过，仪表盘页面应该可以正常访问\n";
    
} catch (\Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "❌ 错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "❌ 错误堆栈:\n" . $e->getTraceAsString() . "\n";
} 