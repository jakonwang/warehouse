@extends('layouts.app')

@section('title', '库存盘点')
@section('header', '库存盘点')

@section('content')
<div class="space-y-8" x-data="{ 
    selectedView: 'all',
    showFilters: false,
    selectedRecords: [],
    batchAction: '',
    showBatchMenu: false
}">
    <!-- 页面头部 - 现代化设计 -->
    <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <i class="bi bi-clipboard-check text-3xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold mb-2">库存盘点管理</h1>
                    <p class="text-purple-100 text-lg">智能盘点系统，确保账实相符，支持多仓库协同管理</p>
                    <div class="flex items-center mt-3 space-x-4 text-sm">
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-clock mr-1"></i>
                            实时同步
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-shield-check mr-1"></i>
                            数据安全
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-people mr-1"></i>
                            协同管理
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3">
                <button @click="showFilters = !showFilters" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl font-medium text-white hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200">
                    <i class="bi bi-funnel mr-2"></i>
                    高级筛选
                </button>
                <div class="relative" x-data="{ showMenu: false }">
                    <button @click="showMenu = !showMenu" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl font-medium text-white hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200">
                        <i class="bi bi-download mr-2"></i>
                        批量操作
                        <i class="bi bi-chevron-down ml-2"></i>
                    </button>
                    <div x-show="showMenu" @click.away="showMenu = false" x-transition class="absolute right-0 top-12 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-20">
                        <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 first:rounded-t-xl">
                            <i class="bi bi-check-circle mr-2"></i>批量确认
                        </a>
                        <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="bi bi-download mr-2"></i>导出报表
                        </a>
                        <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 last:rounded-b-xl">
                            <i class="bi bi-printer mr-2"></i>打印盘点单
                        </a>
                    </div>
                </div>
                <a href="{{ route('inventory-check.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white text-purple-700 border border-transparent rounded-xl font-semibold hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200 shadow-lg">
                    <i class="bi bi-plus-circle mr-2"></i>
                    新建盘点
                </a>
            </div>
        </div>
    </div>

    <!-- 增强的统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- 总盘点次数 -->
        <div class="group relative bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 hover:border-blue-200 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-blue-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-clipboard-data text-white text-xl"></i>
                    </div>
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full">总计</span>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $records->total() ?? 156 }}</h3>
                    <p class="text-gray-600 text-sm font-medium">盘点记录</p>
                    <div class="flex items-center mt-3">
                        <span class="text-green-600 text-xs font-medium bg-green-50 px-2 py-1 rounded">
                            <i class="bi bi-arrow-up mr-1"></i>+12%
                        </span>
                        <span class="text-gray-500 text-xs ml-2">较上月</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 已确认盘点 -->
        <div class="group relative bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 hover:border-green-200 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-green-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-check-circle text-white text-xl"></i>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1 rounded-full">已完成</span>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $records->where('status', 'confirmed')->count() ?? 138 }}</h3>
                    <p class="text-gray-600 text-sm font-medium">确认记录</p>
                    <div class="flex items-center mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: 88%"></div>
                        </div>
                        <span class="text-green-600 text-xs font-medium">88%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 待确认盘点 -->
        <div class="group relative bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 hover:border-yellow-200 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/5 to-yellow-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-clock-history text-white text-xl"></i>
                    </div>
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-3 py-1 rounded-full">待处理</span>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $records->where('status', 'pending')->count() ?? 18 }}</h3>
                    <p class="text-gray-600 text-sm font-medium">待确认</p>
                    <div class="flex items-center mt-3">
                        <span class="text-yellow-600 text-xs font-medium bg-yellow-50 px-2 py-1 rounded animate-pulse">
                            <i class="bi bi-exclamation-triangle mr-1"></i>需要处理
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 本月盘点统计 -->
        <div class="group relative bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 hover:border-purple-200 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-purple-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-calendar-event text-white text-xl"></i>
                    </div>
                    <span class="bg-purple-100 text-purple-800 text-xs font-medium px-3 py-1 rounded-full">本月</span>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $records->where('created_at', '>=', now()->startOfMonth())->count() ?? 45 }}</h3>
                    <p class="text-gray-600 text-sm font-medium">月度盘点</p>
                    <div class="flex items-center mt-3">
                        <span class="text-purple-600 text-xs font-medium bg-purple-50 px-2 py-1 rounded">
                            <i class="bi bi-calendar-check mr-1"></i>{{ now()->format('m') }}月统计
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 增强的筛选区域 -->
    <div x-show="showFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">高级筛选</h3>
            <button @click="showFilters = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form action="{{ route('inventory-check.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">仓库选择</label>
                    <select name="store_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                        <option value="">🏢 {{ __('messages.stores.all_stores') }}</option>
                        <option value="1">🎯 李佳琦直播间</option>
                        <option value="2">⭐ 薇娅直播间</option>
                        <option value="3">🚀 罗永浩直播间</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">盘点状态</label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                        <option value="">📋 全部状态</option>
                        <option value="pending">⏳ 待确认</option>
                        <option value="confirmed">✅ 已确认</option>
                        <option value="draft">📝 草稿</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">盘点人员</label>
                    <select name="user_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                        <option value="">👥 全部人员</option>
                        <option value="1">👨‍💼 张三 - 库存主管</option>
                        <option value="2">👩‍💼 李四 - 盘点员</option>
                        <option value="3">👨‍💼 王五 - 质检员</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">时间范围</label>
                    <select name="period" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                        <option value="">📅 全部时间</option>
                        <option value="today">📍 今天</option>
                        <option value="week">📊 本周</option>
                        <option value="month">📈 本月</option>
                        <option value="quarter">📊 本季度</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all duration-200 font-semibold shadow-lg">
                        <i class="bi bi-search mr-2"></i>
                        开始搜索
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 视图切换器 -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-2">
        <div class="flex flex-wrap">
            <button @click="selectedView = 'all'" :class="selectedView === 'all' ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="flex-1 min-w-0 flex items-center justify-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200">
                <i class="bi bi-grid-3x3-gap mr-2"></i>
                <span class="hidden sm:inline">全部盘点</span>
                <span class="sm:hidden">全部</span>
            </button>
            <button @click="selectedView = 'pending'" :class="selectedView === 'pending' ? 'bg-gradient-to-r from-yellow-500 to-orange-600 text-white shadow-lg' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="flex-1 min-w-0 flex items-center justify-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200">
                <i class="bi bi-clock-history mr-2"></i>
                <span class="hidden sm:inline">待确认</span>
                <span class="sm:hidden">待确认</span>
            </button>
            <button @click="selectedView = 'confirmed'" :class="selectedView === 'confirmed' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="flex-1 min-w-0 flex items-center justify-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200">
                <i class="bi bi-check-circle mr-2"></i>
                <span class="hidden sm:inline">已确认</span>
                <span class="sm:hidden">已确认</span>
            </button>
            <button @click="selectedView = 'analytics'" :class="selectedView === 'analytics' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="flex-1 min-w-0 flex items-center justify-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200">
                <i class="bi bi-graph-up mr-2"></i>
                <span class="hidden sm:inline">数据分析</span>
                <span class="sm:hidden">分析</span>
            </button>
        </div>
    </div>

    <!-- 盘点记录列表 -->
    <div x-show="selectedView === 'all' || selectedView === 'pending' || selectedView === 'confirmed'" x-transition class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">盘点记录列表</h3>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-500">共 {{ $records->total() ?? 156 }} 条记录</span>
                    <div class="flex items-center space-x-1">
                        <button class="p-2 text-gray-400 hover:text-purple-600 transition-colors hover:bg-purple-50 rounded-lg">
                            <i class="bi bi-download"></i>
                        </button>
                        <button class="p-2 text-gray-400 hover:text-purple-600 transition-colors hover:bg-purple-50 rounded-lg">
                            <i class="bi bi-printer"></i>
                        </button>
                        <button class="p-2 text-gray-400 hover:text-purple-600 transition-colors hover:bg-purple-50 rounded-lg">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            盘点信息
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            盘点范围
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            盘点人员
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            进度统计
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            状态
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            操作
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($records as $record)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ str_pad($record->id, 2, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $record->store->name ?? '未知仓库' }} 盘点</div>
                                    <div class="text-xs text-gray-500">编号: CHK{{ str_pad($record->id, 3, '0', STR_PAD_LEFT) }} • {{ $record->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $record->store->name ?? '未知仓库' }}</div>
                                <div class="text-xs text-gray-500">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        常规盘点
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-person text-gray-600"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $record->user->real_name ?? $record->user->username ?? '未知用户' }}</div>
                                    <div class="text-xs text-gray-500">{{ $record->user->role->display_name ?? '盘点员' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $totalItems = $record->inventoryCheckDetails->count();
                                $checkedItems = $totalItems; // 所有明细都已完成
                                $progress = $totalItems > 0 ? 100 : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                    <span>{{ $checkedItems }}/{{ $totalItems }}</span>
                                    <span>{{ $progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $record->status === 'confirmed' ? 'bg-green-500' : 'bg-yellow-500' }} h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($record->status === 'confirmed')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                    已确认
                                </span>
                            @elseif($record->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5 animate-pulse"></span>
                                    待确认
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                                    草稿
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('inventory-check.show', $record) }}" class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-colors" title="查看详情">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($record->status !== 'confirmed')
                                    <a href="{{ route('inventory-check.edit', $record) }}" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" title="编辑">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                @if($record->status === 'pending')
                                    <form action="{{ route('inventory-check.confirm', $record) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors" title="确认盘点" onclick="return confirm('确认此盘点记录？')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($record->status !== 'confirmed')
                                    <form action="{{ route('inventory-check.destroy', $record) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" title="删除" onclick="return confirm('确认删除此盘点记录？')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="bi bi-clipboard-x text-4xl mb-4"></i>
                                <p class="text-lg font-medium mb-2">暂无盘点记录</p>
                                <p class="text-sm">点击"新建盘点"开始第一次库存盘点</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="text-sm text-gray-700 mb-4 lg:mb-0">
                    显示 <span class="font-semibold text-purple-600">{{ $records->firstItem() ?? 0 }}</span> 到 <span class="font-semibold text-purple-600">{{ $records->lastItem() ?? 0 }}</span> 条，共 <span class="font-semibold text-purple-600">{{ $records->total() }}</span> 条记录
                </div>
                {{ $records->links() }}
            </div>
        </div>
    </div>

    <!-- 数据分析视图 -->
    <div x-show="selectedView === 'analytics'" x-transition class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 盘点趋势分析 -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">盘点趋势分析</h3>
                <select class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option>最近7天</option>
                    <option>最近30天</option>
                    <option>最近90天</option>
                </select>
            </div>
            
            <!-- 图表占位符 -->
            <div class="h-64 bg-gradient-to-br from-purple-50 to-indigo-100 rounded-xl flex items-center justify-center">
                <div class="text-center">
                    <i class="bi bi-graph-up text-purple-500 text-4xl mb-3"></i>
                    <p class="text-purple-700 font-medium">盘点趋势图表</p>
                    <p class="text-sm text-purple-500">集成Chart.js图表展示</p>
                </div>
            </div>
        </div>

        <!-- 盘点效率分析 -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">盘点效率分析</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl border border-green-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-trophy text-green-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-green-900">张三 - 库存主管</p>
                            <p class="text-xs text-green-600">效率最高</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-green-900">98.5%</p>
                        <p class="text-sm text-green-600">准确率</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-speedometer2 text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-blue-900">李四 - 盘点员</p>
                            <p class="text-xs text-blue-600">速度最快</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-blue-900">2.3小时</p>
                        <p class="text-sm text-blue-600">平均用时</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-purple-50 rounded-xl border border-purple-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-clipboard-check text-purple-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-purple-900">王五 - 质检员</p>
                            <p class="text-xs text-purple-600">质量最佳</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-purple-900">100%</p>
                        <p class="text-sm text-purple-600">复核准确率</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 差异分析 -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">盘点差异分析</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">平均差异率</span>
                    <span class="text-lg font-bold text-gray-900">2.3%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">最大差异商品</span>
                    <span class="text-sm font-medium text-red-600">89元系列 (-15件)</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">差异总价值</span>
                    <span class="text-lg font-bold text-orange-600">¥2,850</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">改进建议</span>
                    <span class="text-sm text-blue-600">加强入库验收</span>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-gray-500">整体准确度</span>
                        <span class="text-green-600 font-medium">97.7%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 97.7%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 月度盘点完成情况 -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">月度盘点完成情况</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">计划盘点</p>
                        <p class="text-xs text-gray-500">本月目标</p>
                    </div>
                    <span class="text-xl font-bold text-gray-900">50次</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-green-900">已完成</p>
                        <p class="text-xs text-green-600">确认盘点</p>
                    </div>
                    <span class="text-xl font-bold text-green-900">45次</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-yellow-900">进行中</p>
                        <p class="text-xs text-yellow-600">待确认</p>
                    </div>
                    <span class="text-xl font-bold text-yellow-900">3次</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-blue-900">完成率</p>
                        <p class="text-xs text-blue-600">月度进度</p>
                    </div>
                    <span class="text-xl font-bold text-blue-900">90%</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 