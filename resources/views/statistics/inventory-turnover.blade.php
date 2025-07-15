@extends('layouts.app')

@section('title', '库存周转率统计')
@section('header', '库存周转率统计')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- 时间筛选 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <form action="{{ route('statistics.inventory-turnover') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">时间范围</label>
                <div class="flex gap-2">
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                           class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    <span class="flex items-center text-gray-500">至</span>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" 
                           class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="bi bi-search mr-2"></i>查询
                </button>
            </div>
        </form>
    </div>

    <!-- 周转率统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-600 text-sm font-medium">库存周转率</p>
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($turnoverStats['turnover_rate'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                    <i class="bi bi-arrow-repeat text-white text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-600 text-sm font-medium">周转天数</p>
                    <p class="text-2xl font-bold text-green-700">{{ number_format($turnoverStats['turnover_days'], 1) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                    <i class="bi bi-calendar-event text-white text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl p-6 border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-600 text-sm font-medium">销售成本</p>
                    <p class="text-2xl font-bold text-yellow-700">¥{{ number_format($turnoverStats['cost_of_goods_sold'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center">
                    <i class="bi bi-currency-yen text-white text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-600 text-sm font-medium">平均库存成本</p>
                    <p class="text-2xl font-bold text-red-700">¥{{ number_format($turnoverStats['average_inventory'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                    <i class="bi bi-boxes text-white text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 主要内容 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- 库存状态 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <h3 class="text-xl font-semibold text-gray-900">库存状态</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <span class="text-gray-700 font-medium">商品总数</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($inventoryStatus['total_products']) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <span class="text-gray-700 font-medium">库存总量</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($inventoryStatus['total_quantity']) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <span class="text-gray-700 font-medium">库存总值</span>
                        <span class="text-lg font-bold text-gray-900">¥{{ number_format($inventoryStatus['total_value'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-xl">
                        <span class="text-red-700 font-medium">低库存商品数</span>
                        <span class="text-lg font-bold text-red-900">{{ number_format($inventoryStatus['low_stock']) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-xl">
                        <span class="text-yellow-700 font-medium">超库存商品数</span>
                        <span class="text-lg font-bold text-yellow-900">{{ number_format($inventoryStatus['overstock']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 库存变动趋势 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <h3 class="text-xl font-semibold text-gray-900">库存变动趋势</h3>
            </div>
            <div class="p-6">
                <canvas id="inventoryTrendChart" class="w-full" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- 库存预警商品 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-xl font-semibold text-gray-900">库存预警商品</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品名称</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">当前库存</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">最小库存</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">最大库存</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($alertProducts as $product)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product['product'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($product['current_quantity']) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($product['min_quantity']) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($product['max_quantity']) }}</td>
                        <td class="px-6 py-4">
                            @if($product['status'] == 'low')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="bi bi-exclamation-triangle mr-1"></i>库存不足
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="bi bi-exclamation-circle mr-1"></i>库存过高
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="bi bi-check-circle text-green-600 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">暂无预警商品</h3>
                                <p class="text-gray-500">所有商品的库存都在正常范围内</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="{{ asset('assets/chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 库存变动趋势图
    var trendCtx = document.getElementById('inventoryTrendChart').getContext('2d');
    var trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($inventoryTrend->pluck('date')) !!},
            datasets: [{
                label: '库存数量',
                data: {!! json_encode($inventoryTrend->pluck('quantity')) !!},
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            }
        }
    });
});
</script>
@endsection 