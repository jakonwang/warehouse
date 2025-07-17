@extends('layouts.app')

@section('title', __('messages.statistics.sales.title'))

@section('content')
<div class="space-y-8" x-data="{ 
    selectedPeriod: 'custom',
    selectedChart: 'sales',
    startDate: '{{ request('start_date', $startDateObj->format('Y-m-d')) }}',
    endDate: '{{ request('end_date', $endDateObj->format('Y-m-d')) }}',
    updatePeriod(period) {
        this.selectedPeriod = period;
        const today = new Date();
        const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
        
        switch(period) {
            case 'today':
                this.startDate = this.endDate = today.toISOString().split('T')[0];
                break;
            case 'week':
                this.startDate = startOfWeek.toISOString().split('T')[0];
                this.endDate = new Date().toISOString().split('T')[0];
                break;
            case 'month':
                this.startDate = startOfMonth.toISOString().split('T')[0];
                this.endDate = new Date().toISOString().split('T')[0];
                break;
        }
    }
}">
    <!-- 高级页面头部 -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-black bg-opacity-10"></div>
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white bg-opacity-10 rounded-full"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-white bg-opacity-10 rounded-full"></div>
        
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="bi bi-graph-up text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-2"><x-lang key="messages.statistics.sales.title"/></h1>
                        <p class="text-emerald-100 text-lg"><x-lang key="messages.statistics.sales.subtitle"/></p>
                        <div class="flex items-center mt-3 space-x-4 text-sm">
                            @if(!$isSuperAdmin && $storeId)
                                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full backdrop-blur-sm">
                                    <i class="bi bi-building mr-1"></i>当前仓库: {{ $stores->where('id', $storeId)->first()->name ?? '未知仓库' }}
                                </span>
                            @endif
                            <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full backdrop-blur-sm">
                                <i class="bi bi-speedometer2 mr-1"></i><x-lang key="messages.statistics.sales.real_time_monitor"/>
                            </span>
                            <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full backdrop-blur-sm">
                                <i class="bi bi-trending-up mr-1"></i><x-lang key="messages.statistics.sales.trend_analysis"/>
                            </span>
                            <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full backdrop-blur-sm">
                                <i class="bi bi-pie-chart mr-1"></i><x-lang key="messages.statistics.sales.data_visualization"/>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button @click="updatePeriod('today')" 
                            :class="selectedPeriod === 'today' ? 'bg-white bg-opacity-30' : 'bg-white bg-opacity-20'"
                            class="px-4 py-2 rounded-xl font-medium transition-all duration-200 backdrop-blur-sm">
                        <x-lang key="messages.statistics.sales.today"/>
                    </button>
                    <button @click="updatePeriod('week')" 
                            :class="selectedPeriod === 'week' ? 'bg-white bg-opacity-30' : 'bg-white bg-opacity-20'"
                            class="px-4 py-2 rounded-xl font-medium transition-all duration-200 backdrop-blur-sm">
                        <x-lang key="messages.statistics.sales.this_week"/>
                    </button>
                    <button @click="updatePeriod('month')" 
                            :class="selectedPeriod === 'month' ? 'bg-white bg-opacity-30' : 'bg-white bg-opacity-20'"
                            class="px-4 py-2 rounded-xl font-medium transition-all duration-200 backdrop-blur-sm">
                        <x-lang key="messages.statistics.sales.this_month"/>
                    </button>
                    <button @click="updatePeriod('custom')" 
                            :class="selectedPeriod === 'custom' ? 'bg-white bg-opacity-30' : 'bg-white bg-opacity-20'"
                            class="px-4 py-2 rounded-xl font-medium transition-all duration-200 backdrop-blur-sm">
                        <x-lang key="messages.statistics.sales.custom"/>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选区域 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('statistics.sales') }}" class="grid grid-cols-1 lg:grid-cols-{{ $isSuperAdmin ? '4' : '3' }} gap-4 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><x-lang key="messages.statistics.sales.start_date"/></label>
                <input type="date" 
                       name="start_date" 
                       x-model="startDate"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><x-lang key="messages.statistics.sales.end_date"/></label>
                <input type="date" 
                       name="end_date" 
                       x-model="endDate"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
            </div>
            @if($isSuperAdmin)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><x-lang key="messages.statistics.sales.store_filter"/></label>
                <select name="store_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
                    <option value=""><x-lang key="messages.statistics.sales.all_stores"/></option>
                    @foreach($stores ?? [] as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="bi bi-search mr-2"></i><x-lang key="messages.statistics.sales.query_data"/>
                </button>
            </div>
        </form>
    </div>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 总销量 -->
        <div class="bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-box text-2xl"></i>
                </div>
                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-xs font-medium"><x-lang key="messages.statistics.sales.pieces"/></span>
            </div>
            <h3 class="text-3xl font-bold mb-1">{{ number_format($totalStats['total_quantity'] ?? 0) }}</h3>
            <p class="text-blue-100 text-sm"><x-lang key="messages.statistics.sales.total_sales"/></p>
            <div class="mt-3 flex items-center text-xs">
                <span class="text-green-300 font-medium">+15.2%</span>
                <span class="text-blue-200 ml-1"><x-lang key="messages.statistics.sales.vs_last_month"/></span>
            </div>
        </div>

        <!-- 总销售额 -->
        <div class="bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-700 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-currency-dollar text-2xl"></i>
                </div>
                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-xs font-medium"><x-lang key="messages.statistics.sales.amount"/></span>
            </div>
            <h3 class="text-3xl font-bold mb-1">¥{{ number_format($totalStats['total_amount'] ?? 0, 0) }}</h3>
            <p class="text-emerald-100 text-sm"><x-lang key="messages.statistics.sales.total_revenue"/></p>
            <div class="mt-3 flex items-center text-xs">
                <span class="text-green-300 font-medium">+9.8%</span>
                <span class="text-emerald-200 ml-1"><x-lang key="messages.statistics.sales.vs_last_month"/></span>
            </div>
        </div>

        <!-- 总利润 -->
        <div class="bg-gradient-to-br from-purple-500 via-purple-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-graph-up text-2xl"></i>
                </div>
                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-xs font-medium"><x-lang key="messages.statistics.sales.profit"/></span>
            </div>
            <h3 class="text-3xl font-bold mb-1">¥{{ number_format($totalStats['total_profit'] ?? 0, 0) }}</h3>
            <p class="text-purple-100 text-sm"><x-lang key="messages.statistics.sales.total_profit"/></p>
            <div class="mt-3 flex items-center text-xs">
                <span class="text-green-300 font-medium">+12.5%</span>
                <span class="text-purple-200 ml-1"><x-lang key="messages.statistics.sales.vs_last_month"/></span>
            </div>
        </div>

        <!-- 利润率 -->
        @if(auth()->user()->canViewProfitAndCost())
        <div class="bg-gradient-to-br from-orange-500 via-orange-600 to-orange-700 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-percent text-2xl"></i>
                </div>
                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-xs font-medium"><x-lang key="messages.statistics.sales.ratio"/></span>
            </div>
            <h3 class="text-3xl font-bold mb-1">{{ number_format($totalStats['profit_rate'] ?? 0, 1) }}%</h3>
            <p class="text-orange-100 text-sm"><x-lang key="messages.statistics.sales.avg_profit_rate"/></p>
            <div class="mt-3 flex items-center text-xs">
                <span class="text-green-300 font-medium">+2.1%</span>
                <span class="text-orange-200 ml-1"><x-lang key="messages.statistics.sales.vs_last_month"/></span>
            </div>
        </div>
        @endif
    </div>

    <!-- 图表区域 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 销售趋势图 -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900"><x-lang key="messages.statistics.sales.sales_trend_analysis"/></h3>
                <div class="flex items-center space-x-2">
                    <button @click="selectedChart = 'sales'" 
                            :class="selectedChart === 'sales' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                        <x-lang key="messages.statistics.sales.sales_amount"/>
                    </button>
                    <button @click="selectedChart = 'orders'" 
                            :class="selectedChart === 'orders' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                        订单量
                    </button>
                    <button @click="selectedChart = 'profit'" 
                            :class="selectedChart === 'profit' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                        利润
                    </button>
                </div>
            </div>
            <div class="h-80">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- 系列销售占比 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">系列销售占比</h3>
            <div class="h-80">
                <canvas id="seriesPieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 销售明细表格 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">销售明细</h3>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('statistics.sales.export') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                        <i class="bi bi-download"></i>
                    </a>
                    <a href="{{ route('test.export') }}" class="p-2 text-blue-400 hover:text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200" title="测试路由">
                        <i class="bi bi-gear"></i>
                    </a>
                    <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                        <i class="bi bi-printer"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日期</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">单号</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">销售员</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">仓库</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">销售额</th>
                        @if(auth()->user()->canViewProfitAndCost())
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">成本</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">利润</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">利润率</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($salesRecords ?? [] as $record)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record['date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record['order_no'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record['user'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record['store'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">¥{{ number_format($record['total_amount'], 2) }}</td>
                        @if(auth()->user()->canViewProfitAndCost())
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">¥{{ number_format($record['total_cost'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="font-medium {{ $record['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">¥{{ number_format($record['profit'], 2) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="font-medium {{ $record['profit_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($record['profit_rate'], 1) }}%</span>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canViewProfitAndCost() ? '8' : '5' }}" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="bi bi-graph-up text-4xl text-gray-300 mb-2"></i>
                                <p class="text-lg font-medium">暂无销售数据</p>
                                <p class="text-sm">请选择其他时间范围或仓库</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 销售趋势图
    const salesTrendCtx = document.getElementById('salesTrendChart');
    if (salesTrendCtx) {
        new Chart(salesTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels'] ?? ['1月', '2月', '3月', '4月', '5月', '6月']) !!},
                datasets: [{
                    label: '销售额',
                    data: {!! json_encode($chartData['sales'] ?? [12000, 19000, 15000, 25000, 22000, 30000]) !!},
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgb(16, 185, 129)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(16, 185, 129, 0.5)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '¥' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    // 系列销售占比图
    const seriesPieCtx = document.getElementById('seriesPieChart');
    if (seriesPieCtx) {
        new Chart(seriesPieCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($pieData['labels'] ?? ['盲袋系列', '标准商品', '限量版', '其他']) !!},
                datasets: [{
                    data: {!! json_encode($pieData['data'] ?? [45, 30, 15, 10]) !!},
                    backgroundColor: [
                        'rgb(16, 185, 129)',
                        'rgb(59, 130, 246)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(16, 185, 129, 0.5)',
                        borderWidth: 1,
                        cornerRadius: 8
                    }
                },
                cutout: '60%'
            }
        });
    }
});
</script>
@endpush
@endsection 