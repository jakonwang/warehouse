<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    // 仓库健康度评估页面
    public function health(Request $request)
    {
        $stores = \App\Models\Store::all(['id', 'name']);
        $storeId = $request->input('store_id') ?: ($stores->first() ? $stores->first()->id : null);
        $currentStore = $stores->where('id', $storeId)->first();

        // 默认各项分数
        $indicators = [
            '库存周转率' => 0,
            '销售增长率' => 0,
            '利润率' => 0,
            '库存准确率' => 0,
            '操作效率' => 0,
        ];
        $totalScore = 0;

        if ($currentStore) {
            $now = now();
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();
            $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
            $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

            // 1. 库存周转率 = 本月销售成本 / 平均库存成本
            $salesCost = \App\Models\Sale::where('store_id', $currentStore->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_cost');
            $avgInventoryCost = \App\Models\Inventory::where('store_id', $currentStore->id)
                ->avg(\DB::raw('quantity * (select cost_price from products where products.id = inventory.product_id)'));
            $indicators['库存周转率'] = $avgInventoryCost > 0 ? min(round($salesCost / $avgInventoryCost * 100), 100) : 0;

            // 2. 销售增长率 = (本月销售额-上月销售额)/上月销售额
            $salesAmountThis = \App\Models\Sale::where('store_id', $currentStore->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_amount');
            $salesAmountLast = \App\Models\Sale::where('store_id', $currentStore->id)
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->sum('total_amount');
            $indicators['销售增长率'] = $salesAmountLast > 0 ? min(round((($salesAmountThis - $salesAmountLast) / $salesAmountLast) * 100 + 50), 100) : ($salesAmountThis > 0 ? 100 : 0);

            // 3. 利润率 = 本月利润/本月销售额
            $profit = \App\Models\Sale::where('store_id', $currentStore->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_profit');
            $indicators['利润率'] = $salesAmountThis > 0 ? min(round($profit / $salesAmountThis * 100), 100) : 0;

            // 4. 库存准确率 = 最近一次盘点正确商品数/总盘点商品数
            $lastCheck = \App\Models\InventoryCheckRecord::where('store_id', $currentStore->id)
                ->orderByDesc('created_at')->first();
            if ($lastCheck) {
                $details = $lastCheck->inventoryCheckDetails;
                $total = $details->count();
                $correct = $details->where('difference', 0)->count();
                $indicators['库存准确率'] = $total > 0 ? round($correct / $total * 100) : 0;
            }

            // 5. 操作效率 = 本月入库、出库平均用时（示例，暂用默认80分）
            $indicators['操作效率'] = 80;

            // 计算总分
            $totalScore = round(
                $indicators['库存周转率'] * 0.3 +
                $indicators['销售增长率'] * 0.25 +
                $indicators['利润率'] * 0.2 +
                $indicators['库存准确率'] * 0.15 +
                $indicators['操作效率'] * 0.1
            );
        }

        return view('statistics.health', [
            'stores' => $stores,
            'currentStore' => $currentStore,
            'indicators' => $indicators,
            'totalScore' => $totalScore,
        ]);
    }
} 