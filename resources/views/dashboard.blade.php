@extends('layouts.app')

@section('title', __('dashboard.title'))
@section('header', __('dashboard.title'))

@php
// 防御性检查：确保所有必要的变量都存在
if (!isset($storeRanking)) {
    $storeRanking = collect([]);
}
if (!isset($todaySales)) {
    $todaySales = (object) [
        'total_sales' => 0,
        'total_amount' => 0,
        'total_profit' => 0,
        'avg_profit_rate' => 0
    ];
}
if (!isset($lowStockAlerts)) {
    $lowStockAlerts = collect([]);
}
if (!isset($topProducts)) {
    $topProducts = collect([]);
}
if (!isset($recentActivities)) {
    $recentActivities = collect([]);
}
if (!isset($isSuperAdmin)) {
    $isSuperAdmin = auth()->user() ? auth()->user()->isSuperAdmin() : false;
}
if (!isset($currentStore)) {
    $currentStoreId = session('current_store_id');
    $currentStore = null;
    if ($currentStoreId && $currentStoreId != 0) {
        $currentStore = \App\Models\Store::find($currentStoreId);
    }
}
if (!isset($salesTrendData)) {
    $salesTrendData = [
        'dates' => [],
        'amounts' => [],
        'counts' => []
    ];
}
$selectedPeriod = request('period', 'today');
$customRange = request('range', '');
@endphp

@section('content')
<div class="space-y-6">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2"><x-lang key="dashboard.title"/></h1>
                <p class="mt-1 text-sm text-gray-600"><x-lang key="dashboard.subtitle"/></p>
                
                <!-- 数据范围标识 -->
                <div class="mt-3 flex items-center space-x-4">
                    @if($isSuperAdmin)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            <i class="bi bi-shield-check mr-1"></i>超级管理员 - 全平台数据
                        </span>
                    @elseif($currentStore)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="bi bi-building mr-1"></i>当前仓库: {{ $currentStore->name }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <i class="bi bi-info-circle mr-1"></i>请先选择仓库
                        </span>
                    @endif
                    
                    @if($isSuperAdmin && $currentStore)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="bi bi-eye mr-1"></i>已选择仓库: {{ $currentStore->name }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="mt-4 lg:mt-0 flex items-center space-x-3">
                <form id="periodForm" method="get" action="/dashboard" class="inline-flex items-center space-x-3">
                    <select name="period" id="periodSelect" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="document.getElementById('periodForm').submit()">
                        <option value="today" {{ request('period', 'today') == 'today' ? 'selected' : '' }}><x-lang key="dashboard.today"/></option>
                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}><x-lang key="dashboard.week"/></option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}><x-lang key="dashboard.month"/></option>
                        <option value="quarter" {{ request('period') == 'quarter' ? 'selected' : '' }}><x-lang key="dashboard.quarter"/></option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>自定义</option>
                    </select>
                    <input id="customRangeInput" name="range" type="text" value="{{ request('range') }}" placeholder="选择日期区间" class="ml-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64" style="display: none;" readonly />
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"> <i class="bi bi-arrow-clockwise mr-2"></i> <span>{{ __('dashboard.refresh_data') }}</span></button>
                </form>
            </div>
        </div>
    </div>

    <!-- Core Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sales -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-currency-dollar text-2xl"></i>
                    </div>
                    <span class="text-green-300 text-sm font-medium"><x-lang key="dashboard.today"/></span>
                </div>
                <h3 class="text-2xl font-bold">¥{{ number_format($todaySales->total_amount ?? 0, 2) }}</h3>
                <p class="text-blue-100 text-sm"><x-lang key="dashboard.total_sales"/></p>
            </div>
        </div>

        <!-- Order Count -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-cart text-2xl"></i>
                    </div>
                    <span class="text-green-300 text-sm font-medium"><x-lang key="dashboard.total_orders"/></span>
                </div>
                <h3 class="text-2xl font-bold">{{ $todaySales->total_sales ?? 0 }}</h3>
                <p class="text-green-100 text-sm"><x-lang key="dashboard.total_orders"/></p>
            </div>
        </div>

        <!-- Today's Profit -->
        @if(auth()->user()->canViewProfitAndCost())
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-graph-up text-2xl"></i>
                    </div>
                    <span class="text-green-300 text-sm font-medium"><x-lang key="dashboard.total_profit"/></span>
                </div>
                <h3 class="text-2xl font-bold">¥{{ number_format($todaySales->total_profit ?? 0, 2) }}</h3>
                <p class="text-purple-100 text-sm"><x-lang key="dashboard.total_profit"/></p>
            </div>
        </div>
        @endif

        <!-- Average Profit Rate -->
        @if(auth()->user()->canViewProfitAndCost())
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-percent text-2xl"></i>
                    </div>
                    <span class="text-green-300 text-sm font-medium"><x-lang key="dashboard.avg_profit_rate"/></span>
                </div>
                <h3 class="text-2xl font-bold">{{ number_format($todaySales->avg_profit_rate ?? 0, 1) }}%</h3>
                <p class="text-orange-100 text-sm"><x-lang key="dashboard.avg_profit_rate"/></p>
            </div>
        </div>
        @endif
    </div>

    <!-- Store Performance Rankings -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Store Sales Ranking -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    @if($isSuperAdmin)
                        仓库销售排行
                    @else
                        当前仓库销售
                    @endif
                </h3>
                <a href="{{ route('statistics.sales') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium"><x-lang key="dashboard.view_all"/></a>
            </div>
            <div class="space-y-4">
                @if($isSuperAdmin)
                    @forelse($storeRanking as $index => $store)
                    <div class="flex items-center justify-between p-4 {{ $index == 0 ? 'bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200' : 'bg-gray-50' }} rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 {{ $index == 0 ? 'bg-yellow-500' : ($index == 1 ? 'bg-gray-400' : 'bg-orange-400') }} rounded-full flex items-center justify-center text-white text-sm font-bold">{{ $index + 1 }}</div>
                            <div class="ml-3">
                                <h4 class="font-medium text-gray-900">{{ $store->name }}</h4>
                                <p class="text-sm text-gray-500"><x-lang key="dashboard.week"/> <x-lang key="dashboard.sales_count"/></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">¥{{ number_format($store->total_sales, 0) }}</p>
                            <p class="text-sm text-green-600">{{ $index == 0 ? __('dashboard.first_place') : __('dashboard.top_three') }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <p><x-lang key="dashboard.no_sales_data"/></p>
                    </div>
                    @endforelse
                @else
                    @if($currentStore)
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">{{ $currentStore->name }}</h4>
                                    <p class="text-sm text-gray-500">当前仓库销售数据</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900">¥{{ number_format($todaySales->total_amount ?? 0, 0) }}</p>
                                <p class="text-sm text-green-600">今日销售额</p>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>请先选择仓库查看销售数据</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Hot Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900"><x-lang key="dashboard.hot_products"/></h3>
                <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium"><x-lang key="dashboard.view_all"/></a>
            </div>
            <div class="space-y-4">
                @forelse($topProducts as $index => $product)
                <div class="flex items-center justify-between p-4 {{ $index == 0 ? 'bg-blue-50' : 'bg-gray-50' }} rounded-lg">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="bi bi-gift text-white"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="font-medium text-gray-900">{{ $product->name }}</h4>
                            <p class="text-sm text-gray-500"><x-lang key="dashboard.sales_count"/>: {{ $product->total_quantity }}<x-lang key="dashboard.pieces"/></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-900">¥{{ number_format($product->price * $product->total_quantity, 0) }}</p>
                        <p class="text-sm text-green-600">{{ $index == 0 ? __('dashboard.hot_sale') : __('dashboard.on_list') }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <p><x-lang key="dashboard.no_sales_data"/></p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Real-time Monitoring -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Stock Alerts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900"><x-lang key="dashboard.stock_alerts"/></h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $lowStockAlerts->count() }}<x-lang key="dashboard.low_stock_items"/></span>
            </div>
            <div class="space-y-3">
                @forelse($lowStockAlerts as $alert)
                <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-exclamation-triangle text-red-600 text-sm"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-900">{{ $alert->product_name }}</p>
                            <p class="text-xs text-red-600"><x-lang key="dashboard.remaining"/>: {{ $alert->quantity }}<x-lang key="dashboard.pieces"/></p>
                        </div>
                    </div>
                    <a href="{{ route('inventory.index') }}" class="text-red-600 hover:text-red-800">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500">
                    <i class="bi bi-check-circle text-green-500 text-2xl mb-2"></i>
                    <p class="text-sm"><x-lang key="dashboard.sufficient_stock"/></p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900"><x-lang key="dashboard.recent_activities"/></h3>
                <span class="text-xs text-gray-500"><x-lang key="dashboard.real_time_update"/></span>
            </div>
            <div class="space-y-3">
                @forelse($recentActivities as $activity)
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                        <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500">
                    <p class="text-sm"><x-lang key="dashboard.no_activity_records"/></p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="dashboard.quick_actions"/></h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('sales.create') }}" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mb-2">
                        <i class="bi bi-plus-circle text-white"></i>
                    </div>
                    <span class="text-sm font-medium text-blue-900"><x-lang key="dashboard.add_sales"/></span>
                </a>

                <a href="{{ route('stock-ins.create') }}" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center mb-2">
                        <i class="bi bi-box-arrow-in-down text-white"></i>
                    </div>
                    <span class="text-sm font-medium text-green-900"><x-lang key="dashboard.stock_in"/></span>
                </a>

                <a href="{{ route('inventory.index') }}" class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors">
                    <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center mb-2">
                        <i class="bi bi-archive text-white"></i>
                    </div>
                    <span class="text-sm font-medium text-purple-900"><x-lang key="dashboard.inventory_query"/></span>
                </a>

                <a href="{{ route('statistics.sales') }}" class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg border border-orange-200 transition-colors">
                    <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center mb-2">
                        <i class="bi bi-graph-up text-white"></i>
                    </div>
                    <span class="text-sm font-medium text-orange-900"><x-lang key="dashboard.sales_report"/></span>
                </a>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-900 mb-3"><x-lang key="dashboard.system_status"/></h4>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600"><x-lang key="dashboard.database_connection"/></span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></span>
                            <x-lang key="dashboard.normal"/>
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600"><x-lang key="dashboard.server_status"/></span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></span>
                            <x-lang key="dashboard.running_normal"/>
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600"><x-lang key="dashboard.last_backup"/></span>
                        <span class="text-sm text-gray-500"><x-lang key="dashboard.two_hours_ago"/></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 销售趋势曲线图 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="dashboard.sales_trend"/></h3>
        <div id="sales-trend-chart" style="height: 320px;"></div>
    </div>
</div>

@push('scripts')
<!-- 引入flatpickr和ECharts -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
<script>
function renderSalesTrendChart() {
    console.log('开始渲染销售趋势图表');
    
    var chartDom = document.getElementById('sales-trend-chart');
    console.log('图表容器:', chartDom); // 调试用
    
    if (!chartDom) {
        console.error('找不到图表容器 #sales-trend-chart');
        return;
    }
    
    // 检查ECharts是否正确加载
    if (typeof echarts === 'undefined') {
        console.error('ECharts 未加载');
        return;
    }
    
    console.log('ECharts 已加载，版本:', echarts.version);
    
    var myChart = echarts.init(chartDom);
    console.log('图表实例已创建');
    
    // 获取PHP传递的销售趋势数据
    var salesTrendData = {
        dates: @json($salesTrendData['dates'] ?? []),
        amounts: @json($salesTrendData['amounts'] ?? []),
        counts: @json($salesTrendData['counts'] ?? [])
    };
    
    console.log('销售趋势数据:', salesTrendData); // 调试用
    console.log('日期数组长度:', salesTrendData.dates.length);
    console.log('金额数组长度:', salesTrendData.amounts.length);
    console.log('数量数组长度:', salesTrendData.counts.length);
    
    // 检查数据是否为空
    if (!salesTrendData.dates || salesTrendData.dates.length === 0) {
        console.warn('销售趋势数据为空，使用默认数据');
        salesTrendData = {
            dates: ['2024-01-01', '2024-01-02', '2024-01-03', '2024-01-04', '2024-01-05', '2024-01-06', '2024-01-07'],
            amounts: [0, 0, 0, 0, 0, 0, 0],
            counts: [0, 0, 0, 0, 0, 0, 0]
        };
    }
    
    var option = {
        tooltip: { 
            trigger: 'axis',
            formatter: function(params) {
                var result = params[0].axisValue + '<br/>';
                params.forEach(function(param) {
                    if (param.seriesName === '销售额') {
                        result += param.marker + param.seriesName + ': ¥' + param.value.toLocaleString() + '<br/>';
                    } else {
                        result += param.marker + param.seriesName + ': ' + param.value + ' 单<br/>';
                    }
                });
                return result;
            }
        },
        legend: { 
            data: ['销售额', '订单数'],
            top: 10
        },
        grid: { 
            left: '3%', 
            right: '4%', 
            bottom: '3%', 
            top: '15%',
            containLabel: true 
        },
        xAxis: { 
            type: 'category', 
            boundaryGap: false, 
            data: salesTrendData.dates || [],
            axisLabel: {
                formatter: function(value) {
                    return value.substring(5); // 只显示月-日
                }
            }
        },
        yAxis: [
            { 
                type: 'value',
                name: '销售额',
                position: 'left',
                axisLabel: {
                    formatter: '¥{value}'
                }
            },
            {
                type: 'value',
                name: '订单数',
                position: 'right',
                axisLabel: {
                    formatter: '{value} 单'
                }
            }
        ],
        series: [
            { 
                name: '销售额', 
                type: 'line', 
                smooth: true, 
                data: salesTrendData.amounts || [],
                areaStyle: {
                    opacity: 0.3
                },
                itemStyle: {
                    color: '#3b82f6'
                },
                yAxisIndex: 0
            },
            { 
                name: '订单数', 
                type: 'line', 
                smooth: true, 
                data: salesTrendData.counts || [],
                itemStyle: {
                    color: '#10b981'
                },
                yAxisIndex: 1
            }
        ]
    };
    
    console.log('图表配置:', option);
    
    try {
        myChart.setOption(option);
        console.log('图表渲染完成');
    } catch (error) {
        console.error('图表渲染失败:', error);
    }
    
    // 响应式处理
    window.addEventListener('resize', function() {
        myChart.resize();
    });
}

// 控制自定义区间输入框显示和日期选择器
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM加载完成，开始初始化');
    
    var select = document.getElementById('periodSelect');
    var customInput = document.getElementById('customRangeInput');
    
    function toggleCustomInput() {
        if(select.value === 'custom') {
            customInput.style.display = '';
            // 初始化日期选择器
            if (!customInput.hasAttribute('data-flatpickr-initialized')) {
                flatpickr(customInput, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    onChange: function(selectedDates, dateStr, instance) {
                        // 当日期选择改变时，自动提交表单
                        if (dateStr) {
                            document.getElementById('periodForm').submit();
                        }
                    }
                });
                customInput.setAttribute('data-flatpickr-initialized', 'true');
            }
        } else {
            customInput.style.display = 'none';
        }
    }
    
    select.addEventListener('change', toggleCustomInput);
    toggleCustomInput();
    
    // 延迟初始化图表，确保DOM完全加载
    setTimeout(function() {
        console.log('开始初始化销售趋势图表');
        renderSalesTrendChart();
    }, 100);
});
</script>
@endpush
@endsection 