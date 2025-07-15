<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\PriceSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class SaleStatisticsController extends Controller
{
    /**
     * 显示销售统计页面
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfDay()->toDateString());
        $startDateObj = \Carbon\Carbon::parse($startDate)->startOfDay();
        $endDateObj = \Carbon\Carbon::parse($endDate)->endOfDay();
        $storeId = $request->input('store_id') ?? session('current_store_id');
        $user = auth()->user();

        // 查询销售单据
        $salesQuery = \App\Models\Sale::with(['user:id,real_name', 'store:id,name'])
            ->whereBetween('created_at', [$startDateObj, $endDateObj]);
        if ($storeId && $storeId != 0) {
            $salesQuery->where('store_id', $storeId);
        } elseif (!$user->isSuperAdmin()) {
            // 普通用户必须有具体仓库
            $userStoreIds = $user->stores()->pluck('stores.id')->toArray();
            $salesQuery->whereIn('store_id', $userStoreIds);
        }
        $sales = $salesQuery->orderBy('created_at', 'desc')->get();

        // 组装每单的统计数据
        $salesRecords = $sales->map(function($sale) {
            if ($sale->sale_type === 'standard') {
                $totalCost = $sale->saleDetails->sum(function($d) {
                    return ($d->cost ?? $d->cost_price ?? 0) * $d->quantity;
                });
            } else {
                $totalCost = $sale->blindBagDeliveries->sum('total_cost');
            }
            $totalAmount = $sale->total_amount;
            $profit = $totalAmount - $totalCost;
            $profitRate = $totalAmount > 0 ? ($profit / $totalAmount) * 100 : 0;
            return [
                'date' => $sale->created_at->format('Y-m-d'),
                'order_no' => $sale->order_no,
                'user' => $sale->user->real_name ?? '',
                'store' => $sale->store->name ?? '',
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'profit' => $profit,
                'profit_rate' => $profitRate,
            ];
        });

        // 计算总统计数据
        $totalStats = [
            'total_quantity' => $sales->sum(function($sale) {
                return $sale->saleDetails->sum('quantity') + $sale->blindBagDeliveries->sum('quantity');
            }),
            'total_amount' => $sales->sum('total_amount'),
            'total_profit' => $sales->sum(function($sale) {
                if ($sale->sale_type === 'standard') {
                    $totalCost = $sale->saleDetails->sum(function($d) {
                        return ($d->cost ?? $d->cost_price ?? 0) * $d->quantity;
                    });
                } else {
                    $totalCost = $sale->blindBagDeliveries->sum('total_cost');
                }
                return $sale->total_amount - $totalCost;
            }),
        ];
        
        $totalStats['profit_rate'] = $totalStats['total_amount'] > 0 ? 
            ($totalStats['total_profit'] / $totalStats['total_amount']) * 100 : 0;

        $stores = \App\Models\Store::all();
        $isSuperAdmin = $user->isSuperAdmin();
        return view('statistics.sales', compact('salesRecords', 'startDateObj', 'endDateObj', 'stores', 'totalStats', 'storeId', 'isSuperAdmin'));
    }

    /**
     * 销售统计页面（与index方法相同）
     */
    public function sales(Request $request)
    {
        return $this->index($request);
    }

    /**
     * 获取统计数据
     */
    private function getStatisticsData($startDate, $endDate)
    {
        // 获取销售明细数据
        $salesDetails = \App\Models\SaleDetail::query()
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->join('stores', 'sales.store_id', '=', 'stores.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'sale_details.*',
                'sales.created_at as sale_date',
                'products.name as product_name',
                'products.cost_price',
                'users.real_name as user_name',
                'stores.name as store_name',
                'sales.sale_type'
            )
            ->orderBy('sales.created_at', 'desc')
            ->get();

        return $salesDetails;
    }

    /**
     * 导出销售统计报表
     */
    public function export(Request $request)
    {
        try {
            // 获取时间范围
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now()->endOfMonth());

            // 获取统计数据
            $data = $this->getStatisticsData($startDate, $endDate);

            // 生成CSV内容
            $csvContent = $this->generateSalesCSV($data);

            // 生成文件名
            $filename = 'sales_statistics_' . now()->format('Y-m-d_H-i-s') . '.csv';

            // 返回CSV下载
            return Response::make($csvContent, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            return back()->with('error', '导出失败：' . $e->getMessage());
        }
    }

    /**
     * 生成销售统计CSV内容
     */
    private function generateSalesCSV($data)
    {
        // CSV头部
        $headers = [
            '日期',
            '商品名称',
            '销售类型',
            '数量',
            '单价',
            '总金额',
            '成本',
            '利润',
            '利润率',
            '销售员',
            '仓库'
        ];

        $csv = $this->arrayToCsv($headers);

        // 数据行
        foreach ($data as $row) {
            $csvRow = [
                $row->sale_date ?? $row->created_at,
                $row->product_name ?? '未知商品',
                $row->sale_type == 'standard' ? '标品' : '盲袋',
                $row->quantity ?? 0,
                $row->unit_price ?? 0,
                $row->total_price ?? 0,
                $row->cost_price ?? 0,
                $row->profit ?? 0,
                $row->profit_rate ?? 0 . '%',
                $row->user_name ?? '未知',
                $row->store_name ?? '未知仓库'
            ];

            $csv .= $this->arrayToCsv($csvRow);
        }

        return $csv;
    }

    /**
     * 数组转CSV格式
     */
    private function arrayToCsv($array)
    {
        $csv = '';
        foreach ($array as $value) {
            // 处理包含逗号、引号或换行符的值
            $value = str_replace('"', '""', $value);
            if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
                $value = '"' . $value . '"';
            }
            $csv .= $value . ',';
        }
        return rtrim($csv, ',') . "\n";
    }

    /**
     * 测试盲袋成本计算
     */
    public function testBlindBagCost()
    {
        $blindBagSales = \App\Models\Sale::where('sale_type', 'blind_bag')->get();
        
        $results = [];
        foreach ($blindBagSales as $sale) {
            $saleDetails = $sale->saleDetails;
            $blindBagDeliveries = $sale->blindBagDeliveries;
            
            $totalDeliveryCost = $blindBagDeliveries->sum('total_cost');
            $totalSaleAmount = $sale->total_amount;
            $calculatedProfit = $totalSaleAmount - $totalDeliveryCost;
            
            $results[] = [
                'sale_id' => $sale->id,
                'sale_amount' => $totalSaleAmount,
                'delivery_cost' => $totalDeliveryCost,
                'calculated_profit' => $calculatedProfit,
                'profit_rate' => $totalSaleAmount > 0 ? ($calculatedProfit / $totalSaleAmount) * 100 : 0,
                'delivery_details' => $blindBagDeliveries->map(function($delivery) {
                    return [
                        'product' => $delivery->deliveryProduct->name ?? 'Unknown',
                        'quantity' => $delivery->quantity,
                        'unit_cost' => $delivery->unit_cost,
                        'total_cost' => $delivery->total_cost
                    ];
                })
            ];
        }
        
        return response()->json($results);
    }
} 