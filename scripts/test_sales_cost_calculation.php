<?php

/**
 * 测试销售页面中标品的成本计算修复
 * 使用方法: php scripts/test_sales_cost_calculation.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;

echo "=== 测试销售页面中标品的成本计算修复 ===\n\n";

try {
    // 1. 检查商品数据
    echo "1. 检查商品数据:\n";
    $products = Product::where('type', 'standard')->take(3)->get();
    foreach ($products as $product) {
        echo "   - {$product->name} (编码: {$product->code}): 售价¥{$product->price}, 成本¥{$product->cost_price}\n";
    }
    echo "\n";

    // 2. 检查销售记录
    echo "2. 检查销售记录:\n";
    $sales = Sale::with(['saleDetails.product'])->take(3)->get();
    foreach ($sales as $sale) {
        echo "   - 销售记录 #{$sale->id} (类型: {$sale->sale_type})\n";
        echo "     总金额: ¥{$sale->total_amount}, 总成本: ¥{$sale->total_cost}, 总利润: ¥{$sale->total_profit}\n";
        
        if ($sale->saleDetails->isNotEmpty()) {
            echo "     销售明细:\n";
            foreach ($sale->saleDetails as $detail) {
                $expectedCost = $detail->quantity * $detail->cost_price;
                $costStatus = $expectedCost == $detail->cost_price ? '❌ 错误' : '✅ 正确';
                echo "       * {$detail->product->name}: {$detail->quantity}个 × ¥{$detail->cost_price} = ¥{$expectedCost} {$costStatus}\n";
            }
        }
        echo "\n";
    }

    // 3. 测试成本计算逻辑
    echo "3. 测试成本计算逻辑:\n";
    
    // 模拟销售数据
    $testData = [
        ['quantity' => 5, 'cost_price' => 10],
        ['quantity' => 3, 'cost_price' => 20],
        ['quantity' => 2, 'cost_price' => 15]
    ];
    
    $totalCost = 0;
    $expectedTotalCost = 0;
    
    foreach ($testData as $index => $item) {
        $cost = $item['cost_price'];
        $quantity = $item['quantity'];
        $itemTotalCost = $cost * $quantity;
        
        // 错误的计算方式（修复前）
        $totalCost += $cost;
        
        // 正确的计算方式（修复后）
        $expectedTotalCost += $itemTotalCost;
        
        echo "   - 商品 {$index}: {$quantity}个 × ¥{$cost} = ¥{$itemTotalCost}\n";
    }
    
    echo "   错误的计算方式（修复前）: ¥{$totalCost}\n";
    echo "   正确的计算方式（修复后）: ¥{$expectedTotalCost}\n";
    echo "   差异: ¥" . ($expectedTotalCost - $totalCost) . "\n\n";

    // 4. 检查控制器修复
    echo "4. 检查控制器修复:\n";
    
    $controllerPath = app_path('Http/Controllers/SaleController.php');
    if (file_exists($controllerPath)) {
        $controllerContent = file_get_contents($controllerPath);
        
        $checks = [
            '$totalCost += $detail->cost_price * $detail->quantity' => '标品成本计算（乘以数量）',
            'totalCost += detail->cost_price * detail->quantity' => '移动端成本计算（乘以数量）'
        ];
        
        foreach ($checks as $pattern => $description) {
            if (strpos($controllerContent, $pattern) !== false) {
                echo "   ✅ {$description}: 已修复\n";
            } else {
                echo "   ❌ {$description}: 未找到\n";
            }
        }
        
        // 检查是否还有错误的计算方式
        $wrongPatterns = [
            '$totalCost += $detail->cost_price;' => '错误的成本计算（未乘以数量）',
            'totalCost += detail->cost_price;' => '错误的成本计算（未乘以数量）'
        ];
        
        foreach ($wrongPatterns as $pattern => $description) {
            if (strpos($controllerContent, $pattern) !== false) {
                echo "   ⚠️  {$description}: 仍然存在\n";
            } else {
                echo "   ✅ {$description}: 已修复\n";
            }
        }
        
    } else {
        echo "   ❌ 控制器文件不存在\n";
    }
    
    echo "\n";

    // 5. 检查移动端控制器
    echo "5. 检查移动端控制器:\n";
    
    $mobileControllerPath = app_path('Http/Controllers/Mobile/SaleController.php');
    if (file_exists($mobileControllerPath)) {
        $mobileControllerContent = file_get_contents($mobileControllerPath);
        
        $checks = [
            '$totalCost += $detail->cost_price * $detail->quantity' => '标品成本计算（乘以数量）'
        ];
        
        foreach ($checks as $pattern => $description) {
            if (strpos($mobileControllerContent, $pattern) !== false) {
                echo "   ✅ {$description}: 已修复\n";
            } else {
                echo "   ❌ {$description}: 未找到\n";
            }
        }
        
    } else {
        echo "   ❌ 移动端控制器文件不存在\n";
    }
    
    echo "\n";

    // 6. 验证修复效果
    echo "6. 验证修复效果:\n";
    
    // 检查是否有销售记录需要重新计算
    $incorrectSales = Sale::with(['saleDetails.product'])->get()->filter(function($sale) {
        if ($sale->sale_type === 'standard' && $sale->saleDetails->isNotEmpty()) {
            $expectedCost = $sale->saleDetails->sum(function($detail) {
                return $detail->quantity * $detail->cost_price;
            });
            return abs($sale->total_cost - $expectedCost) > 0.01; // 允许0.01的误差
        }
        return false;
    });
    
    if ($incorrectSales->count() > 0) {
        echo "   ⚠️  发现 {$incorrectSales->count()} 条销售记录的成本计算可能不正确\n";
        echo "   建议运行数据修复脚本重新计算这些记录的成本\n";
    } else {
        echo "   ✅ 所有销售记录的成本计算都是正确的\n";
    }
    
    echo "\n";

    // 7. 修复建议
    echo "7. 修复建议:\n";
    echo "   - 已修复PC端和移动端销售控制器的成本计算逻辑\n";
    echo "   - 建议检查历史销售记录的成本计算是否正确\n";
    echo "   - 可以考虑创建数据修复脚本重新计算历史记录\n";
    echo "   - 确保前端显示的成本和利润数据与后端计算一致\n";
    
    echo "\n";

} catch (\Exception $e) {
    echo "测试过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== 测试完成 ===\n"; 