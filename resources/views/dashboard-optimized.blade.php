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
@endphp

@section('content')
<div class="space-y-6">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="mt-1 text-sm text-gray-600"><x-lang key="dashboard.subtitle"/></p>
            </div>
            <div class="mt-4 lg:mt-0 flex items-center space-x-3">
                <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="bi bi-arrow-clockwise mr-2"></i>
                    <span><x-lang key="dashboard.refresh_data"/></span>
                </button>
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
    </div>
</div>
@endsection