<?php

/**
 * 批量将现有图片上传到七牛云
 * 
 * 使用方法：
 * php scripts/migrate_images_to_qiniu.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\QiniuStorageService;

echo "========================================\n";
echo "图片批量迁移到七牛云脚本\n";
echo "========================================\n\n";

try {
    $qiniuService = new QiniuStorageService();
    echo "✓ 七牛云服务初始化成功\n\n";
} catch (\Exception $e) {
    echo "✗ 七牛云服务初始化失败: " . $e->getMessage() . "\n";
    echo "请检查.env文件中的QINIU配置\n";
    exit(1);
}

$stats = [
    'products' => ['total' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 0],
    'sales' => ['total' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 0],
    'returns' => ['total' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 0],
    'stock_in' => ['total' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 0],
    'stock_out' => ['total' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 0],
];

/**
 * 迁移商品图片
 */
echo "1. 开始迁移商品图片...\n";
$products = DB::table('products')->whereNotNull('image')->where('image', '!=', '')->get();
$stats['products']['total'] = $products->count();

foreach ($products as $product) {
    $imagePath = $product->image;
    
    // 如果已经是七牛云URL，跳过
    if (str_starts_with($imagePath, 'http') && str_contains($imagePath, config('filesystems.disks.qiniu.domain'))) {
        $stats['products']['skipped']++;
        continue;
    }
    
    try {
        // 获取本地文件路径
        $localPath = null;
        if (str_contains($imagePath, 'uploads/')) {
            $localPath = public_path($imagePath);
        } elseif (str_contains($imagePath, 'storage/')) {
            $localPath = public_path($imagePath);
        } else {
            $localPath = storage_path('app/public/' . $imagePath);
        }
        
        if (!file_exists($localPath)) {
            echo "  ⚠ 文件不存在，跳过: {$imagePath}\n";
            $stats['products']['skipped']++;
            continue;
        }
        
        // 上传到七牛云
        $qiniuPath = 'products/' . basename($imagePath);
        $qiniuUrl = $qiniuService->put($localPath, $qiniuPath);
        
        // 更新数据库
        DB::table('products')->where('id', $product->id)->update(['image' => $qiniuUrl]);
        
        echo "  ✓ 商品 #{$product->id}: {$imagePath} -> {$qiniuUrl}\n";
        $stats['products']['success']++;
        
    } catch (\Exception $e) {
        echo "  ✗ 商品 #{$product->id} 迁移失败: {$e->getMessage()}\n";
        $stats['products']['failed']++;
    }
}

echo "\n";

/**
 * 迁移销售凭证图片
 */
echo "2. 开始迁移销售凭证图片...\n";
$sales = DB::table('sales')->whereNotNull('image_path')->where('image_path', '!=', '')->get();
$stats['sales']['total'] = $sales->count();

foreach ($sales as $sale) {
    $imagePath = $sale->image_path;
    
    // 如果已经是七牛云URL，跳过
    if (str_starts_with($imagePath, 'http') && str_contains($imagePath, config('filesystems.disks.qiniu.domain'))) {
        $stats['sales']['skipped']++;
        continue;
    }
    
    try {
        // 获取本地文件路径
        $localPath = storage_path('app/public/' . $imagePath);
        
        if (!file_exists($localPath)) {
            echo "  ⚠ 文件不存在，跳过: {$imagePath}\n";
            $stats['sales']['skipped']++;
            continue;
        }
        
        // 上传到七牛云
        $qiniuPath = 'sales/' . basename($imagePath);
        $qiniuUrl = $qiniuService->put($localPath, $qiniuPath);
        
        // 更新数据库
        DB::table('sales')->where('id', $sale->id)->update(['image_path' => $qiniuUrl]);
        
        echo "  ✓ 销售 #{$sale->id}: {$imagePath} -> {$qiniuUrl}\n";
        $stats['sales']['success']++;
        
    } catch (\Exception $e) {
        echo "  ✗ 销售 #{$sale->id} 迁移失败: {$e->getMessage()}\n";
        $stats['sales']['failed']++;
    }
}

echo "\n";

/**
 * 迁移退货凭证图片
 */
echo "3. 开始迁移退货凭证图片...\n";
$returns = DB::table('return_records')->whereNotNull('image_path')->where('image_path', '!=', '')->get();
$stats['returns']['total'] = $returns->count();

foreach ($returns as $return) {
    $imagePath = $return->image_path;
    
    // 如果已经是七牛云URL，跳过
    if (str_starts_with($imagePath, 'http') && str_contains($imagePath, config('filesystems.disks.qiniu.domain'))) {
        $stats['returns']['skipped']++;
        continue;
    }
    
    try {
        // 获取本地文件路径
        $localPath = storage_path('app/public/' . $imagePath);
        
        if (!file_exists($localPath)) {
            echo "  ⚠ 文件不存在，跳过: {$imagePath}\n";
            $stats['returns']['skipped']++;
            continue;
        }
        
        // 上传到七牛云
        $qiniuPath = 'returns/' . basename($imagePath);
        $qiniuUrl = $qiniuService->put($localPath, $qiniuPath);
        
        // 更新数据库
        DB::table('return_records')->where('id', $return->id)->update(['image_path' => $qiniuUrl]);
        
        echo "  ✓ 退货 #{$return->id}: {$imagePath} -> {$qiniuUrl}\n";
        $stats['returns']['success']++;
        
    } catch (\Exception $e) {
        echo "  ✗ 退货 #{$return->id} 迁移失败: {$e->getMessage()}\n";
        $stats['returns']['failed']++;
    }
}

echo "\n";

/**
 * 迁移入库凭证图片
 */
echo "4. 开始迁移入库凭证图片...\n";
$stockIns = DB::table('stock_in_records')->whereNotNull('image_path')->where('image_path', '!=', '')->get();
$stats['stock_in']['total'] = $stockIns->count();

foreach ($stockIns as $stockIn) {
    $imagePath = $stockIn->image_path;
    
    // 如果已经是七牛云URL，跳过
    if (str_starts_with($imagePath, 'http') && str_contains($imagePath, config('filesystems.disks.qiniu.domain'))) {
        $stats['stock_in']['skipped']++;
        continue;
    }
    
    try {
        // 获取本地文件路径
        $localPath = storage_path('app/public/' . $imagePath);
        
        if (!file_exists($localPath)) {
            echo "  ⚠ 文件不存在，跳过: {$imagePath}\n";
            $stats['stock_in']['skipped']++;
            continue;
        }
        
        // 上传到七牛云
        $qiniuPath = 'stock-in/' . basename($imagePath);
        $qiniuUrl = $qiniuService->put($localPath, $qiniuPath);
        
        // 更新数据库
        DB::table('stock_in_records')->where('id', $stockIn->id)->update(['image_path' => $qiniuUrl]);
        
        echo "  ✓ 入库 #{$stockIn->id}: {$imagePath} -> {$qiniuUrl}\n";
        $stats['stock_in']['success']++;
        
    } catch (\Exception $e) {
        echo "  ✗ 入库 #{$stockIn->id} 迁移失败: {$e->getMessage()}\n";
        $stats['stock_in']['failed']++;
    }
}

echo "\n";

/**
 * 迁移出库凭证图片
 */
echo "5. 开始迁移出库凭证图片...\n";
$stockOuts = DB::table('stock_out_records')->whereNotNull('image_path')->where('image_path', '!=', '')->get();
$stats['stock_out']['total'] = $stockOuts->count();

foreach ($stockOuts as $stockOut) {
    $imagePath = $stockOut->image_path;
    
    // 如果已经是七牛云URL，跳过
    if (str_starts_with($imagePath, 'http') && str_contains($imagePath, config('filesystems.disks.qiniu.domain'))) {
        $stats['stock_out']['skipped']++;
        continue;
    }
    
    try {
        // 获取本地文件路径
        $localPath = storage_path('app/public/' . $imagePath);
        
        if (!file_exists($localPath)) {
            echo "  ⚠ 文件不存在，跳过: {$imagePath}\n";
            $stats['stock_out']['skipped']++;
            continue;
        }
        
        // 上传到七牛云
        $qiniuPath = 'stock-out/' . basename($imagePath);
        $qiniuUrl = $qiniuService->put($localPath, $qiniuPath);
        
        // 更新数据库
        DB::table('stock_out_records')->where('id', $stockOut->id)->update(['image_path' => $qiniuUrl]);
        
        echo "  ✓ 出库 #{$stockOut->id}: {$imagePath} -> {$qiniuUrl}\n";
        $stats['stock_out']['success']++;
        
    } catch (\Exception $e) {
        echo "  ✗ 出库 #{$stockOut->id} 迁移失败: {$e->getMessage()}\n";
        $stats['stock_out']['failed']++;
    }
}

echo "\n";

/**
 * 输出统计信息
 */
echo "========================================\n";
echo "迁移完成统计\n";
echo "========================================\n\n";

foreach ($stats as $type => $stat) {
    $typeName = [
        'products' => '商品图片',
        'sales' => '销售凭证',
        'returns' => '退货凭证',
        'stock_in' => '入库凭证',
        'stock_out' => '出库凭证',
    ][$type] ?? $type;
    
    echo "{$typeName}:\n";
    echo "  总数: {$stat['total']}\n";
    echo "  成功: {$stat['success']}\n";
    echo "  失败: {$stat['failed']}\n";
    echo "  跳过: {$stat['skipped']}\n";
    echo "\n";
}

$totalSuccess = array_sum(array_column($stats, 'success'));
$totalFailed = array_sum(array_column($stats, 'failed'));
$totalSkipped = array_sum(array_column($stats, 'skipped'));

echo "总计:\n";
echo "  成功: {$totalSuccess}\n";
echo "  失败: {$totalFailed}\n";
echo "  跳过: {$totalSkipped}\n";
echo "\n";

echo "迁移完成！\n";

