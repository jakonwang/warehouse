<?php

/**
 * 修复销售记录的成本计算
 * 使用方法: php scripts/fix_sales_cost_calculation.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;

echo "=== 修复销售记录的成本计算 ===\n\n";

try {
    // 1. 检查需要修复的销售记录
    echo "1. 检查需要修复的销售记录:\n";
    
    $incorrectSales = Sale::with(['saleDetails.product'])->get()->filter(function($sale) {
        if ($sale->sale_type === 'standard' && $sale->saleDetails->isNotEmpty()) {
            $expectedCost = $sale->saleDetails->sum(function($detail) {
                return $detail->quantity * $detail->cost_price;
            });
            return abs($sale->total_cost - $expectedCost) > 0.01; // 允许0.01的误差
        }
        return false;
    });
    
    if ($incorrectSales->count() === 0) {
        echo "   ✅ 所有销售记录的成本计算都是正确的，无需修复\n";
        echo "\n=== 修复完成 ===\n";
        exit;
    }
    
    echo "   发现 {$incorrectSales->count()} 条销售记录需要修复\n\n";
    
    // 2. 显示修复前的数据
    echo "2. 修复前的数据:\n";
    foreach ($incorrectSales as $sale) {
        echo "   - 销售记录 #{$sale->id}\n";
        echo "     当前总成本: ¥{$sale->total_cost}\n";
        echo "     销售明细:\n";
        
        $expectedCost = 0;
        foreach ($sale->saleDetails as $detail) {
            $itemCost = $detail->quantity * $detail->cost_price;
            $expectedCost += $itemCost;
            echo "       * {$detail->product->name}: {$detail->quantity}个 × ¥{$detail->cost_price} = ¥{$itemCost}\n";
        }
        
        echo "     期望总成本: ¥{$expectedCost}\n";
        echo "     差异: ¥" . ($expectedCost - $sale->total_cost) . "\n\n";
    }
    
    // 3. 确认是否执行修复
    echo "3. 确认修复操作:\n";
    echo "   即将修复 {$incorrectSales->count()} 条销售记录的成本计算\n";
    echo "   此操作将更新数据库中的成本数据\n";
    echo "   是否继续？(y/N): ";
    
    $handle = fopen("php://stdin", "r");
    $input = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($input) !== 'y') {
        echo "   操作已取消\n";
        echo "\n=== 修复取消 ===\n";
        exit;
    }
    
    // 4. 执行修复
    echo "\n4. 执行修复:\n";
    
    DB::beginTransaction();
    
    try {
        $fixedCount = 0;
        $totalCostDifference = 0;
        
        foreach ($incorrectSales as $sale) {
            echo "   - 修复销售记录 #{$sale->id}...";
            
            // 重新计算总成本
            $newTotalCost = $sale->saleDetails->sum(function($detail) {
                return $detail->quantity * $detail->cost_price;
            });
            
            // 计算差异
            $costDifference = $newTotalCost - $sale->total_cost;
            $totalCostDifference += $costDifference;
            
            // 更新销售记录
            $sale->total_cost = $newTotalCost;
            $sale->total_profit = $sale->total_amount - $newTotalCost;
            $sale->profit_rate = $sale->total_amount > 0 ? ($sale->total_profit / $sale->total_amount) * 100 : 0;
            $sale->save();
            
            $fixedCount++;
            echo " ✅ (成本: ¥{$sale->total_cost} → ¥{$newTotalCost}, 差异: ¥{$costDifference})\n";
        }
        
        DB::commit();
        
        echo "\n   ✅ 修复完成！共修复 {$fixedCount} 条销售记录\n";
        echo "   总成本差异: ¥{$totalCostDifference}\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
    
    // 5. 验证修复结果
    echo "\n5. 验证修复结果:\n";
    
    $remainingIncorrectSales = Sale::with(['saleDetails.product'])->get()->filter(function($sale) {
        if ($sale->sale_type === 'standard' && $sale->saleDetails->isNotEmpty()) {
            $expectedCost = $sale->saleDetails->sum(function($detail) {
                return $detail->quantity * $detail->cost_price;
            });
            return abs($sale->total_cost - $expectedCost) > 0.01;
        }
        return false;
    });
    
    if ($remainingIncorrectSales->count() === 0) {
        echo "   ✅ 所有销售记录的成本计算现在都是正确的\n";
    } else {
        echo "   ⚠️  仍有 {$remainingIncorrectSales->count()} 条销售记录需要修复\n";
    }
    
    // 6. 清理缓存
    echo "\n6. 清理缓存:\n";
    
    try {
        // 清除仪表盘缓存
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $cacheKey = 'dashboard_data_' . $user->id;
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
        
        echo "   ✅ 已清理仪表盘缓存\n";
        
    } catch (\Exception $e) {
        echo "   ⚠️  清理缓存时出现错误: " . $e->getMessage() . "\n";
    }
    
    // 7. 修复总结
    echo "\n7. 修复总结:\n";
    echo "   - 修复了 {$fixedCount} 条销售记录的成本计算\n";
    echo "   - 总成本差异: ¥{$totalCostDifference}\n";
    echo "   - 所有标品销售记录现在都使用正确的成本计算方式\n";
    echo "   - 成本 = 数量 × 单价成本\n";
    echo "   - 利润 = 销售金额 - 总成本\n";
    echo "   - 利润率 = (利润 / 销售金额) × 100%\n";
    
    echo "\n";

} catch (\Exception $e) {
    echo "修复过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
        echo "已回滚数据库事务\n";
    }
}

echo "=== 修复完成 ===\n"; 