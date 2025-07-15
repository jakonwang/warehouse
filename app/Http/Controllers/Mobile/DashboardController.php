<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Inventory;
use App\Models\StockInRecord;
use App\Models\ReturnRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * 移动端首页
     */
    public function index()
    {
        $user = Auth::user();
        
        // 今日数据统计
        $today = Carbon::today();
        $todaySales = Sale::whereDate('created_at', $today)->count();
        $todayRevenue = Sale::whereDate('created_at', $today)->sum('total_amount') ?? 0;
        
        // 商品总数
        $totalProducts = \App\Models\Product::count();
        
        // 库存预警
        $lowStockCount = Inventory::where('quantity', '<=', 'min_quantity')->count();
        
        // 最近活动记录
        $recentActivities = collect();
        
        try {
            // 获取最近的销售记录
            $recentSales = Sale::latest()->limit(3)->get();
            foreach ($recentSales as $sale) {
                $recentActivities->push((object)[
                    'description' => '销售记录：' . ($sale->customer_name ?? '匿名客户') . ' - ¥' . number_format($sale->total_amount ?? 0, 2),
                    'icon' => 'cart3',
                    'created_at' => $sale->created_at instanceof Carbon ? $sale->created_at : Carbon::parse($sale->created_at),
                ]);
            }
            
            // 获取最近的退货记录
            $recentReturns = ReturnRecord::latest()->limit(2)->get();
            foreach ($recentReturns as $return) {
                $recentActivities->push((object)[
                    'description' => '退货记录：' . ($return->customer_name ?? '匿名客户') . ' - ¥' . number_format($return->total_amount ?? 0, 2),
                    'icon' => 'arrow-return-left',
                    'created_at' => $return->created_at instanceof Carbon ? $return->created_at : Carbon::parse($return->created_at),
                ]);
            }
            
            // 获取最近的入库记录
            $recentStockIns = DB::table('stock_in_records')
                ->leftJoin('users', 'stock_in_records.user_id', '=', 'users.id')
                ->select(
                    'stock_in_records.*',
                    'users.real_name as user_name'
                )
                ->orderBy('stock_in_records.created_at', 'desc')
                ->limit(2)
                ->get();

            foreach ($recentStockIns as $stockIn) {
                // 获取入库详情
                $stockInDetails = DB::table('stock_in_details')
                    ->leftJoin('products', 'stock_in_details.product_id', '=', 'products.id')
                    ->select(
                        'stock_in_details.*',
                        'products.name as product_name'
                    )
                    ->where('stock_in_details.stock_in_record_id', $stockIn->id)
                    ->get();

                $productNames = [];
                foreach ($stockInDetails as $detail) {
                    if ($detail->product_name) {
                        $productNames[] = $detail->product_name . ' × ' . $detail->quantity;
                    }
                }
                
                if (!empty($productNames)) {
                    $recentActivities->push((object)[
                        'description' => '入库记录：' . implode(', ', $productNames),
                        'icon' => 'box-arrow-in-down',
                        'created_at' => $stockIn->created_at instanceof Carbon ? $stockIn->created_at : Carbon::parse($stockIn->created_at),
                    ]);
                }
            }
            
            // 按时间排序并限制数量
            $recentActivities = $recentActivities->sortByDesc('created_at')->take(5);
            
        } catch (\Exception $e) {
            // 如果出现错误，使用空的活动列表
            $recentActivities = collect();
        }

        return view('mobile.dashboard', compact(
            'todaySales',
            'todayRevenue', 
            'totalProducts',
            'lowStockCount',
            'recentActivities'
        ));
    }

    /**
     * 获取快捷操作菜单
     */
    public function quickActions()
    {
        $actions = [
            [
                'name' => '销售记录',
                'icon' => 'bi-cart3',
                'route' => 'mobile.sales.index',
                'color' => 'bg-blue-500'
            ],
            [
                'name' => '库存查询',
                'icon' => 'bi-archive',
                'route' => 'mobile.inventory.index',
                'color' => 'bg-green-500'
            ],
            [
                'name' => '入库管理',
                'icon' => 'bi-box-arrow-in-down',
                'route' => 'mobile.stock-in.index',
                'color' => 'bg-purple-500'
            ],
            [
                'name' => '退货管理',
                'icon' => 'bi-arrow-return-left',
                'route' => 'mobile.returns.index',
                'color' => 'bg-orange-500'
            ]
        ];

        return response()->json($actions);
    }
} 