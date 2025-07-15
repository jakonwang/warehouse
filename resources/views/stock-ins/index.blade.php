@extends('layouts.app')

@section('title', __('messages.stock_ins.title'))
@section('header', __('messages.stock_ins.title'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8 relative overflow-hidden">
    <!-- 装饰性背景元素 -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-200/30 to-purple-200/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-indigo-200/30 to-blue-200/30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 right-1/3 w-64 h-64 bg-gradient-to-br from-purple-200/20 to-pink-200/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/3 left-1/3 w-64 h-64 bg-gradient-to-br from-cyan-200/20 to-blue-200/20 rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 relative z-10" x-data="{ showFilters: false }">
        
        <!-- 顶部统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-blue-500/10 p-6 hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-calendar-day text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600"><x-lang key="messages.stock_ins.today_inbound"/></p>
                        <p class="text-2xl font-bold text-blue-700">{{ $stockIns->where('created_at', '>=', today())->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-green-500/10 p-6 hover:shadow-2xl hover:shadow-green-500/20 transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-boxes text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600"><x-lang key="messages.stock_ins.total_inbound"/></p>
                        <p class="text-2xl font-bold text-green-700">{{ $stockIns->sum(function($record) { return $record->stockInDetails->sum('quantity'); }) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-purple-500/10 p-6 hover:shadow-2xl hover:shadow-purple-500/20 transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-calendar3 text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600"><x-lang key="messages.stock_ins.this_month_inbound"/></p>
                        <p class="text-2xl font-bold text-purple-700">{{ $stockIns->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-orange-500/10 p-6 hover:shadow-2xl hover:shadow-orange-500/20 transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-people text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600"><x-lang key="messages.stock_ins.operators"/></p>
                        <p class="text-2xl font-bold text-orange-700">{{ $stockIns->pluck('user_id')->unique()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 操作栏 -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-gray-900"><x-lang key="messages.stock_ins.inbound_records"/></h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ __('messages.stock_ins.total_records', ['count' => $stockIns->total()]) }}
                </span>
            </div>
            
            <div class="flex items-center space-x-3">
                <button @click="showFilters = !showFilters" class="inline-flex items-center px-4 py-2 bg-white/80 backdrop-blur-xl border border-white/30 rounded-xl shadow-lg hover:shadow-xl hover:shadow-blue-500/20 transition-all duration-300 text-gray-700 font-medium hover:bg-white/90">
                    <i class="bi bi-funnel mr-2"></i>
                    <x-lang key="messages.stock_ins.filter"/>
                    <i class="bi bi-chevron-down ml-2 transition-transform duration-200" :class="{ 'rotate-180': showFilters }"></i>
                </button>
                
                <a href="{{ route('stock-ins.create') }}" class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/20 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="bi bi-plus-lg mr-2"></i>
                    <x-lang key="messages.stock_ins.add_inbound"/>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <!-- 筛选区域 -->
        <div x-show="showFilters" x-transition class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-indigo-500/10 p-6">
            <form method="GET" action="{{ route('stock-ins.index') }}">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stock_ins.store_filter"/></label>
                        <select name="store_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value=""><x-lang key="messages.stock_ins.all_stores"/></option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stock_ins.operator"/></label>
                        <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value=""><x-lang key="messages.stock_ins.all_operators"/></option>
                            @foreach($stockIns->pluck('user')->unique('id') as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->real_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stock_ins.date_range"/></label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <i class="bi bi-search mr-2"></i>
                            <x-lang key="messages.stock_ins.search"/>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 入库记录列表 -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-gray-500/10 overflow-hidden">
            @if($stockIns->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <x-lang key="messages.stock_ins.inbound_number"/>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <x-lang key="messages.stock_ins.store"/>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <x-lang key="messages.stock_ins.product_details"/>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <x-lang key="messages.stock_ins.total_quantity"/>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <x-lang key="messages.stock_ins.total_amount"/>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <x-lang key="messages.stock_ins.operator"/>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <x-lang key="messages.stock_ins.inbound_time"/>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <x-lang key="messages.stock_ins.actions"/>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/50 divide-y divide-gray-200">
                            @foreach($stockIns as $stockIn)
                            <tr class="hover:bg-white/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">#{{ $stockIn->id }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">IN-{{ str_pad($stockIn->id, 6, '0', STR_PAD_LEFT) }}</div>
                                            <div class="text-sm text-gray-500">入库单号</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $stockIn->store->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $stockIn->store->location ?? '未设置位置' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($stockIn->stockInDetails->take(3) as $detail)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $detail->product->name }}: {{ $detail->quantity }}件
                                            </span>
                                        @endforeach
                                        @if($stockIn->stockInDetails->count() > 3)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                +{{ $stockIn->stockInDetails->count() - 3 }}种
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="bi bi-boxes text-indigo-600"></i>
                                        </div>
                                        <div class="ml-3">
                                            <span class="text-lg font-bold text-gray-900">{{ $stockIn->stockInDetails->sum('quantity') }}</span>
                                            <span class="text-sm text-gray-500 ml-1">件</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">¥{{ number_format($stockIn->total_amount, 2) }}</div>
                                    <div class="text-sm text-gray-500">成本¥{{ number_format($stockIn->total_cost, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-xs font-medium">{{ substr($stockIn->user->real_name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $stockIn->user->real_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $stockIn->user->username }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ date('Y-m-d H:i', strtotime($stockIn->created_at)) }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($stockIn->created_at)->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('stock-ins.show', $stockIn) }}" 
                                           class="text-blue-600 hover:text-blue-900 transition-colors p-2 hover:bg-blue-50 rounded-lg"
                                           title="查看详情">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST" action="{{ route('stock-ins.destroy', $stockIn) }}" 
                                              class="inline-block"
                                              onsubmit="return confirm('确定要删除这条入库记录吗？删除后将回退相应的库存数量。')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 transition-colors p-2 hover:bg-red-50 rounded-lg"
                                                    title="删除记录">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 分页 -->
                <div class="bg-white/50 px-6 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            显示 <span class="font-medium">{{ $stockIns->firstItem() }}</span> 到 <span class="font-medium">{{ $stockIns->lastItem() }}</span> 条，共 <span class="font-medium">{{ $stockIns->total() }}</span> 条记录
                        </div>
                        <div class="flex items-center space-x-2">
                            {{ $stockIns->links() }}
                        </div>
                    </div>
                </div>
            @else
                <!-- 空状态 -->
                <div class="p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-box-arrow-in-down text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">暂无入库记录</h3>
                    <p class="text-gray-500 mb-6">开始创建第一个入库记录</p>
                    <a href="{{ route('stock-ins.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="bi bi-plus-lg mr-2"></i>
                        新增入库
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 仓库切换器功能
    const storeSwitcher = document.getElementById('store-switcher');
    if (storeSwitcher) {
        storeSwitcher.addEventListener('change', function() {
            const selectedStoreId = this.value;
            const currentUrl = new URL(window.location);
            
            if (selectedStoreId) {
                currentUrl.searchParams.set('store_id', selectedStoreId);
            } else {
                currentUrl.searchParams.delete('store_id');
            }
            
            // 保持其他筛选条件
            const userFilter = document.querySelector('select[name="user_id"]');
            if (userFilter && userFilter.value) {
                currentUrl.searchParams.set('user_id', userFilter.value);
            }
            
            const dateFilter = document.querySelector('input[name="date_from"]');
            if (dateFilter && dateFilter.value) {
                currentUrl.searchParams.set('date_from', dateFilter.value);
            }
            
            window.location.href = currentUrl.toString();
        });
    }
    
    // 同步筛选区域中的仓库选择器
    const filterStoreSelect = document.querySelector('select[name="store_id"]');
    if (filterStoreSelect && storeSwitcher) {
        filterStoreSelect.addEventListener('change', function() {
            storeSwitcher.value = this.value;
        });
        
        storeSwitcher.addEventListener('change', function() {
            filterStoreSelect.value = this.value;
        });
    }
});
</script>

@endsection 