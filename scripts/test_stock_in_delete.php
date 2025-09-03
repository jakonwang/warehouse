<?php

/**
 * 测试入库记录删除时的库存回退功能
 * 使用方法: php scripts/test_stock_in_delete.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockInRecord;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

echo "=== 测试入库记录删除时的库存回退功能 ===\n\n";

try {
    // 1. 检查当前库存状态
    echo "1. 当前库存状态:\n";
    $inventories = Inventory::with(['product', 'store'])->get();
    foreach ($inventories as $inventory) {
        echo "   - {$inventory->product->name} (仓库: {$inventory->store->name}): {$inventory->quantity}个\n";
    }
    echo "\n";

    // 2. 检查入库记录
    echo "2. 当前入库记录:\n";
    $stockInRecords = StockInRecord::with(['store', 'stockInDetails.product'])->get();
    if ($stockInRecords->isEmpty()) {
        echo "   没有入库记录\n";
    } else {
        foreach ($stockInRecords as $record) {
            echo "   - 入库记录 #{$record->id} (仓库: {$record->store->name})\n";
            foreach ($record->stockInDetails as $detail) {
                echo "     * {$detail->product->name}: {$detail->quantity}个\n";
            }
        }
    }
    echo "\n";

    // 3. 测试删除逻辑
    if ($stockInRecords->isNotEmpty()) {
        $testRecord = $stockInRecords->first();
        echo "3. 测试删除入库记录 #{$testRecord->id}:\n";
        
        // 记录删除前的库存
        $beforeInventories = [];
        foreach ($testRecord->stockInDetails as $detail) {
            $inventory = Inventory::where('store_id', $testRecord->store_id)
                ->where('product_id', $detail->product_id)
                ->first();
            if ($inventory) {
                $beforeInventories[$detail->product_id] = $inventory->quantity;
                echo "   - 删除前 {$detail->product->name} 库存: {$inventory->quantity}个\n";
            }
        }
        
        // 执行删除逻辑（模拟）
        echo "\n   执行删除逻辑...\n";
        DB::beginTransaction();
        
        try {
            // 恢复库存（多仓库支持）
            foreach ($testRecord->stockInDetails as $detail) {
                $inventory = Inventory::where('store_id', $testRecord->store_id)
                    ->where('product_id', $detail->product_id)
                    ->first();
                
                if ($inventory) {
                    $oldQuantity = $inventory->quantity;
                    $inventory->quantity -= $detail->quantity;
                    $inventory->save();
                    
                    echo "   - {$detail->product->name}: {$oldQuantity} - {$detail->quantity} = {$inventory->quantity}\n";
                } else {
                    echo "   - 警告: 找不到 {$detail->product->name} 的库存记录\n";
                }
            }
            
            echo "\n   删除逻辑执行完成\n";
            DB::rollBack(); // 回滚，不实际删除
            
        } catch (\Exception $e) {
            DB::rollBack();
            echo "   错误: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "3. 没有入库记录可测试\n";
    }
    
    // 4. 检查库存表结构
    echo "\n4. 库存表结构检查:\n";
    $columns = DB::select("DESCRIBE inventory");
    foreach ($columns as $column) {
        echo "   - {$column->Field}: {$column->Type} " . ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    // 5. 检查是否有库存记录表
    echo "\n5. 检查库存记录表:\n";
    try {
        $recordColumns = DB::select("DESCRIBE inventory_records");
        echo "   库存记录表存在，包含以下字段:\n";
        foreach ($recordColumns as $column) {
            echo "   - {$column->Field}: {$column->Type}\n";
        }
    } catch (\Exception $e) {
        echo "   库存记录表不存在或无法访问\n";
    }
    
} catch (\Exception $e) {
    echo "测试过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== 测试完成 ===\n"; 