<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SaleDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class FixSaleDetailsCost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:sale-details-cost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复销售明细表中缺失的成本价格数据';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始修复销售明细成本价格...');

        // 查找所有成本价格为空的销售明细
        $saleDetails = SaleDetail::whereNull('cost')
            ->orWhere('cost', 0)
            ->get();

        $this->info("找到 {$saleDetails->count()} 条需要修复的记录");

        $fixedCount = 0;
        $errorCount = 0;

        foreach ($saleDetails as $detail) {
            try {
                // 获取产品的成本价格
                $product = Product::find($detail->product_id);
                
                if ($product && $product->cost_price) {
                    $detail->cost = $product->cost_price;
                    $detail->cost_price = $product->cost_price;
                    
                    // 重新计算利润
                    $detail->profit = $detail->quantity * ($detail->price - $product->cost_price);
                    
                    $detail->save();
                    $fixedCount++;
                    
                    $this->line("已修复销售明细 ID: {$detail->id}, 产品: {$product->name}, 成本价格: {$product->cost_price}");
                } else {
                    $this->warn("产品 {$detail->product_id} 不存在或成本价格为空");
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->error("修复销售明细 ID: {$detail->id} 时出错: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->info("修复完成！成功修复: {$fixedCount} 条，失败: {$errorCount} 条");
        
        return 0;
    }
} 