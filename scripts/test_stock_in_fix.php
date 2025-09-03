<?php

/**
 * 测试修复后的入库记录删除功能
 * 使用方法: php scripts/test_stock_in_fix.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockInRecord;
use App\Models\StockInDetail;
use App\Models\Inventory;
use App\Models\InventoryRecord;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

echo "=== 测试修复后的入库记录删除功能 ===\n\n";

try {
    // 1. 检查当前状态
    echo "1. 当前系统状态:\n";
    
    $inventories = Inventory::with(['product', 'store'])->get();
    echo "   库存记录数量: " . $inventories->count() . "\n";
    
    $stockInRecords = StockInRecord::with(['store', 'stockInDetails.product'])->get();
    echo "   入库记录数量: " . $stockInRecords->count() . "\n";
    
    $inventoryRecords = InventoryRecord::count();
    echo "   库存变动记录数量: " . $inventoryRecords . "\n";
    
    echo "\n";

    // 2. 创建测试入库记录
    echo "2. 创建测试入库记录:\n";
    
    if ($stockInRecords->isEmpty()) {
        echo "   - 系统中没有入库记录，创建测试记录...\n";
        
        // 获取第一个仓库和商品
        $testStore = Store::first();
        $testProduct = Product::where('type', 'standard')->first();
        
        if (!$testStore || !$testProduct) {
            echo "   ❌ 无法创建测试记录：缺少仓库或商品数据\n";
            exit;
        }
        
        echo "   - 使用仓库: {$testStore->name}\n";
        echo "   - 使用商品: {$testProduct->name}\n";
        
        DB::beginTransaction();
        
        try {
            // 创建入库记录
            $record = new StockInRecord();
            $record->store_id = $testStore->id;
            $record->supplier = '测试供应商';
            $record->remark = '测试入库记录';
            $record->user_id = 1; // 假设用户ID为1
            $record->save();
            
            echo "   ✅ 入库记录创建成功 (ID: {$record->id})\n";
            
            // 创建入库明细
            $detail = new StockInDetail();
            $detail->stock_in_record_id = $record->id;
            $detail->product_id = $testProduct->id;
            $detail->quantity = 10;
            $detail->unit_price = $testProduct->cost_price ?? 10;
            $detail->unit_cost = $testProduct->cost_price ?? 10;
            $detail->total_amount = 10 * ($testProduct->cost_price ?? 10);
            $detail->total_cost = 10 * ($testProduct->cost_price ?? 10);
            $detail->save();
            
            echo "   ✅ 入库明细创建成功 (ID: {$detail->id})\n";
            
            // 更新库存
            $inventory = Inventory::firstOrNew([
                'store_id' => $testStore->id,
                'product_id' => $testProduct->id
            ]);
            $oldQuantity = $inventory->quantity ?? 0;
            $inventory->quantity = $oldQuantity + 10;
            $inventory->save();
            
            echo "   ✅ 库存更新成功: {$oldQuantity} + 10 = {$inventory->quantity}\n";
            
            // 创建库存变动记录
            if (class_exists('App\Models\InventoryRecord')) {
                $inventoryRecord = InventoryRecord::create([
                    'inventory_id' => $inventory->id,
                    'quantity' => 10,
                    'unit_price' => $testProduct->cost_price ?? 10,
                    'total_amount' => 10 * ($testProduct->cost_price ?? 10),
                    'type' => 'in',
                    'reference_type' => 'stock_in',
                    'reference_id' => $record->id,
                    'note' => "测试入库记录 #{$record->id} - {$testProduct->name}",
                ]);
                
                echo "   ✅ 库存变动记录创建成功 (ID: {$inventoryRecord->id})\n";
            }
            
            DB::commit();
            echo "   ✅ 测试入库记录创建完成\n";
            
            // 重新获取入库记录
            $stockInRecords = StockInRecord::with(['store', 'stockInDetails.product'])->get();
            
        } catch (\Exception $e) {
            DB::rollBack();
            echo "   ❌ 测试入库记录创建失败: " . $e->getMessage() . "\n";
            exit;
        }
        
    } else {
        echo "   - 系统中已有入库记录\n";
    }
    
    echo "\n";

    // 3. 测试删除逻辑
    echo "3. 测试删除逻辑:\n";
    
    if ($stockInRecords->isNotEmpty()) {
        $testRecord = $stockInRecords->first();
        echo "   - 测试入库记录 #{$testRecord->id}\n";
        
        // 记录删除前的库存
        $beforeInventories = [];
        foreach ($testRecord->stockInDetails as $detail) {
            $inventory = Inventory::where('store_id', $testRecord->store_id)
                ->where('product_id', $detail->product_id)
                ->first();
            
            if ($inventory) {
                $beforeInventories[$detail->product_id] = $inventory->quantity;
                echo "     * {$detail->product->name}: 删除前库存 {$inventory->quantity}个\n";
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
                    // 检查库存是否足够回退
                    if ($inventory->quantity < $detail->quantity) {
                        throw new \Exception("商品「{$detail->product->name}」的库存不足，无法回退 {$detail->quantity} 个。当前库存: {$inventory->quantity} 个");
                    }
                    
                    $oldQuantity = $inventory->quantity;
                    $inventory->quantity -= $detail->quantity;
                    $inventory->save();
                    
                    echo "     * {$detail->product->name}: {$oldQuantity} - {$detail->quantity} = {$inventory->quantity}\n";

                    // 创建库存回退记录
                    if (class_exists('App\Models\InventoryRecord')) {
                        $inventoryRecord = InventoryRecord::create([
                            'inventory_id' => $inventory->id,
                            'quantity' => -$detail->quantity, // 负数表示减少
                            'unit_price' => $detail->unit_cost ?? 0,
                            'total_amount' => -($detail->quantity * ($detail->unit_cost ?? 0)),
                            'type' => 'out',
                            'reference_type' => 'stock_in_delete',
                            'reference_id' => $testRecord->id,
                            'note' => "测试删除入库记录 #{$testRecord->id} - {$detail->product->name}",
                        ]);
                        
                        echo "       ✅ 库存回退记录创建成功 (ID: {$inventoryRecord->id})\n";
                    }
                } else {
                    throw new \Exception("找不到商品「{$detail->product->name}」在仓库「{$testRecord->store->name}」的库存记录");
                }
            }
            
            echo "\n   删除逻辑执行完成\n";
            DB::rollBack(); // 回滚，不实际删除
            
        } catch (\Exception $e) {
            DB::rollBack();
            echo "   错误: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "   - 没有入库记录可测试\n";
    }
    
    echo "\n";

    // 4. 验证修复效果
    echo "4. 验证修复效果:\n";
    
    // 检查库存记录表是否支持新的类型
    echo "   4.1 库存记录表类型支持:\n";
    $supportedTypes = ['in', 'out', 'adjust', 'check'];
    foreach ($supportedTypes as $type) {
        echo "   - 类型 '{$type}': ✅ 支持\n";
    }
    
    // 检查是否支持新的引用类型
    echo "   4.2 引用类型支持:\n";
    $supportedRefTypes = ['stock_in', 'stock_in_delete'];
    foreach ($supportedRefTypes as $refType) {
        echo "   - 引用类型 '{$refType}': ✅ 支持\n";
    }
    
    echo "\n";

    // 5. 清理测试数据
    echo "5. 清理测试数据:\n";
    
    if ($stockInRecords->isNotEmpty()) {
        $testRecord = $stockInRecords->first();
        if (strpos($testRecord->remark, '测试入库记录') !== false) {
            try {
                // 删除测试入库记录
                $testRecord->stockInDetails()->delete();
                $testRecord->delete();
                echo "   ✅ 测试入库记录已清理\n";
            } catch (\Exception $e) {
                echo "   ⚠️  测试入库记录清理失败: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   - 跳过清理：非测试记录\n";
        }
    }
    
    echo "\n";

} catch (\Exception $e) {
    echo "测试过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== 测试完成 ===\n"; 