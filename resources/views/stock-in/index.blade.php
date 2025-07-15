@extends('layouts.app')

@section('title', '入库管理')
@section('header', '入库管理')

@section('content')
<div class="space-y-8" x-data="{ 
    selectedView: 'all',
    showFilters: false,
    selectedRecords: [],
    batchAction: '',
    showBatchMenu: false
}">
    <!-- 现代化页面头部 -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <i class="bi bi-box-arrow-in-down text-3xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold mb-2">智能入库管理</h1>
                    <p class="text-green-100 text-lg">全方位入库记录管理，支持多仓库协同操作</p>
                    <div class="flex items-center mt-3 space-x-4 text-sm">
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-shield-check mr-1"></i>
                            批次管理
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-graph-up mr-1"></i>
                            实时统计
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-people mr-1"></i>
                            协同操作
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3">
                <button @click="showFilters = !showFilters" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl font-medium text-white hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200">
                    <i class="bi bi-funnel mr-2"></i>
                    高级筛选
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl font-medium text-white hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200">
                        <i class="bi bi-download mr-2"></i>
                        批量操作
                        <i class="bi bi-chevron-down ml-2"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="bi bi-file-earmark-excel mr-2"></i>导出Excel
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="bi bi-file-earmark-pdf mr-2"></i>导出PDF
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="bi bi-printer mr-2"></i>打印记录
                        </a>
                    </div>
                </div>
                <a href="{{ route('stock-ins.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white text-green-700 border border-transparent rounded-xl font-semibold hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200 shadow-lg">
                    <i class="bi bi-plus-circle mr-2"></i>
                    新增入库
                </a>
            </div>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- 今日入库 -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <i class="bi bi-calendar-date text-white text-xl"></i>
                </div>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full">今日</span>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $records->where('created_at', '>=', today())->count() }}</h3>
                <p class="text-gray-600 text-sm font-medium">今日入库笔数</p>
                <div class="mt-2 flex items-center text-xs">
                    <span class="text-green-600 font-medium">+12.5%</span>
                    <span class="text-gray-500 ml-1">较昨天</span>
                </div>
            </div>
        </div>

        <!-- 今日金额 -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                    <i class="bi bi-currency-dollar text-white text-xl"></i>
                </div>
                <span class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1 rounded-full">金额</span>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-1">¥{{ number_format($records->where('created_at', '>=', today())->sum('total_amount'), 0) }}</h3>
                <p class="text-gray-600 text-sm font-medium">今日入库金额</p>
                <div class="mt-2 flex items-center text-xs">
                    <span class="text-green-600 font-medium">+8.3%</span>
                    <span class="text-gray-500 ml-1">较昨天</span>
                </div>
            </div>
        </div>

        <!-- 总记录数 -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="bi bi-archive text-white text-xl"></i>
                </div>
                <span class="bg-purple-100 text-purple-800 text-xs font-medium px-3 py-1 rounded-full">总计</span>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($records->total()) }}</h3>
                <p class="text-gray-600 text-sm font-medium">历史入库记录</p>
                <div class="mt-2 flex items-center text-xs">
                    <span class="text-blue-600 font-medium">{{ $records->count() }}</span>
                    <span class="text-gray-500 ml-1">本页显示</span>
                </div>
            </div>
        </div>

        <!-- 活跃供应商 -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
                    <i class="bi bi-truck text-white text-xl"></i>
                </div>
                <span class="bg-orange-100 text-orange-800 text-xs font-medium px-3 py-1 rounded-full">供应商</span>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $records->pluck('supplier')->filter()->unique()->count() }}</h3>
                <p class="text-gray-600 text-sm font-medium">活跃供应商数</p>
                <div class="mt-2 flex items-center text-xs">
                    <span class="text-orange-600 font-medium">3</span>
                    <span class="text-gray-500 ml-1">新增本月</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 高级筛选区 -->
    <div x-show="showFilters" x-transition class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="bi bi-funnel mr-2 text-green-600"></i>
                高级筛选条件
            </h3>
            <button @click="showFilters = false" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg"></i>
            </button>
                </div>

        <form action="{{ route('stock-ins.index') }}" method="GET" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- 仓库选择 -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="bi bi-building mr-1 text-green-600"></i>
                        仓库选择
                    </label>
                    <select name="store_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                        <option value="">🏢 所有仓库</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                <!-- 供应商筛选 -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="bi bi-truck mr-1 text-green-600"></i>
                        供应商
                    </label>
                    <select name="supplier" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                        <option value="">🚚 所有供应商</option>
                        @foreach($records->pluck('supplier')->filter()->unique() as $supplier)
                            <option value="{{ $supplier }}" {{ request('supplier') == $supplier ? 'selected' : '' }}>
                                {{ $supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 时间范围 -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="bi bi-calendar-range mr-1 text-green-600"></i>
                        时间范围
                    </label>
                    <select name="date_range" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                        <option value="">📅 所有时间</option>
                        <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>📆 今天</option>
                        <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>📋 本周</option>
                        <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>📊 本月</option>
                        <option value="quarter" {{ request('date_range') == 'quarter' ? 'selected' : '' }}>📈 本季度</option>
                    </select>
                </div>

                <!-- 金额范围 -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="bi bi-currency-dollar mr-1 text-green-600"></i>
                        金额范围
                    </label>
                    <div class="flex space-x-2">
                        <input type="number" name="amount_min" placeholder="最小金额" value="{{ request('amount_min') }}" class="w-1/2 px-3 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors text-sm">
                        <input type="number" name="amount_max" placeholder="最大金额" value="{{ request('amount_max') }}" class="w-1/2 px-3 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors text-sm">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="text-sm text-gray-600">
                    <i class="bi bi-info-circle mr-1"></i>
                    筛选条件将应用于当前页面的所有记录
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" @click="showFilters = false" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200 font-semibold">
                        <i class="bi bi-x-circle mr-2"></i>
                        取消
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 font-semibold shadow-lg">
                        <i class="bi bi-search mr-2"></i>
                        应用筛选
                    </button>
                            </div>
                        </div>
                    </form>
    </div>

    <!-- 入库记录列表 -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">入库记录列表</h3>
                    <p class="text-gray-600">管理所有入库记录，支持查看详情和批量操作</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                        共 {{ $records->total() }} 条记录
                    </span>
                </div>
            </div>
        </div>

        @if($records->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">入库信息</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">仓库/供应商</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">金额统计</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">操作员/时间</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">操作</th>
                                </tr>
                            </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($records as $record)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">#{{ $record->id }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">入库单号: IN-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $record->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <i class="bi bi-building mr-1 text-blue-600"></i>
                                        {{ $record->store->name ?? '未指定仓库' }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <i class="bi bi-truck mr-1 text-orange-600"></i>
                                        {{ $record->supplier ?? '暂无供应商' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="space-y-1">
                                    <div class="text-lg font-bold text-blue-600">¥{{ number_format($record->total_amount, 2) }}</div>
                                    <div class="text-xs text-gray-500">销售总额</div>
                                    <div class="text-sm font-medium text-green-600">¥{{ number_format($record->total_cost, 2) }}</div>
                                    <div class="text-xs text-gray-500">成本总额</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <i class="bi bi-person-circle mr-1 text-purple-600"></i>
                                        {{ $record->user->real_name ?? '系统用户' }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <i class="bi bi-clock mr-1 text-gray-400"></i>
                                        {{ $record->created_at->format('Y-m-d H:i:s') }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('stock-ins.show', $record) }}" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" title="查看详情">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                        @if($record->canDelete())
                                        <button onclick="if(confirm('确定要删除这条入库记录吗？')) { document.getElementById('delete-form-{{ $record->id }}').submit(); }" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" title="删除">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $record->id }}" action="{{ route('stock-ins.destroy', $record) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
        @else
            <!-- 空状态 -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-box-arrow-in-down text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">暂无入库记录</h3>
                <p class="text-gray-500 mb-8">开始创建第一条入库记录</p>
                <a href="{{ route('stock-ins.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 font-semibold shadow-lg">
                    <i class="bi bi-plus-circle mr-2"></i>
                    创建入库记录
                </a>
            </div>
        @endif
    </div>

    <!-- 分页 -->
    @if($records->hasPages())
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 px-6 py-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="text-sm text-gray-700 mb-4 lg:mb-0">
                显示 <span class="font-semibold text-green-600">{{ $records->firstItem() }}</span> 到 <span class="font-semibold text-green-600">{{ $records->lastItem() }}</span> 条，共 <span class="font-semibold text-green-600">{{ $records->total() }}</span> 条记录
            </div>
            <div class="flex items-center space-x-2">
                {{ $records->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection 