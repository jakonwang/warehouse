<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ProductSalesTrendController extends Controller
{
    /**
     * 构造方法，添加权限中间件
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canViewReports()) {
                abort(403, '您没有权限访问报表功能');
            }
            return $next($request);
        });
    }

    /**
     * 显示产品销售趋势分析页面
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonths(6)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $storeId = $request->input('store_id') ?? session('current_store_id');
        $productId = $request->input('product_id');
        $limit = $request->input('limit', 20);
        
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        
        // 获取用户可访问的仓库
        $stores = $isSuperAdmin ? Store::all() : $user->getAccessibleStores();
        
        // 获取产品销售趋势数据
        $productQuery = $this->buildBaseSalesQuery($startDate, $endDate, $storeId, $productId, $user);
        $trendData = $this->getProductSalesTrendData($productQuery, $startDate, $endDate, $limit);
        
        // 获取每日销售趋势（重新构建查询）
        $dailyTrend = $this->getDailySalesTrend(null, $startDate, $endDate);
        
        // 获取销售预测数据
        $predictionData = $this->generateSalesPrediction($dailyTrend);
        
        // 获取产品列表（用于筛选）
        $products = $this->getProductsForFilter($user, $storeId);
        
        // 详细调试信息
        \Log::info('ProductSalesTrend:', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'storeId' => $storeId,
            'trendData_count' => $trendData->count(),
            'dailyTrend_count' => $dailyTrend->count(),
            'dailyTrend_sample' => $dailyTrend->take(3)->toArray(),
            'user_id' => $user->id,
            'is_super_admin' => $isSuperAdmin
        ]);
        
        return view('statistics.product-sales-trend', compact(
            'trendData', 'dailyTrend', 'predictionData', 'stores', 'products',
            'startDate', 'endDate', 'storeId', 'productId', 'limit', 'isSuperAdmin'
        ));
    }

    /**
     * 构建基础销售查询
     */
    private function buildBaseSalesQuery($startDate, $endDate, $storeId, $productId, $user)
    {
        // 构建标准商品销售查询（来自sale_details表）
        $standardQuery = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('products.type', 'standard');

        // 构建盲袋发货查询（来自blind_bag_deliveries表）
        $blindBagQuery = DB::table('blind_bag_deliveries')
            ->join('sales', 'blind_bag_deliveries.sale_id', '=', 'sales.id')
            ->join('products', 'blind_bag_deliveries.delivery_product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('products.type', 'standard');

        // 权限控制
        if (!$user->isSuperAdmin()) {
            $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
            $standardQuery->whereIn('sales.store_id', $userStoreIds);
            $blindBagQuery->whereIn('sales.store_id', $userStoreIds);
        }

        // 仓库筛选
        if ($storeId && $storeId != 0) {
            $standardQuery->where('sales.store_id', $storeId);
            $blindBagQuery->where('sales.store_id', $storeId);
        }

        // 产品筛选
        if ($productId) {
            $standardQuery->where('sale_details.product_id', $productId);
            $blindBagQuery->where('blind_bag_deliveries.delivery_product_id', $productId);
        }

        // 合并两个查询结果
        $standardData = $standardQuery->select(
            'products.id as product_id',
            'products.name as product_name',
            'products.code as product_code',
            'products.image as product_image',
            DB::raw('SUM(sale_details.quantity) as total_quantity'),
            DB::raw('SUM(sale_details.total) as total_amount'),
            DB::raw('COUNT(DISTINCT sales.id) as order_count'),
            DB::raw('AVG(sale_details.price) as avg_price'),
            DB::raw('COUNT(DISTINCT DATE(sales.created_at)) as active_days')
        )
        ->groupBy('products.id', 'products.name', 'products.code', 'products.image')
        ->get();

        $blindBagData = $blindBagQuery->select(
            'products.id as product_id',
            'products.name as product_name',
            'products.code as product_code',
            'products.image as product_image',
            DB::raw('SUM(blind_bag_deliveries.quantity) as total_quantity'),
            DB::raw('SUM(blind_bag_deliveries.total_cost) as total_amount'),
            DB::raw('COUNT(DISTINCT sales.id) as order_count'),
            DB::raw('AVG(blind_bag_deliveries.unit_cost) as avg_price'),
            DB::raw('COUNT(DISTINCT DATE(sales.created_at)) as active_days')
        )
        ->groupBy('products.id', 'products.name', 'products.code', 'products.image')
        ->get();

        // 合并数据
        $combinedData = collect();
        
        // 处理标准商品销售数据
        foreach ($standardData as $item) {
            $combinedData->put($item->product_id, $item);
        }
        
        // 处理盲袋发货数据，累加到现有数据或创建新记录
        foreach ($blindBagData as $item) {
            if ($combinedData->has($item->product_id)) {
                // 累加到现有记录
                $existing = $combinedData->get($item->product_id);
                $existing->total_quantity += $item->total_quantity;
                $existing->total_amount += $item->total_amount;
                $existing->order_count += $item->order_count;
                $existing->active_days = max($existing->active_days, $item->active_days);
                // 重新计算平均价格
                $existing->avg_price = ($existing->avg_price + $item->avg_price) / 2;
            } else {
                // 创建新记录
                $combinedData->put($item->product_id, $item);
            }
        }

        return $combinedData->values();
    }

    /**
     * 获取产品销售趋势数据
     */
    private function getProductSalesTrendData($data, $startDate, $endDate, $limit)
    {
        return $data->map(function ($item) use ($startDate, $endDate) {
            // 使用实际有销售记录的天数计算平均日销量
            $activeDays = max($item->active_days, 1); // 至少为1天，避免除零错误
            $item->avg_daily_sales = round($item->total_quantity / $activeDays, 2);
            $item->sales_frequency = $item->active_days > 0 ? round($item->active_days / (Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1) * 100, 1) : 0;
            
            // 计算建议备货量：日均销量 * 天数 * 1.5
            $item->suggested_7day_stock = round($item->avg_daily_sales * 7 * 1.5, 0);
            $item->suggested_15day_stock = round($item->avg_daily_sales * 15 * 1.5, 0);
            
            // 计算趋势（与上一周期对比）
            $item->trend = $this->calculateTrend($item->product_id, $startDate, $endDate);
            
            return $item;
        })
        ->sortByDesc('total_quantity')
        ->take($limit);
    }

    /**
     * 获取每日销售趋势
     */
    private function getDailySalesTrend($query, $startDate, $endDate)
    {
        $user = auth()->user();
        $storeId = session('current_store_id');
        
        // 获取标准商品销售数据
        $standardData = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('products.type', 'standard');

        // 获取盲袋发货数据
        $blindBagData = DB::table('blind_bag_deliveries')
            ->join('sales', 'blind_bag_deliveries.sale_id', '=', 'sales.id')
            ->join('products', 'blind_bag_deliveries.delivery_product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('products.type', 'standard');

        // 权限控制
        if (!$user->isSuperAdmin()) {
            $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
            $standardData->whereIn('sales.store_id', $userStoreIds);
            $blindBagData->whereIn('sales.store_id', $userStoreIds);
        }

        // 仓库筛选
        if ($storeId && $storeId != 0) {
            $standardData->where('sales.store_id', $storeId);
            $blindBagData->where('sales.store_id', $storeId);
        }

        // 执行查询
        $standardData = $standardData->select(
            DB::raw('DATE(sales.created_at) as sale_date'),
            DB::raw('SUM(sale_details.quantity) as daily_quantity'),
            DB::raw('SUM(sale_details.total) as daily_amount'),
            DB::raw('COUNT(DISTINCT sale_details.product_id) as product_types')
        )
        ->groupBy('sale_date')
        ->get();

        $blindBagData = $blindBagData->select(
            DB::raw('DATE(sales.created_at) as sale_date'),
            DB::raw('SUM(blind_bag_deliveries.quantity) as daily_quantity'),
            DB::raw('SUM(blind_bag_deliveries.total_cost) as daily_amount'),
            DB::raw('COUNT(DISTINCT blind_bag_deliveries.delivery_product_id) as product_types')
        )
        ->groupBy('sale_date')
        ->get();

        // 合并数据
        $combinedData = collect();
        
        // 处理标准商品销售数据
        foreach ($standardData as $item) {
            $combinedData->put($item->sale_date, (object) [
                'sale_date' => $item->sale_date,
                'daily_quantity' => $item->daily_quantity,
                'daily_amount' => $item->daily_amount,
                'product_types' => $item->product_types
            ]);
        }
        
        // 处理盲袋发货数据，累加到现有数据或创建新记录
        foreach ($blindBagData as $item) {
            if ($combinedData->has($item->sale_date)) {
                // 累加到现有记录
                $existing = $combinedData->get($item->sale_date);
                $existing->daily_quantity += $item->daily_quantity;
                $existing->daily_amount += $item->daily_amount;
                $existing->product_types += $item->product_types;
            } else {
                // 创建新记录
                $combinedData->put($item->sale_date, (object) [
                    'sale_date' => $item->sale_date,
                    'daily_quantity' => $item->daily_quantity,
                    'daily_amount' => $item->daily_amount,
                    'product_types' => $item->product_types
                ]);
            }
        }

        return $combinedData->values()
            ->sortBy('sale_date')
            ->map(function ($item) {
                $item->formatted_date = Carbon::parse($item->sale_date)->format('m-d');
                return $item;
            });
    }

    /**
     * 计算产品销售趋势
     */
    private function calculateTrend($productId, $startDate, $endDate)
    {
        $currentPeriodStart = Carbon::parse($startDate);
        $currentPeriodEnd = Carbon::parse($endDate);
        $days = $currentPeriodStart->diffInDays($currentPeriodEnd) + 1;
        
        // 上一周期的开始和结束日期
        $previousPeriodEnd = $currentPeriodStart->copy()->subDay();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays($days - 1);

        // 当前周期销量
        $currentSales = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->where('sale_details.product_id', $productId)
            ->whereBetween('sales.created_at', [$currentPeriodStart->startOfDay(), $currentPeriodEnd->endOfDay()])
            ->sum('sale_details.quantity');

        // 上一周期销量
        $previousSales = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->where('sale_details.product_id', $productId)
            ->whereBetween('sales.created_at', [$previousPeriodStart->startOfDay(), $previousPeriodEnd->endOfDay()])
            ->sum('sale_details.quantity');

        if ($previousSales == 0) {
            return $currentSales > 0 ? 100 : 0; // 新产品或无历史数据
        }

        return round((($currentSales - $previousSales) / $previousSales) * 100, 1);
    }

    /**
     * 生成销售预测数据
     */
    private function generateSalesPrediction($dailyTrend)
    {
        if ($dailyTrend->count() < 7) {
            return collect(); // 数据不足，无法预测
        }

        $data = $dailyTrend->pluck('daily_quantity')->toArray();
        $days = count($data);
        
        // 简单线性回归预测接下来7天
        $predictions = collect();
        
        // 计算移动平均
        $movingAvg = $this->calculateMovingAverage($data, 7);
        
        // 计算趋势斜率
        $slope = $this->calculateTrendSlope($data);
        
        for ($i = 1; $i <= 7; $i++) {
            $baseValue = end($movingAvg);
            $trendValue = $baseValue + ($slope * $i);
            
            // 添加季节性调整（周末效应）
            $dayOfWeek = Carbon::now()->addDays($i)->dayOfWeek;
            $seasonalFactor = in_array($dayOfWeek, [0, 6]) ? 0.8 : 1.1; // 周末销量较低
            
            $prediction = max(0, round($trendValue * $seasonalFactor));
            
            $predictions->push([
                'date' => Carbon::now()->addDays($i)->format('m-d'),
                'predicted_quantity' => $prediction,
                'confidence' => max(60, 100 - ($i * 5)) // 置信度随时间递减
            ]);
        }

        return $predictions;
    }

    /**
     * 计算移动平均
     */
    private function calculateMovingAverage($data, $period)
    {
        $result = [];
        for ($i = $period - 1; $i < count($data); $i++) {
            $sum = array_sum(array_slice($data, $i - $period + 1, $period));
            $result[] = $sum / $period;
        }
        return $result;
    }

    /**
     * 计算趋势斜率
     */
    private function calculateTrendSlope($data)
    {
        $n = count($data);
        if ($n < 2) return 0;
        
        $sumX = array_sum(range(0, $n - 1));
        $sumY = array_sum($data);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $i * $data[$i];
            $sumX2 += $i * $i;
        }
        
        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        if ($denominator == 0) return 0;
        
        return (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
    }

    /**
     * 获取用于筛选的产品列表
     */
    private function getProductsForFilter($user, $storeId = null)
    {
        // 获取最近30天有销售记录的产品
        $query = Product::whereExists(function ($query) use ($user, $storeId) {
            $query->select(DB::raw(1))
                ->from('sale_details')
                ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                ->whereColumn('sale_details.product_id', 'products.id')
                ->where('sales.created_at', '>=', now()->subDays(30));
                
            if (!$user->isSuperAdmin()) {
                $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
                $query->whereIn('sales.store_id', $userStoreIds);
            }
            
            if ($storeId && $storeId != 0) {
                $query->where('sales.store_id', $storeId);
            }
        });

        return $query->where('type', 'standard')  // 只显示标准商品
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();
    }

    /**
     * 导出产品销售趋势数据
     */
    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonths(6)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $storeId = $request->input('store_id');
        $productId = $request->input('product_id');
        
        $user = auth()->user();
        $query = $this->buildBaseSalesQuery($startDate, $endDate, $storeId, $productId, $user);
        $data = $this->getProductSalesTrendData($query, $startDate, $endDate, 1000);

        $filename = 'product_sales_trend_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ];

        return Response::stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            
            // 添加 BOM 以支持 Excel 正确显示中文
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // 写入表头
            fputcsv($handle, [
                '产品ID', '产品名称', '产品编码', '总销量', '总金额', '订单数',
                '平均单价', '平均日销量', '建议7天备货量', '建议15天备货量', '销售频率(%)', '趋势(%)'
            ]);
            
            // 写入数据
            foreach ($data as $item) {
                fputcsv($handle, [
                    $item->product_id,
                    $item->product_name,
                    $item->product_code,
                    $item->total_quantity,
                    number_format($item->total_amount, 2),
                    $item->order_count,
                    number_format($item->avg_price, 2),
                    $item->avg_daily_sales,
                    $item->suggested_7day_stock,
                    $item->suggested_15day_stock,
                    $item->sales_frequency . '%',
                    ($item->trend > 0 ? '+' : '') . $item->trend . '%'
                ]);
            }
            
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * 获取产品详细销售趋势（API接口）
     */
    public function getProductDetailTrend(Request $request)
    {
        $productId = $request->input('product_id');
        $days = $request->input('days', 30);
        
        if (!$productId) {
            return response()->json(['error' => '产品ID不能为空'], 400);
        }

        // 验证产品是否为标准商品
        $product = Product::find($productId);
        if (!$product || $product->type !== 'standard') {
            return response()->json(['error' => '只支持查看标准商品的详情'], 400);
        }

        $endDate = now();
        $startDate = $endDate->copy()->subDays($days - 1);
        
        // 获取标准商品销售数据
        $standardData = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sale_details.product_id', $productId)
            ->where('products.type', 'standard')
            ->whereBetween('sales.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->select(
                DB::raw('DATE(sales.created_at) as sale_date'),
                DB::raw('SUM(sale_details.quantity) as quantity'),
                DB::raw('SUM(sale_details.total) as amount')
            )
            ->groupBy('sale_date')
            ->get();

        // 获取盲袋发货数据
        $blindBagData = DB::table('blind_bag_deliveries')
            ->join('sales', 'blind_bag_deliveries.sale_id', '=', 'sales.id')
            ->join('products', 'blind_bag_deliveries.delivery_product_id', '=', 'products.id')
            ->where('blind_bag_deliveries.delivery_product_id', $productId)
            ->where('products.type', 'standard')
            ->whereBetween('sales.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->select(
                DB::raw('DATE(sales.created_at) as sale_date'),
                DB::raw('SUM(blind_bag_deliveries.quantity) as quantity'),
                DB::raw('SUM(blind_bag_deliveries.total_cost) as amount')
            )
            ->groupBy('sale_date')
            ->get();

        // 合并数据
        $combinedData = collect();
        
        // 处理标准商品销售数据
        foreach ($standardData as $item) {
            $combinedData->put($item->sale_date, $item);
        }
        
        // 处理盲袋发货数据，累加到现有数据或创建新记录
        foreach ($blindBagData as $item) {
            if ($combinedData->has($item->sale_date)) {
                // 累加到现有记录
                $existing = $combinedData->get($item->sale_date);
                $existing->quantity += $item->quantity;
                $existing->amount += $item->amount;
            } else {
                // 创建新记录
                $combinedData->put($item->sale_date, $item);
            }
        }

        // 转换为API响应格式
        $data = $combinedData->values()
            ->sortBy('sale_date')
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->sale_date)->format('Y-m-d'),
                    'quantity' => (int)$item->quantity,
                    'amount' => (float)$item->amount
                ];
            });

        return response()->json($data);
    }
} 