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
    public function index(Request $request)
    {
        // 获取时间筛选参数
        $period = $request->input('period', 'today');
        $customRange = $request->input('range');
        
        // 计算时间范围
        $dateRange = $this->calculateDateRange($period, $customRange);
        
        // 检查是否有强制刷新参数
        $forceRefresh = request('refresh') === 'true';
        
        // 使用缓存获取仪表盘数据，缓存时间减少到2分钟
        $cacheKey = 'dashboard_data_' . auth()->id() . '_' . $period . '_' . ($customRange ?? 'default');
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        $dashboardData = Cache::remember($cacheKey, 120, function () use ($dateRange) {
            return $this->getDashboardData($dateRange);
        });

        // 添加时间范围信息到视图
        $dashboardData['dateRange'] = $dateRange;
        $dashboardData['currentPeriod'] = $period;
        $dashboardData['customRange'] = $customRange;
        
        // 添加所有时间范围的日期信息
        $dashboardData['allDateRanges'] = $this->getAllDateRanges();

        return view('dashboard', $dashboardData);
    }

    /**
     * 获取所有时间范围的日期信息
     */
    private function getAllDateRanges()
    {
        $now = now();
        
        return [
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => '今日',
                'display' => $now->format('m-d')
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay(),
                'label' => '昨日',
                'display' => $now->copy()->subDay()->format('m-d')
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
                'label' => '本周',
                'display' => $now->copy()->startOfWeek()->format('m-d') . ' ~ ' . $now->copy()->endOfWeek()->format('m-d')
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
                'label' => '本月',
                'display' => $now->copy()->startOfMonth()->format('m-d') . ' ~ ' . $now->copy()->endOfMonth()->format('m-d')
            ],
            'quarter' => [
                'start' => $now->copy()->startOfQuarter(),
                'end' => $now->copy()->endOfQuarter(),
                'label' => '本季度',
                'display' => $now->copy()->startOfQuarter()->format('m-d') . ' ~ ' . $now->copy()->endOfQuarter()->format('m-d')
            ]
        ];
    }

    /**
     * 计算时间范围
     */
    private function calculateDateRange($period, $customRange = null)
    {
        $now = now();
        
        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'label' => '今日'
                ];
                
            case 'yesterday':
                return [
                    'start' => $now->copy()->subDay()->startOfDay(),
                    'end' => $now->copy()->subDay()->endOfDay(),
                    'label' => '昨日'
                ];
                
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                    'label' => '本周'
                ];
                
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'label' => '本月'
                ];
                
            case 'quarter':
                return [
                    'start' => $now->copy()->startOfQuarter(),
                    'end' => $now->copy()->endOfQuarter(),
                    'label' => '本季度'
                ];
                
            case 'custom':
                if ($customRange) {
                    $dates = explode(' - ', $customRange);
                    if (count($dates) === 2) {
                        return [
                            'start' => Carbon::parse(trim($dates[0]))->startOfDay(),
                            'end' => Carbon::parse(trim($dates[1]))->endOfDay(),
                            'label' => '自定义'
                        ];
                    }
                }
                // 如果自定义日期解析失败，默认返回今天
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'label' => '今日'
                ];
                
            default:
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'label' => '今日'
                ];
        }
    }

    /**
     * 获取仪表盘数据
     */
    private function getDashboardData($dateRange = null)
    {
        try {
            $user = auth()->user();
            $currentStoreId = session('current_store_id');
            $isSuperAdmin = $user->isSuperAdmin();
            
            // 如果没有提供时间范围，使用默认的今天
            if (!$dateRange) {
                $dateRange = $this->calculateDateRange('today');
            }
            
            // 获取销售数据（使用时间范围）
            $salesData = $this->getSalesData($dateRange);

            // 获取库存预警数据
            $lowStockAlerts = $this->getLowStockAlerts();

            // 获取热销商品（使用时间范围）
            $topProducts = $this->getTopProducts($dateRange);

            // 获取仓库销售排行（使用时间范围）
            $storeRanking = $this->getStoreRanking($dateRange);

            // 获取最近活动
            $recentActivities = $this->getRecentActivities();

            // 获取销售趋势数据（使用时间范围）
            $salesTrendData = $this->getSalesTrendData($dateRange);
            
            // 调试信息
            \Log::info('销售趋势数据: ' . json_encode($salesTrendData));

            // 获取当前仓库信息
            $currentStore = null;
            if ($currentStoreId && $currentStoreId != 0) {
                $currentStore = \App\Models\Store::find($currentStoreId);
            }

            return compact(
                'salesData',
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
                'salesData' => (object)[
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
     * 获取销售数据（基于时间范围）
     */
    private function getSalesData($dateRange)
    {
        $currentStoreId = session('current_store_id');
        $user = auth()->user();
        
        $query = DB::table('sales')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            
        if ($currentStoreId && $currentStoreId != 0) {
            $query->where('store_id', $currentStoreId);
        } elseif (!$user->isSuperAdmin()) {
            $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
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
            $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
            $query->whereIn('inventory.store_id', $userStoreIds);
        }
        return $query->limit(5)->get();
    }

    /**
     * 获取热销商品（基于时间范围）
     */
    private function getTopProducts($dateRange = null)
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
            
        // 应用时间范围筛选
        if ($dateRange) {
            $query->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']]);
        }
        
        if ($currentStoreId && $currentStoreId != 0) {
            $query->where('sales.store_id', $currentStoreId);
        } elseif (!$user->isSuperAdmin()) {
            $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
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
            $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
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
     * 获取销售趋势数据（基于时间范围）
     */
    private function getSalesTrendData($dateRange = null)
    {
        try {
            $currentStoreId = session('current_store_id');
            $user = auth()->user();
            
            // 如果没有提供时间范围，默认使用最近7天
            if (!$dateRange) {
                $dateRange = [
                    'start' => now()->subDays(7),
                    'end' => now(),
                    'label' => '最近7天'
                ];
            }
            
            // 调试信息
            \Log::info('开始获取销售趋势数据', [
                'currentStoreId' => $currentStoreId,
                'isSuperAdmin' => $user->isSuperAdmin(),
                'userId' => $user->id,
                'dateRange' => $dateRange
            ]);
            
            $query = DB::table('sales')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('COALESCE(SUM(total_amount), 0) as total_amount')
                )
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                
            if ($currentStoreId && $currentStoreId != 0) {
                $query->where('store_id', $currentStoreId);
                \Log::info('使用当前仓库ID筛选', ['store_id' => $currentStoreId]);
            } elseif (!$user->isSuperAdmin()) {
                $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
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