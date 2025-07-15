<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Activity;
use App\Models\PriceSeriesCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * 显示仪表盘首页
     */
    public function index()
    {
        // 使用缓存获取仪表盘数据，缓存时间增加到10分钟
        $dashboardData = Cache::remember('dashboard_data_' . auth()->id(), 600, function () {
            return $this->getDashboardData();
        });

        return view('dashboard', $dashboardData);
    }

    /**
     * 获取仪表盘数据
     */
    private function getDashboardData()
    {
        try {
            $user = auth()->user();
            $currentStoreId = session('current_store_id');
            $isSuperAdmin = $user->isSuperAdmin();
            
            // 获取今日销售数据
            $todaySales = $this->getTodaySalesData();

            // 获取库存预警数据
            $lowStockAlerts = $this->getLowStockAlerts();

            // 获取热销商品
            $topProducts = $this->getTopProducts();

            // 获取仓库销售排行
            $storeRanking = $this->getStoreRanking();

            // 获取最近活动
            $recentActivities = $this->getRecentActivities();

            // 获取销售趋势数据
            $salesTrendData = $this->getSalesTrendData();
            
            // 调试信息
            \Log::info('销售趋势数据: ' . json_encode($salesTrendData));

            // 获取当前仓库信息
            $currentStore = null;
            if ($currentStoreId && $currentStoreId != 0) {
                $currentStore = \App\Models\Store::find($currentStoreId);
            }

            return compact(
                'todaySales',
                'lowStockAlerts', 
                'topProducts',
                'storeRanking',
                'recentActivities',
                'salesTrendData',
                'isSuperAdmin',
                'currentStore'
            );
        } catch (\Exception $e) {
            // 记录错误日志
            \Log::error('仪表盘数据获取失败: ' . $e->getMessage());
            
            // 获取用户信息用于异常情况
            $user = auth()->user();
            $currentStoreId = session('current_store_id');
            $isSuperAdmin = $user ? $user->isSuperAdmin() : false;
            $currentStore = null;
            if ($currentStoreId && $currentStoreId != 0) {
                $currentStore = \App\Models\Store::find($currentStoreId);
            }
            
            // 返回默认数据
            return [
                'todaySales' => (object)[
                    'total_sales' => 0,
                    'total_amount' => 0,
                    'total_profit' => 0,
                    'avg_profit_rate' => 0
                ],
                'lowStockAlerts' => collect(),
                'topProducts' => collect(),
                'storeRanking' => collect(),
                'recentActivities' => collect(),
                'salesTrendData' => [
                    'dates' => [],
                    'amounts' => [],
                    'counts' => []
                ],
                'isSuperAdmin' => $isSuperAdmin,
                'currentStore' => $currentStore
            ];
        }
    }

    /**
     * 获取今日销售数据
     */
    private function getTodaySalesData()
    {
        $currentStoreId = session('current_store_id');
        $user = auth()->user();
        $query = DB::table('sales')->whereDate('created_at', today());
        if ($currentStoreId && $currentStoreId != 0) {
            $query->where('store_id', $currentStoreId);
        } elseif (!$user->isSuperAdmin()) {
            $userStoreIds = $user->stores()->pluck('stores.id')->toArray();
            $query->whereIn('store_id', $userStoreIds);
        }
        $result = $query->selectRaw('
            COUNT(*) as total_sales,
            COALESCE(SUM(total_amount), 0) as total_amount,
            COALESCE(SUM(total_profit), 0) as total_profit,
            COALESCE(AVG(profit_rate), 0) as avg_profit_rate
        ')->first();

        // 防御性处理，保证返回对象有所有属性
        if (!$result) {
            $result = (object) [
                'total_sales' => 0,
                'total_amount' => 0,
                'total_profit' => 0,
                'avg_profit_rate' => 0
            ];
        } else {
            // 补全缺失属性
            foreach ([
                'total_sales' => 0,
                'total_amount' => 0,
                'total_profit' => 0,
                'avg_profit_rate' => 0
            ] as $key => $default) {
                if (!property_exists($result, $key)) {
                    $result->$key = $default;
                }
            }
        }
        return $result;
    }

    /**
     * 获取库存预警数据
     */
    private function getLowStockAlerts()
    {
        $currentStoreId = session('current_store_id');
        $user = auth()->user();
        $query = DB::table('inventory')
            ->leftJoin('products', 'inventory.product_id', '=', 'products.id')
            ->select(
                'inventory.*',
                'products.name as product_name',
                'products.code as product_code',
                'products.image as product_image'
            )
            ->where('inventory.quantity', '<=', DB::raw('inventory.min_quantity'))
            ->where('products.type', 'standard')
            ->where('products.is_active', true);
        if ($currentStoreId && $currentStoreId != 0) {
            $query->where('inventory.store_id', $currentStoreId);
        } elseif (!$user->isSuperAdmin()) {
            $userStoreIds = $user->stores()->pluck('stores.id')->toArray();
            $query->whereIn('inventory.store_id', $userStoreIds);
        }
        return $query->limit(5)->get();
    }

    /**
     * 获取热销商品
     */
    private function getTopProducts()
    {
        $currentStoreId = session('current_store_id');
        $user = auth()->user();
        $query = DB::table('products')
            ->leftJoin('sale_details', 'products.id', '=', 'sale_details.product_id')
            ->leftJoin('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->select(
                'products.*',
                DB::raw('COALESCE(SUM(sale_details.quantity), 0) as total_quantity')
            )
            ->where('products.is_active', true)
            ->where('products.type', 'standard');
        if ($currentStoreId && $currentStoreId != 0) {
            $query->where('sales.store_id', $currentStoreId);
        } elseif (!$user->isSuperAdmin()) {
            $userStoreIds = $user->stores()->pluck('stores.id')->toArray();
            $query->whereIn('sales.store_id', $userStoreIds);
        }
        return $query->groupBy('products.id', 'products.name', 'products.code', 'products.description', 'products.price', 'products.cost_price', 'products.image', 'products.type', 'products.is_active', 'products.created_at', 'products.updated_at')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * 获取仓库销售排行
     */
    private function getStoreRanking()
    {
        $currentStoreId = session('current_store_id');
        $user = auth()->user();
        $query = DB::table('stores')
            ->leftJoin('sales', 'stores.id', '=', 'sales.store_id')
            ->select(
                'stores.*',
                DB::raw('COALESCE(SUM(sales.total_amount), 0) as total_sales')
            )
            ->where('stores.is_active', true);
        if ($currentStoreId && $currentStoreId != 0) {
            $query->where('stores.id', $currentStoreId);
        } elseif (!$user->isSuperAdmin()) {
            $userStoreIds = $user->stores()->pluck('stores.id')->toArray();
            $query->whereIn('stores.id', $userStoreIds);
        }
        return $query->groupBy('stores.id', 'stores.name', 'stores.address', 'stores.phone', 'stores.manager', 'stores.is_active', 'stores.created_at', 'stores.updated_at')
            ->orderBy('total_sales', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * 获取最近活动
     */
    private function getRecentActivities()
    {
        $activities = DB::table('activities')
            ->leftJoin('users', 'activities.user_id', '=', 'users.id')
            ->select(
                'activities.*',
                'users.real_name as user_name'
            )
            ->orderBy('activities.created_at', 'desc')
            ->limit(4)
            ->get();

        // 将 created_at 转换为 Carbon 实例
        return $activities->map(function ($activity) {
            $activity->created_at = Carbon::parse($activity->created_at);
            return $activity;
        });
    }

    /**
     * 获取库存图表数据
     */
    private function getInventoryChartData()
    {
        try {
            $data = DB::table('inventory')
                ->leftJoin('products', 'inventory.product_id', '=', 'products.id')
                ->select(
                    'products.name',
                    DB::raw('SUM(inventory.quantity) as total_quantity')
                )
                ->where('products.is_active', true)
                ->groupBy('products.id', 'products.name')
                ->orderBy('total_quantity', 'desc')
                ->limit(10)
                ->get();

            return [
                'labels' => $data->pluck('name')->toArray(),
                'data' => $data->pluck('total_quantity')->toArray()
            ];
        } catch (\Exception $e) {
            return ['labels' => [], 'data' => []];
        }
    }

    /**
     * 获取销售趋势数据
     */
    private function getSalesTrendData()
    {
        try {
            $currentStoreId = session('current_store_id');
            $user = auth()->user();
            
            // 调试信息
            \Log::info('开始获取销售趋势数据', [
                'currentStoreId' => $currentStoreId,
                'isSuperAdmin' => $user->isSuperAdmin(),
                'userId' => $user->id
            ]);
            
            $query = DB::table('sales')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('COALESCE(SUM(total_amount), 0) as total_amount')
                )
                ->whereBetween('created_at', [now()->subDays(7), now()]);
                
            if ($currentStoreId && $currentStoreId != 0) {
                $query->where('store_id', $currentStoreId);
                \Log::info('使用当前仓库ID筛选', ['store_id' => $currentStoreId]);
            } elseif (!$user->isSuperAdmin()) {
                $userStoreIds = $user->stores()->pluck('stores.id')->toArray();
                $query->whereIn('store_id', $userStoreIds);
                \Log::info('使用用户仓库ID筛选', ['userStoreIds' => $userStoreIds]);
            }
            
            $data = $query->groupBy('date')
                ->orderBy('date')
                ->get();

            // 调试信息
            \Log::info('原始销售数据查询结果', [
                'data_count' => $data->count(),
                'data' => $data->toArray()
            ]);

            // 填充缺失的日期
            $dates = [];
            $amounts = [];
            $counts = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dates[] = $date;
                
                $dayData = $data->where('date', $date)->first();
                $amounts[] = $dayData ? (float)$dayData->total_amount : 0;
                $counts[] = $dayData ? (int)$dayData->count : 0;
            }

            $result = [
                'dates' => $dates,
                'amounts' => $amounts,
                'counts' => $counts
            ];

            // 调试信息
            \Log::info('处理后的销售趋势数据', $result);

            return $result;
        } catch (\Exception $e) {
            \Log::error('获取销售趋势数据失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // 返回默认数据
            $dates = [];
            $amounts = [];
            $counts = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dates[] = $date;
                $amounts[] = 0;
                $counts[] = 0;
            }
            
            return [
                'dates' => $dates,
                'amounts' => $amounts,
                'counts' => $counts
            ];
        }
    }

    /**
     * 获取销售图表数据
     */
    private function getSalesChartData()
    {
        try {
            $data = DB::table('sales')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(total_amount) as total_amount')
                )
                ->whereBetween('created_at', [now()->subDays(30), now()])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return [
                'labels' => $data->pluck('date')->toArray(),
                'counts' => $data->pluck('count')->toArray(),
                'amounts' => $data->pluck('total_amount')->toArray()
            ];
        } catch (\Exception $e) {
            return ['labels' => [], 'counts' => [], 'amounts' => []];
        }
    }
} 