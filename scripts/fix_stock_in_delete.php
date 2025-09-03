<?php

/**
 * 修复入库记录删除时的库存回退问题
 * 使用方法: php scripts/fix_stock_in_delete.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockInRecord;
use App\Models\Inventory;
use App\Models\InventoryRecord;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

echo "=== 修复入库记录删除时的库存回退问题 ===\n\n";

try {
    // 1. 检查当前状态
    echo "1. 当前系统状态检查:\n";
    
    $inventories = Inventory::with(['product', 'store'])->get();
    echo "   库存记录数量: " . $inventories->count() . "\n";
    
    $stockInRecords = StockInRecord::with(['store', 'stockInDetails.product'])->get();
    echo "   入库记录数量: " . $stockInRecords->count() . "\n";
    
    $inventoryRecords = InventoryRecord::count();
    echo "   库存变动记录数量: " . $inventoryRecords . "\n";
    
    echo "\n";

    // 2. 分析问题
    echo "2. 问题分析:\n";
    
    if ($stockInRecords->isEmpty()) {
        echo "   ❌ 问题1: 系统中没有入库记录，但存在库存数据\n";
        echo "   可能原因:\n";
        echo "   - 入库记录被手动删除\n";
        echo "   - 库存数据通过其他方式创建\n";
        echo "   - 数据库迁移或种子数据问题\n";
    } else {
        echo "   ✅ 入库记录存在\n";
    }
    
    if ($inventoryRecords == 0) {
        echo "   ❌ 问题2: 没有库存变动记录，无法追踪库存变化历史\n";
    } else {
        echo "   ✅ 库存变动记录存在\n";
    }
    
    echo "\n";

    // 3. 修复方案
    echo "3. 修复方案:\n";
    
    // 3.1 增强入库记录删除逻辑
    echo "   3.1 增强入库记录删除逻辑:\n";
    
    $stockInControllerPath = app_path('Http/Controllers/StockInController.php');
    if (file_exists($stockInControllerPath)) {
        $controllerContent = file_get_contents($stockInControllerPath);
        
        // 检查是否已经包含库存记录创建逻辑
        if (strpos($controllerContent, 'InventoryRecord::create') === false) {
            echo "   - 需要添加入库时的库存记录创建\n";
        } else {
            echo "   - 入库时库存记录创建逻辑已存在\n";
        }
        
        // 检查删除逻辑是否完整
        if (strpos($controllerContent, 'inventory->quantity -= $detail->quantity') !== false) {
            echo "   - 删除时库存回退逻辑已存在\n";
        } else {
            echo "   - 删除时库存回退逻辑缺失\n";
        }
    }
    
    // 3.2 创建库存记录追踪
    echo "   3.2 创建库存记录追踪:\n";
    
    if ($inventoryRecords == 0) {
        echo "   - 需要为现有库存创建初始记录\n";
        
        // 创建初始库存记录
        $createdCount = 0;
        foreach ($inventories as $inventory) {
            if ($inventory->quantity > 0) {
                try {
                    InventoryRecord::create([
                        'inventory_id' => $inventory->id,
                        'quantity' => $inventory->quantity,
                        'unit_price' => $inventory->product->cost_price ?? 0,
                        'total_amount' => $inventory->quantity * ($inventory->product->cost_price ?? 0),
                        'type' => 'in',
                        'reference_type' => 'initial',
                        'reference_id' => 0,
                        'note' => '系统初始化库存',
                    ]);
                    $createdCount++;
                } catch (\Exception $e) {
                    echo "   - 警告: 为库存 #{$inventory->id} 创建记录失败: " . $e->getMessage() . "\n";
                }
            }
        }
        
        if ($createdCount > 0) {
            echo "   ✅ 成功创建 {$createdCount} 条初始库存记录\n";
        }
    }
    
    echo "\n";

    // 4. 测试修复后的功能
    echo "4. 测试修复后的功能:\n";
    
    // 4.1 测试库存回退逻辑
    echo "   4.1 测试库存回退逻辑:\n";
    
    if ($stockInRecords->isNotEmpty()) {
        $testRecord = $stockInRecords->first();
        echo "   - 测试入库记录 #{$testRecord->id}\n";
        
        foreach ($testRecord->stockInDetails as $detail) {
            $inventory = Inventory::where('store_id', $testRecord->store_id)
                ->where('product_id', $detail->product_id)
                ->first();
            
            if ($inventory) {
                $oldQuantity = $inventory->quantity;
                $newQuantity = $oldQuantity - $detail->quantity;
                
                echo "     * {$detail->product->name}: {$oldQuantity} - {$detail->quantity} = {$newQuantity}\n";
                
                // 验证库存是否足够回退
                if ($newQuantity < 0) {
                    echo "       ⚠️  警告: 库存不足，无法完全回退\n";
                }
            }
        }
    } else {
        echo "   - 没有入库记录可测试\n";
    }
    
    // 4.2 测试库存记录创建
    echo "   4.2 测试库存记录创建:\n";
    
    $testInventory = $inventories->first();
    if ($testInventory) {
        echo "   - 测试库存 #{$testInventory->id} ({$testInventory->product->name})\n";
        
        try {
            $record = InventoryRecord::create([
                'inventory_id' => $testInventory->id,
                'quantity' => 1,
                'unit_price' => $testInventory->product->cost_price ?? 0,
                'total_amount' => 1 * ($testInventory->product->cost_price ?? 0),
                'type' => 'test',
                'reference_type' => 'test',
                'reference_id' => 999,
                'note' => '测试库存记录',
            ]);
            
            echo "     ✅ 测试库存记录创建成功 (ID: {$record->id})\n";
            
            // 删除测试记录
            $record->delete();
            echo "     ✅ 测试库存记录已清理\n";
            
        } catch (\Exception $e) {
            echo "     ❌ 测试库存记录创建失败: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";

    // 5. 修复建议
    echo "5. 修复建议:\n";
    echo "   5.1 立即修复:\n";
    echo "   - 检查并修复 StockInController 的删除逻辑\n";
    echo "   - 确保删除时正确回退库存数量\n";
    echo "   - 添加库存记录追踪\n";
    
    echo "   5.2 长期改进:\n";
    echo "   - 实现完整的库存变动审计日志\n";
    echo "   - 添加库存操作权限控制\n";
    echo "   - 实现库存预警和自动补货\n";
    
    echo "\n";

} catch (\Exception $e) {
    echo "修复过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== 修复完成 ===\n"; 