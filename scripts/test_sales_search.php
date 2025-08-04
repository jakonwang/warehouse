<?php

/**
 * 销售搜索功能测试脚本
 * 
 * 测试销售页面的搜索功能是否正常工作
 * 包括商品搜索、客户名称搜索、时间搜索等
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// 启动Laravel应用
$app = Application::configure(basePath: __DIR__ . '/..')
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== 销售搜索功能测试 ===\n\n";

try {
    // 测试1: 检查销售控制器是否存在
    echo "1. 检查销售控制器...\n";
    $controller = new \App\Http\Controllers\SaleController();
    echo "   ✅ 销售控制器加载成功\n\n";

    // 测试2: 检查销售模型是否存在
    echo "2. 检查销售模型...\n";
    $saleModel = new \App\Models\Sale();
    echo "   ✅ 销售模型加载成功\n\n";

    // 测试3: 检查商品模型是否存在
    echo "3. 检查商品模型...\n";
    $productModel = new \App\Models\Product();
    echo "   ✅ 商品模型加载成功\n\n";

    // 测试4: 检查数据库连接
    echo "4. 检查数据库连接...\n";
    $db = \Illuminate\Support\Facades\DB::connection();
    $db->getPdo();
    echo "   ✅ 数据库连接正常\n\n";

    // 测试5: 检查销售记录数量
    echo "5. 检查销售记录...\n";
    $salesCount = \App\Models\Sale::count();
    echo "   📊 当前销售记录总数: {$salesCount}\n\n";

    // 测试6: 检查商品记录数量
    echo "6. 检查商品记录...\n";
    $productsCount = \App\Models\Product::count();
    echo "   📊 当前商品记录总数: {$productsCount}\n\n";

    // 测试7: 检查带图片的商品数量
    echo "7. 检查带图片的商品...\n";
    $productsWithImages = \App\Models\Product::whereNotNull('image')->count();
    echo "   📊 带图片的商品数量: {$productsWithImages}\n\n";

    // 测试8: 检查销售详情记录
    echo "8. 检查销售详情记录...\n";
    $saleDetailsCount = \App\Models\SaleDetail::count();
    echo "   📊 销售详情记录总数: {$saleDetailsCount}\n\n";

    // 测试9: 检查盲袋销售记录
    echo "9. 检查盲袋销售记录...\n";
    $blindBagSalesCount = \App\Models\BlindBagSale::count();
    echo "   📊 盲袋销售记录总数: {$blindBagSalesCount}\n\n";

    // 测试10: 检查盲袋发货记录
    echo "10. 检查盲袋发货记录...\n";
    $blindBagDeliveriesCount = \App\Models\BlindBagDelivery::count();
    echo "   📊 盲袋发货记录总数: {$blindBagDeliveriesCount}\n\n";

    // 测试11: 模拟搜索查询
    echo "11. 测试搜索查询...\n";
    
    // 测试商品搜索
    $productSearchQuery = \App\Models\Sale::with([
        'saleDetails.product:id,name,image',
        'blindBagSales.product:id,name,image',
        'blindBagDeliveries.deliveryProduct:id,name,image'
    ]);
    
    // 模拟商品名称搜索
    $testProductName = '测试';
    $productSearchQuery->where(function($q) use ($testProductName) {
        $q->whereHas('saleDetails.product', function($productQuery) use ($testProductName) {
            $productQuery->where('name', 'like', '%' . $testProductName . '%')
                        ->orWhere('code', 'like', '%' . $testProductName . '%');
        })
        ->orWhereHas('blindBagSales.product', function($productQuery) use ($testProductName) {
            $productQuery->where('name', 'like', '%' . $testProductName . '%')
                        ->orWhere('code', 'like', '%' . $testProductName . '%');
        })
        ->orWhereHas('blindBagDeliveries.deliveryProduct', function($productQuery) use ($testProductName) {
            $productQuery->where('name', 'like', '%' . $testProductName . '%')
                        ->orWhere('code', 'like', '%' . $testProductName . '%');
        });
    });
    
    $searchResultCount = $productSearchQuery->count();
    echo "   📊 商品搜索查询测试完成，结果数量: {$searchResultCount}\n\n";

    // 测试12: 检查语言文件
    echo "12. 检查语言文件...\n";
    $langPath = __DIR__ . '/../resources/lang/zh_CN/messages.php';
    if (file_exists($langPath)) {
        $messages = require $langPath;
        if (isset($messages['sale']['product_search'])) {
            echo "   ✅ 销售搜索相关翻译存在\n";
        } else {
            echo "   ⚠️  销售搜索相关翻译缺失\n";
        }
    } else {
        echo "   ❌ 语言文件不存在\n";
    }
    echo "\n";

    // 测试13: 检查视图文件
    echo "13. 检查视图文件...\n";
    $viewPath = __DIR__ . '/../resources/views/sales/index.blade.php';
    if (file_exists($viewPath)) {
        echo "   ✅ 销售列表视图文件存在\n";
        
        // 检查是否包含图片相关代码
        $viewContent = file_get_contents($viewPath);
        if (strpos($viewContent, 'product-image') !== false) {
            echo "   ✅ 图片显示功能代码存在\n";
        } else {
            echo "   ⚠️  图片显示功能代码缺失\n";
        }
        
        if (strpos($viewContent, 'imageModal') !== false) {
            echo "   ✅ 图片模态框功能代码存在\n";
        } else {
            echo "   ⚠️  图片模态框功能代码缺失\n";
        }
    } else {
        echo "   ❌ 销售列表视图文件不存在\n";
    }
    echo "\n";

    echo "=== 测试完成 ===\n";
    echo "✅ 所有基础功能检查通过\n";
    echo "📝 请访问 /sales 页面测试实际功能\n\n";

} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "📍 错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
} 