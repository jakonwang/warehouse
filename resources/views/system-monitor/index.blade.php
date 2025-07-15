@extends('layouts.app')

@section('title', __('messages.system_monitor.title'))
@section('header', __('messages.system_monitor.title'))

@section('content')
<div class="space-y-8" x-data="{ 
    activeTab: 'overview',
    refreshInterval: null,
    autoRefresh: true
}">
    <!-- 页面头部 -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <i class="bi bi-speedometer2 text-3xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ __('messages.system_monitor.title') }}</h1>
                    <p class="text-indigo-100 text-lg">{{ __('messages.system_monitor.subtitle') }}</p>
                    <div class="flex items-center mt-3 space-x-4 text-sm">
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-clock mr-1"></i>
                            {{ __('messages.system_monitor.real_time_update') }}
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-shield-check mr-1"></i>
                            {{ __('messages.system_monitor.security_monitoring') }}
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-graph-up mr-1"></i>
                            {{ __('messages.system_monitor.performance_analysis') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-4 lg:mt-0 flex items-center space-x-3">
                <button @click="autoRefresh = !autoRefresh" 
                        :class="autoRefresh ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-500 hover:bg-gray-600'"
                        class="px-4 py-2 rounded-lg text-white transition-colors duration-200">
                    <i class="bi" :class="autoRefresh ? 'bi-pause-circle' : 'bi-play-circle'"></i>
                    <span x-text="autoRefresh ? '{{ __('messages.system_monitor.auto_refresh') }}' : '{{ __('messages.system_monitor.manual_refresh') }}'"></span>
                </button>
                <button @click="location.reload()" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-white transition-colors duration-200">
                    <i class="bi bi-arrow-clockwise mr-2"></i>
                    {{ __('messages.system_monitor.refresh_data') }}
                </button>
            </div>
        </div>
    </div>

    <!-- 标签页导航 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'overview'" 
                        :class="activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    <i class="bi bi-grid-3x3-gap mr-2"></i>
                    {{ __('messages.system_monitor.overview') }}
                </button>
                <button @click="activeTab = 'performance'" 
                        :class="activeTab === 'performance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    <i class="bi bi-speedometer2 mr-2"></i>
                    {{ __('messages.system_monitor.performance') }}
                </button>
                <button @click="activeTab = 'database'" 
                        :class="activeTab === 'database' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    <i class="bi bi-database mr-2"></i>
                    {{ __('messages.system_monitor.database') }}
                </button>
                <button @click="activeTab = 'errors'" 
                        :class="activeTab === 'errors' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    <i class="bi bi-exclamation-triangle mr-2"></i>
                    {{ __('messages.system_monitor.errors') }}
                </button>
            </nav>
        </div>

        <!-- 标签页内容 -->
        <div class="p-6">
            <!-- 系统概览 -->
            <div x-show="activeTab === 'overview'" class="space-y-6">
                <!-- 系统状态卡片 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
                    <!-- 服务器状态 -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">{{ __('messages.system_monitor.server_status') }}</p>
                                <p class="text-3xl font-bold">{{ __('messages.system_monitor.healthy') }}</p>
                                <p class="text-green-200 text-xs mt-1">{{ __('messages.system_monitor.running_stable') }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-server text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- 数据库状态 -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">{{ __('messages.system_monitor.database_status') }}</p>
                                <p class="text-3xl font-bold">{{ $databaseStatus['status'] === 'healthy' ? __('messages.system_monitor.healthy') : __('messages.system_monitor.error') }}</p>
                                <p class="text-blue-200 text-xs mt-1">{{ $databaseStatus['response_time'] }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-database text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- 缓存状态 -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">{{ __('messages.system_monitor.cache_status') }}</p>
                                <p class="text-3xl font-bold">{{ $cacheStatus['status'] === 'healthy' ? __('messages.system_monitor.healthy') : __('messages.system_monitor.error') }}</p>
                                <p class="text-purple-200 text-xs mt-1">{{ $cacheStatus['response_time'] }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-lightning text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- 内存使用 -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100 text-sm font-medium">{{ __('messages.system_monitor.memory_usage') }}</p>
                                <p class="text-3xl font-bold">{{ $systemStatus['memory_usage']['usage_percentage'] }}%</p>
                                <p class="text-orange-200 text-xs mt-1">{{ $systemStatus['memory_usage']['current'] }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-cpu text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 系统信息 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- 基本信息 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="bi bi-info-circle mr-2 text-blue-500"></i>
                            {{ __('messages.system_monitor.system_info') }}
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.system_monitor.server_time') }}</span>
                                <span class="font-medium">{{ $systemStatus['server_time'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.system_monitor.php_version') }}</span>
                                <span class="font-medium">{{ $systemStatus['php_version'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.system_monitor.laravel_version') }}</span>
                                <span class="font-medium">{{ $systemStatus['laravel_version'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.system_monitor.database_connection') }}</span>
                                <span class="font-medium" :class="$systemStatus['database_connection'] === 'connected' ? 'text-green-600' : 'text-red-600'">
                                    {{ $systemStatus['database_connection'] === 'connected' ? __('messages.system_monitor.connected') : __('messages.system_monitor.disconnected') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.system_monitor.cache_connection') }}</span>
                                <span class="font-medium" :class="$systemStatus['cache_connection'] === 'connected' ? 'text-green-600' : 'text-red-600'">
                                    {{ $systemStatus['cache_connection'] === 'connected' ? __('messages.system_monitor.connected') : __('messages.system_monitor.disconnected') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 磁盘使用 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="bi bi-hdd mr-2 text-blue-500"></i>
                            {{ __('messages.system_monitor.disk_usage') }}
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.system_monitor.total_space') }}</span>
                                <span class="font-medium">{{ $systemStatus['disk_usage']['total'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.system_monitor.used_space') }}</span>
                                <span class="font-medium">{{ $systemStatus['disk_usage']['used'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.system_monitor.available_space') }}</span>
                                <span class="font-medium">{{ $systemStatus['disk_usage']['free'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" :style="`width: {{ $systemStatus['disk_usage']['usage_percentage'] }}%`"></div>
                            </div>
                            <div class="text-center text-sm text-gray-500">
                                {{ __('messages.system_monitor.usage_rate') }}: {{ $systemStatus['disk_usage']['usage_percentage'] }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 性能指标 -->
            <div x-show="activeTab === 'performance'" class="space-y-6">
                <!-- 业务指标 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="bi bi-people text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">{{ __('messages.system_monitor.total_users') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($performanceMetrics['total_users']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="bi bi-box text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">{{ __('messages.system_monitor.total_products') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($performanceMetrics['total_products']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="bi bi-cart text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">{{ __('messages.system_monitor.today_sales') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($performanceMetrics['total_sales']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="bi bi-exclamation-triangle text-orange-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">{{ __('messages.system_monitor.stock_warning') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($performanceMetrics['low_stock_items']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 财务指标 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.system_monitor.today_revenue') }}</h3>
                        <div class="text-center">
                            <p class="text-4xl font-bold text-green-600">¥{{ number_format($performanceMetrics['today_revenue']) }}</p>
                            <p class="text-gray-500 mt-2">{{ __('messages.system_monitor.today_sales_amount') }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.system_monitor.today_profit') }}</h3>
                        <div class="text-center">
                            <p class="text-4xl font-bold text-blue-600">¥{{ number_format($performanceMetrics['today_profit']) }}</p>
                            <p class="text-gray-500 mt-2">{{ __('messages.system_monitor.today_profit_amount') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 数据库状态 -->
            <div x-show="activeTab === 'database'" class="space-y-6">
                <!-- 数据库概览 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.system_monitor.database_status') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-900">{{ $databaseStatus['status'] === 'healthy' ? __('messages.system_monitor.healthy') : __('messages.system_monitor.error') }}</p>
                            <p class="text-gray-500 text-sm">{{ __('messages.system_monitor.connection_status') }}</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-900">{{ $databaseStatus['response_time'] }}</p>
                            <p class="text-gray-500 text-sm">{{ __('messages.system_monitor.response_time') }}</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-900">{{ $databaseStatus['slow_queries'] }}</p>
                            <p class="text-gray-500 text-sm">{{ __('messages.system_monitor.slow_queries') }}</p>
                        </div>
                    </div>
                </div>

                <!-- 表大小统计 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.system_monitor.table_sizes') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.system_monitor.table_name') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.system_monitor.record_count') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.system_monitor.estimated_size') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($databaseStatus['table_sizes'] as $table)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $table['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($table['count']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($table['size']) }} B</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 错误日志 -->
            <div x-show="activeTab === 'errors'" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.system_monitor.recent_errors') }}</h3>
                    @if(count($recentErrors) > 0)
                        <div class="space-y-3">
                            @foreach($recentErrors as $error)
                                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-sm text-red-800">{{ $error }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="bi bi-check-circle text-4xl text-green-500 mb-4"></i>
                            <p class="text-gray-500">{{ __('messages.system_monitor.no_errors') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 自动刷新功能
document.addEventListener('alpine:init', () => {
    Alpine.data('monitorData', () => ({
        init() {
            if (this.autoRefresh) {
                this.startAutoRefresh();
            }
        },
        
        startAutoRefresh() {
            this.refreshInterval = setInterval(() => {
                if (this.autoRefresh) {
                    location.reload();
                }
            }, 30000); // 30秒刷新一次
        },
        
        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }
    }));
});
</script>
@endsection 