<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryCheckRecord;
use App\Models\ReturnRecord;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartDataController extends Controller
{
    /**
     * 获取销售趋势数据
     */
    public function salesTrend(Request $request)
    {
        $period = $request->get('period', 'month'); // month, week, year
        $startDate = now()->subMonths(6);
        $endDate = now();

        if ($period === 'week') {
            $startDate = now()->subWeeks(4);
            $format = '%Y-%u'; // Year-Week
            $labels = [];
            for ($i = 3; $i >= 0; $i--) {
                $labels[] = '第' . now()->subWeeks($i)->weekOfMonth . '周';
            }
        } elseif ($period === 'year') {
            $startDate = now()->subYears(2);
            $format = '%Y';
            $labels = [];
            for ($i = 1; $i >= 0; $i--) {
                $labels[] = now()->subYears($i)->year . '年';
            }
        } else {
            $format = '%Y-%m';
            $labels = [];
            for ($i = 5; $i >= 0; $i--) {
                $labels[] = now()->subMonths($i)->format('n月');
            }
        }

        $salesData = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(sale_date, '$format') as period"),
                DB::raw('SUM(total_amount) as sales'),
                DB::raw('SUM(total_profit) as profits')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $sales = [];
        $profits = [];
        $periodKeys = [];

        if ($period === 'week') {
            for ($i = 3; $i >= 0; $i--) {
                $key = now()->subWeeks($i)->format('Y-W');
                $periodKeys[] = $key;
            }
        } elseif ($period === 'year') {
            for ($i = 1; $i >= 0; $i--) {
                $key = now()->subYears($i)->format('Y');
                $periodKeys[] = $key;
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $key = now()->subMonths($i)->format('Y-m');
                $periodKeys[] = $key;
            }
        }

        foreach ($periodKeys as $key) {
            $sales[] = $salesData->get($key)?->sales ?? 0;
            $profits[] = $salesData->get($key)?->profits ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'sales' => $sales,
            'profits' => $profits
        ]);
    }

    /**
     * 获取库存分布数据
     */
    public function inventoryDistribution()
    {
        // 简化查询，避免 Eloquent 关联
        $inventoryData = DB::table('products as p')
            ->leftJoin('inventory as i', 'i.product_id', '=', 'p.id')
            ->select('p.name', 'p.price', DB::raw('COALESCE(SUM(i.quantity), 0) as total_quantity'))
            ->groupBy('p.id', 'p.name', 'p.price')
            ->having('total_quantity', '>', 0)
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'quantity' => $item->total_quantity,
                    'price' => $item->price
                ];
            });

        $labels = [];
        $values = [];

        foreach ($inventoryData as $item) {
            $labels[] = $item['name'];
            $values[] = $item['quantity'];
        }

        // 如果没有足够的数据，添加默认标签
        if (count($labels) < 5) {
            $defaultLabels = ['商品A', '商品B', '商品C', '商品D', '其他'];
            $defaultValues = [100, 80, 60, 40, 20];
            
            for ($i = count($labels); $i < 5; $i++) {
                $labels[] = $defaultLabels[$i] ?? '其他';
                $values[] = $defaultValues[$i] ?? 0;
            }
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }

    /**
     * 获取退货原因数据
     */
    public function returnReasons()
    {
        $returnData = ReturnRecord::select('reason', DB::raw('COUNT(*) as count'))
            ->groupBy('reason')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $labels = [];
        $values = [];

        foreach ($returnData as $item) {
            $reasonMap = [
                'quality' => '质量问题',
                'damage' => '商品损坏',
                'wrong_item' => '发错商品',
                'customer' => '客户原因',
                'other' => '其他'
            ];
            
            $labels[] = $reasonMap[$item->reason] ?? $item->reason;
            $values[] = $item->count;
        }

        // 如果没有数据，使用默认数据
        if (empty($labels)) {
            $labels = ['暂无退货记录'];
            $values = [0];
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }

    /**
     * 获取盘点频率数据
     */
    public function inventoryCheckFrequency()
    {
        $startDate = now()->subWeeks(4);
        $labels = [];
        $values = [];

        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $count = InventoryCheckRecord::whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
            
            $labels[] = '第' . (4 - $i) . '周';
            $values[] = $count;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }

    /**
     * 获取多仓库对比数据
     */
    public function storeComparison()
    {
        // 简化查询，避免 Eloquent 关联
        $stores = DB::table('stores')->where('is_active', true)->select('id', 'name')->get();
        $labels = [];
        $sales = [];
        $profits = [];

        foreach ($stores as $store) {
            $storeSales = DB::table('sales')
                ->where('store_id', $store->id)
                ->whereBetween('sale_date', [now()->subMonth(), now()])
                ->select(
                    DB::raw('COALESCE(SUM(total_amount), 0) as total_sales'),
                    DB::raw('COALESCE(SUM(total_profit), 0) as total_profits')
                )
                ->first();

            $labels[] = $store->name;
            $sales[] = $storeSales->total_sales ?? 0;
            $profits[] = $storeSales->total_profits ?? 0;
        }

        // 如果没有仓库数据，使用默认数据
        if (empty($labels)) {
            $labels = ['暂无仓库数据'];
            $sales = [0];
            $profits = [0];
        }

        return response()->json([
            'labels' => $labels,
            'sales' => $sales,
            'profits' => $profits
        ]);
    }
} 