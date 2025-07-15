<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * 显示销售统计报表
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $salesByPriceRange = DB::table('sales')
            ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->whereBetween('sales.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('
                    CASE
                        WHEN sale_details.price <= 29 THEN "29元系列"
                        WHEN sale_details.price <= 59 THEN "59元系列"
                        WHEN sale_details.price <= 89 THEN "89元系列"
                        ELSE "159元系列"
                    END as price_range
                '),
                DB::raw('SUM(sale_details.quantity) as total_quantity'),
                DB::raw('SUM(sale_details.quantity * sale_details.price) as total_amount'),
                DB::raw('SUM(sale_details.quantity * (sale_details.price - sale_details.cost)) as total_profit')
            )
            ->groupBy('price_range')
            ->get();

        $totalSales = DB::table('sales')
            ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->whereBetween('sales.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum(DB::raw('sale_details.quantity * sale_details.price'));

        $totalProfit = DB::table('sales')
            ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->whereBetween('sales.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum(DB::raw('sale_details.quantity * (sale_details.price - sale_details.cost)'));

        return view('reports.sales', compact('salesByPriceRange', 'totalSales', 'totalProfit', 'startDate', 'endDate'));
    }
} 