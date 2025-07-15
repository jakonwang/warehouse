<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Sale;
use App\Models\StockIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryTurnoverController extends Controller
{
    /**
     * 显示库存周转率统计页面
     */
    public function index(Request $request)
    {
        try {
            // 获取时间范围
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now()->endOfMonth());

            // 计算库存周转率
            $turnoverStats = $this->calculateTurnoverRate($startDate, $endDate);

            // 获取库存状态
            $inventoryStatus = $this->getInventoryStatus();

            // 获取库存变动趋势
            $inventoryTrend = $this->getInventoryTrend($startDate, $endDate);

            // 获取库存预警商品
            $alertProducts = $this->getAlertProducts();

            return view('statistics.inventory-turnover', compact(
                'turnoverStats',
                'inventoryStatus',
                'inventoryTrend',
                'alertProducts',
                'startDate',
                'endDate'
            ));
        } catch (\Exception $e) {
            // 返回错误信息用于调试
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * 测试方法 - 用于调试
     */
    public function test()
    {
        try {
            // 测试基本查询
            $inventoryCount = Inventory::count();
            $productsCount = DB::table('products')->count();
            $saleDetailsCount = DB::table('sale_details')->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'inventory_count' => $inventoryCount,
                    'products_count' => $productsCount,
                    'sale_details_count' => $saleDetailsCount,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * 计算库存周转率
     */
    private function calculateTurnoverRate($startDate, $endDate)
    {
        try {
            // 计算销售成本 - 使用销售明细表中的数据
            $costOfGoodsSold = DB::table('sale_details')
                ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                ->whereBetween('sales.created_at', [$startDate, $endDate])
                ->sum(DB::raw('sale_details.quantity * sale_details.cost_price'));

            // 计算平均库存成本 - 通过关联Product表获取成本价格
            $averageInventory = Inventory::join('products', 'inventory.product_id', '=', 'products.id')
                ->avg(DB::raw('inventory.quantity * products.cost_price'));

            // 计算周转率
            $turnoverRate = $averageInventory > 0 ? $costOfGoodsSold / $averageInventory : 0;

            // 计算周转天数
            $daysInPeriod = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
            $turnoverDays = $turnoverRate > 0 ? $daysInPeriod / $turnoverRate : 0;

            return [
                'cost_of_goods_sold' => $costOfGoodsSold,
                'average_inventory' => $averageInventory,
                'turnover_rate' => $turnoverRate,
                'turnover_days' => $turnoverDays
            ];
        } catch (\Exception $e) {
            // 返回默认值
            return [
                'cost_of_goods_sold' => 0,
                'average_inventory' => 0,
                'turnover_rate' => 0,
                'turnover_days' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 获取库存状态
     */
    private function getInventoryStatus()
    {
        try {
            // 获取用户有权限的仓库
            $userStores = auth()->user()->stores()->pluck('stores.id');
            
            return [
                'total_products' => Inventory::whereIn('store_id', $userStores)->count(),
                'total_quantity' => Inventory::whereIn('store_id', $userStores)->sum('quantity'),
                'total_value' => Inventory::join('products', 'inventory.product_id', '=', 'products.id')
                    ->whereIn('inventory.store_id', $userStores)
                    ->sum(DB::raw('inventory.quantity * products.price')),
                'low_stock' => Inventory::whereIn('store_id', $userStores)
                    ->where('quantity', '<', DB::raw('min_quantity'))
                    ->count(),
                'overstock' => Inventory::whereIn('store_id', $userStores)
                    ->where('quantity', '>', DB::raw('max_quantity'))
                    ->count()
            ];
        } catch (\Exception $e) {
            return [
                'total_products' => 0,
                'total_quantity' => 0,
                'total_value' => 0,
                'low_stock' => 0,
                'overstock' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 获取库存变动趋势
     */
    private function getInventoryTrend($startDate, $endDate)
    {
        try {
            // 暂时移除权限检查
            // $userStores = auth()->user()->stores()->pluck('stores.id');
            
            // 获取每日库存变动 - 使用库存记录表
            $dailyChanges = DB::table('inventory_records')
                ->join('inventory', 'inventory_records.inventory_id', '=', 'inventory.id')
                ->select(
                    DB::raw('DATE(inventory_records.created_at) as date'),
                    DB::raw('SUM(inventory_records.quantity) as change_quantity')
                )
                // ->whereIn('inventory.store_id', $userStores)
                ->whereBetween('inventory_records.created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // 计算累计库存
            $runningTotal = 0;
            $trend = collect();
            
            foreach ($dailyChanges as $change) {
                $runningTotal += $change->change_quantity;
                $trend->push([
                    'date' => $change->date,
                    'quantity' => $runningTotal
                ]);
            }

            return $trend;
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * 获取库存预警商品
     */
    private function getAlertProducts()
    {
        try {
            // 使用 DB 查询替代 Eloquent 关系查询
            $alertProducts = DB::table('inventory')
                ->leftJoin('products', 'inventory.product_id', '=', 'products.id')
                ->select(
                    'inventory.*',
                    'products.name as product_name',
                    'products.code as product_code'
                )
                ->where(function($query) {
                    $query->where('inventory.quantity', '<', DB::raw('inventory.min_quantity'))
                          ->orWhere('inventory.quantity', '>', DB::raw('inventory.max_quantity'));
                })
                ->orderBy(DB::raw('ABS(inventory.quantity - inventory.min_quantity)'))
                ->limit(10)
                ->get()
                ->map(function ($inventory) {
                    return [
                        'product' => $inventory->product_name ?? '未知商品',
                        'current_quantity' => $inventory->quantity,
                        'min_quantity' => $inventory->min_quantity,
                        'max_quantity' => $inventory->max_quantity,
                        'status' => $inventory->quantity < $inventory->min_quantity ? 'low' : 'over'
                    ];
                });

            return $alertProducts;
        } catch (\Exception $e) {
            return collect();
        }
    }
} 