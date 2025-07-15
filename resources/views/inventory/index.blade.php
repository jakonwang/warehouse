@extends('layouts.app')

@section('title', __('messages.inventory.title'))
@section('header', __('messages.inventory.title'))

@section('content')
<div class="space-y-6">
    <!-- 顶部数据概览 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium"><x-lang key="messages.inventory.total_inventory"/></p>
                    <p class="text-4xl font-bold">{{ number_format($inventory->sum('quantity')) }}</p>
                    <p class="text-blue-200 text-xs mt-1"><x-lang key="messages.inventory.increase_from_last_month"/></p>
                </div>
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                    <i class="bi bi-boxes text-3xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-700 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm font-medium"><x-lang key="messages.inventory.inventory_value"/></p>
                    <p class="text-4xl font-bold">
                        ¥{{ number_format($inventory->sum(function($item){ return $item->quantity * ($item->product->cost_price ?? 0); })) }}
                    </p>
                    <p class="text-emerald-200 text-xs mt-1"><x-lang key="messages.inventory.increase_from_last_month"/></p>
                </div>
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                    <i class="bi bi-currency-dollar text-3xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-500 via-orange-600 to-orange-700 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium"><x-lang key="messages.inventory.low_stock_warning"/></p>
                    <p class="text-4xl font-bold">
                        {{ $inventory->filter(function($item){ return $item->quantity <= ($item->min_quantity ?? 0); })->count() }}
                    </p>
                    <p class="text-orange-200 text-xs mt-1"><x-lang key="messages.inventory.need_restock"/></p>
                </div>
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                    <i class="bi bi-exclamation-triangle text-3xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 via-purple-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium"><x-lang key="messages.inventory.turnover_rate"/></p>
                    <p class="text-4xl font-bold">2.3</p>
                    <p class="text-purple-200 text-xs mt-1"><x-lang key="messages.inventory.times_per_month"/></p>
                </div>
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                    <i class="bi bi-arrow-repeat text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 快速操作面板 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900"><x-lang key="messages.inventory.quick_actions"/></h3>
            <div class="flex items-center space-x-2">
                <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                    <i class="bi bi-gear"></i>
                </button>
                <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                    <i class="bi bi-question-circle"></i>
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="{{ route('inventory.create') }}" class="group bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 text-center hover:from-blue-100 hover:to-blue-200 transition-all duration-200 transform hover:-translate-y-1">
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-600 transition-colors">
                    <i class="bi bi-plus-lg text-white text-xl"></i>
                </div>
                <p class="text-sm font-medium text-blue-700"><x-lang key="messages.inventory.add_inventory"/></p>
            </a>
            
            <a href="{{ route('stock-ins.create') }}" class="group bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 text-center hover:from-green-100 hover:to-green-200 transition-all duration-200 transform hover:-translate-y-1">
                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-green-600 transition-colors">
                    <i class="bi bi-box-arrow-in-down text-white text-xl"></i>
                </div>
                <p class="text-sm font-medium text-green-700"><x-lang key="messages.inventory.batch_inbound"/></p>
            </a>
            
            <a href="{{ route('stock-outs.create') }}" class="group bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 text-center hover:from-orange-100 hover:to-orange-200 transition-all duration-200 transform hover:-translate-y-1">
                <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-orange-600 transition-colors">
                    <i class="bi bi-box-arrow-up text-white text-xl"></i>
                </div>
                <p class="text-sm font-medium text-orange-700"><x-lang key="messages.inventory.batch_outbound"/></p>
            </a>
            
            <a href="{{ route('inventory.check') }}" class="group bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 text-center hover:from-purple-100 hover:to-purple-200 transition-all duration-200 transform hover:-translate-y-1">
                <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-purple-600 transition-colors">
                    <i class="bi bi-clipboard-check text-white text-xl"></i>
                </div>
                <p class="text-sm font-medium text-purple-700"><x-lang key="messages.inventory.inventory_check"/></p>
            </a>
            
            <a href="{{ route('inventory.low-stock') }}" class="group bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 text-center hover:from-red-100 hover:to-red-200 transition-all duration-200 transform hover:-translate-y-1">
                <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-red-600 transition-colors">
                    <i class="bi bi-exclamation-triangle text-white text-xl"></i>
                </div>
                <p class="text-sm font-medium text-red-700"><x-lang key="messages.inventory.low_stock"/></p>
            </a>
            
            <a href="{{ route('statistics.inventory-turnover') }}" class="group bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 text-center hover:from-gray-100 hover:to-gray-200 transition-all duration-200 transform hover:-translate-y-1">
                <div class="w-12 h-12 bg-gray-500 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-gray-600 transition-colors">
                    <i class="bi bi-graph-up text-white text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-700"><x-lang key="messages.inventory.reports"/></p>
            </a>
            <!-- 新增：仓库调拨快捷操作按钮 -->
            <a href="{{ route('store-transfers.index') }}" class="group bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl p-4 text-center hover:from-teal-100 hover:to-teal-200 transition-all duration-200 transform hover:-translate-y-1">
                <div class="w-12 h-12 bg-teal-500 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-teal-600 transition-colors">
                    <i class="bi bi-arrow-left-right text-white text-xl"></i>
                </div>
                <p class="text-sm font-medium text-teal-700">仓库调拨</p>
            </a>
        </div>
    </div>

    <!-- 主要内容区域 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- 工具栏 -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <h3 class="text-xl font-semibold text-gray-900"><x-lang key="messages.inventory.inventory_list"/></h3>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">{{ $inventory->total() }} <x-lang key="messages.inventory.items"/></span>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <button onclick="selectAll()" class="text-sm text-gray-500 hover:text-gray-700"><x-lang key="messages.inventory.select_all"/></button>
                        <span class="text-gray-300">|</span>
                        <button onclick="batchOperation()" class="text-sm text-gray-500 hover:text-gray-700"><x-lang key="messages.inventory.batch_operations"/></button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('inventory.export') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                            <i class="bi bi-download"></i>
                        </a>
                        <button onclick="printData()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                            <i class="bi bi-printer"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- 高级筛选 -->
            <div class="mt-4">
                <form class="flex flex-wrap gap-3 items-center">
                    <div class="flex-1 min-w-64">
                        <input type="text" name="keyword" placeholder="<x-lang key="messages.inventory.search_placeholder"/>" 
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" 
                               value="{{ request('keyword') }}">
                    </div>
                    
                    <select name="status" class="px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value=""><x-lang key="messages.inventory.all_status"/></option>
                        <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}><x-lang key="messages.inventory.normal_stock"/></option>
                        <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}><x-lang key="messages.inventory.insufficient_stock"/></option>
                        <option value="overstock" {{ request('status') == 'overstock' ? 'selected' : '' }}><x-lang key="messages.inventory.overstock"/></option>
                    </select>
                    
                    <div class="flex gap-2">
                        <input type="number" name="min_quantity" placeholder="<x-lang key="messages.inventory.min_stock"/>" 
                               class="w-24 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" 
                               value="{{ request('min_quantity') }}">
                        <input type="number" name="max_quantity" placeholder="<x-lang key="messages.inventory.max_stock"/>" 
                               class="w-24 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" 
                               value="{{ request('max_quantity') }}">
                    </div>
                    
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-all duration-200">
                        <i class="bi bi-search mr-1"></i><x-lang key="messages.inventory.search"/>
                    </button>
                </form>
            </div>
        </div>

        <!-- 数据表格 -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.inventory.product_info"/></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.inventory.stock_quantity"/></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.inventory.stock_status"/></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.inventory.last_check"/></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.inventory.actions"/></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if($inventory->count() > 0)
                        @foreach($inventory as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" value="{{ $item->id }}" class="item-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-box text-white text-sm"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 product-name">{{ $item->product->name ?? '未知商品' }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->product->code ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($item->quantity) }}</div>
                                <div class="text-xs text-gray-500 min-quantity">{{ $item->min_quantity }}</div>
                                <div class="text-xs text-gray-500 max-quantity">{{ $item->max_quantity }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->quantity <= $item->min_quantity)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                        库存不足
                                    </span>
                                @elseif($item->quantity >= $item->max_quantity)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                        库存过多
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                        库存正常
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 last-check-date">
                                {{ $item->last_check_date ? date('Y-m-d', strtotime($item->last_check_date)) : '未盘点' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button 
                                        class="text-purple-600 hover:text-purple-700 hover:bg-purple-50 px-2 py-1 rounded transition-all duration-200"
                                        @click="$dispatch('open-quick-adjust', {id: {{ $item->id }}, quantity: {{ $item->quantity }}})"
                                    >
                                        <i class="bi bi-plus-circle mr-1"></i>调整
                                    </button>
                                    <a href="{{ route('inventory.show', $item) }}" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-2 py-1 rounded transition-all duration-200">
                                        <i class="bi bi-eye mr-1"></i>详情
                                    </a>
                                    <a href="{{ route('inventory.edit', $item) }}" class="text-green-600 hover:text-green-700 hover:bg-green-50 px-2 py-1 rounded transition-all duration-200">
                                        <i class="bi bi-pencil mr-1"></i>编辑
                                    </a>
                                    <button class="text-orange-600 hover:text-orange-700 hover:bg-orange-50 px-2 py-1 rounded transition-all duration-200" onclick="openCheckModal({{ $item->id }}, '{{ $item->product->name ?? '未知商品' }}', {{ $item->quantity }})">
                                        <i class="bi bi-clipboard-check mr-1"></i>盘点
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="bi bi-box text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">暂无库存数据</h3>
                                <p class="text-gray-500">当前没有库存记录，请先添加商品或进行入库操作。</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        @if($inventory->count() > 0)
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $inventory->links() }}
            </div>
        @endif
    </div>
</div>

{{-- 快速调整弹窗组件 --}}
<div 
    x-data="quickAdjustModal()" 
    x-show="show" 
    x-cloak 
    x-init="init()"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm"
>
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative transform transition-all duration-300" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <button @click="close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors duration-200">
            <i class="bi bi-x-lg text-xl"></i>
        </button>
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-plus-circle text-white text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900">快速调整库存</h3>
        </div>
        
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4">
                <div class="text-sm text-gray-600 mb-1">当前库存</div>
                <div class="text-2xl font-bold text-gray-900" x-text="currentQuantity">0</div>
                <div class="text-xs text-gray-500">件</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">调整数量（可正可负）</label>
                <input type="number" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" x-model="adjustQuantity" placeholder="如：+10 或 -5">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">调整原因</label>
                <input type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" x-model="reason" placeholder="如：盘点修正、损耗等">
            </div>
            
            <template x-if="error">
                <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                    <div class="flex items-center">
                        <i class="bi bi-exclamation-circle text-red-500 mr-2"></i>
                        <span class="text-red-700 text-sm" x-text="error"></span>
                    </div>
                </div>
            </template>
            
            <div class="flex space-x-3 pt-4">
                <button @click="close" type="button" class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 font-medium">取消</button>
                <button @click="submit" type="button" class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:from-purple-600 hover:to-purple-700 transition-all duration-200 shadow-sm hover:shadow-md" :disabled="loading">
                    <span x-show="!loading">确认调整</span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <i class="bi bi-arrow-repeat animate-spin mr-2"></i>提交中...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 盘点弹窗组件 --}}
<div 
    x-data="checkModal()" 
    x-show="checkShow" 
    x-cloak 
    x-init="init()"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm"
>
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative transform transition-all duration-300" x-show="checkShow" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <button @click="close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors duration-200">
            <i class="bi bi-x-lg text-xl"></i>
        </button>
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-clipboard-check text-white text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900">库存盘点</h3>
        </div>
        
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4">
                <div class="text-sm text-gray-600 mb-1">商品名称</div>
                <div class="text-2xl font-bold text-gray-900" x-text="productName"></div>
                <div class="text-xs text-gray-500">件</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">当前库存</label>
                <div class="text-2xl font-bold text-gray-900" x-text="currentQuantity"></div>
                <div class="text-xs text-gray-500">件</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">调整后库存</label>
                <input type="number" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" x-model="newQuantity" placeholder="请输入调整后的库存数量">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">调整原因</label>
                <input type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" x-model="reason" placeholder="如：盘点修正、损耗等">
            </div>
            
            <template x-if="error">
                <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                    <div class="flex items-center">
                        <i class="bi bi-exclamation-circle text-red-500 mr-2"></i>
                        <span class="text-red-700 text-sm" x-text="error"></span>
                    </div>
                </div>
            </template>
            
            <div class="flex space-x-3 pt-4">
                <button @click="close" type="button" class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 font-medium">取消</button>
                <button @click="submit" type="button" class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md" :disabled="loading">
                    <span x-show="!loading">确认盘点</span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <i class="bi bi-arrow-repeat animate-spin mr-2"></i>盘点中...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Alpine 组件定义 --}}
<script>
function quickAdjustModal() {
    return {
        show: false,
        inventoryId: null,
        currentQuantity: 0,
        adjustQuantity: '',
        reason: '',
        loading: false,
        error: '',
        init() {
            window.addEventListener('open-quick-adjust', e => {
                this.open(e.detail.id, e.detail.quantity);
            });
        },
        open(id, quantity) {
            this.inventoryId = id;
            this.currentQuantity = quantity;
            this.adjustQuantity = '';
            this.reason = '';
            this.error = '';
            this.show = true;
        },
        close() { this.show = false; },
        submit() {
            if (this.adjustQuantity === '' || isNaN(this.adjustQuantity)) {
                this.error = '请输入调整数量';
                return;
            }
            this.loading = true;
            fetch(`/inventory/${this.inventoryId}/quick-adjust`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    adjust_quantity: this.adjustQuantity,
                    reason: this.reason
                })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    window.location.reload();
                } else {
                    this.error = data.message || '调整失败';
                }
            })
            .catch(() => {
                this.loading = false;
                this.error = '网络错误，请重试';
            });
        }
    }
}

function checkModal() {
    return {
        checkShow: false,
        inventoryId: null,
        productName: '',
        currentQuantity: 0,
        newQuantity: '',
        reason: '',
        loading: false,
        error: '',
        init() {
            window.addEventListener('open-check-modal', e => {
                this.open(e.detail.id, e.detail.name, e.detail.quantity);
            });
        },
        open(id, name, quantity) {
            this.inventoryId = id;
            this.productName = name;
            this.currentQuantity = quantity;
            this.newQuantity = '';
            this.reason = '';
            this.error = '';
            this.checkShow = true;
        },
        close() { this.checkShow = false; },
        submit() {
            if (this.newQuantity === '' || isNaN(this.newQuantity)) {
                this.error = '请输入调整后的库存数量';
                return;
            }
            this.loading = true;
            fetch(`/inventory/${this.inventoryId}/check`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    new_quantity: this.newQuantity,
                    reason: this.reason
                })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    window.location.reload();
                } else {
                    this.error = data.message || '盘点失败';
                }
            })
            .catch(() => {
                this.loading = false;
                this.error = '网络错误，请重试';
            });
        }
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('quickAdjustModal', quickAdjustModal);
    Alpine.data('checkModal', checkModal);
});

// 批量操作函数
function batchOperation() {
    const selectedItems = document.querySelectorAll('tbody tr input[type="checkbox"]:checked');
    if (selectedItems.length === 0) {
        alert('请至少选择一个库存项进行操作！');
        return;
    }

    const action = prompt('请输入批量操作类型 (quick-adjust, check, delete):');
    if (!action) {
        return;
    }

    const itemsToProcess = Array.from(selectedItems).map(checkbox => {
        const row = checkbox.closest('tr');
        const productName = row.querySelector('.product-name').textContent;
        const minQuantity = row.querySelector('.min-quantity').textContent;
        const maxQuantity = row.querySelector('.max-quantity').textContent;
        const lastCheckDate = row.querySelector('.last-check-date').textContent;

        return {
            id: checkbox.value,
            name: productName,
            quantity: 0, // 这个会在具体操作中设置
            min_quantity: parseInt(minQuantity),
            max_quantity: parseInt(maxQuantity),
            last_check_date: lastCheckDate
        };
    });

    if (action === 'quick-adjust') {
        const quantity = prompt('请输入统一的调整数量 (可正可负):');
        if (quantity === null) return;
        const reason = prompt('请输入调整原因:');
        if (reason === null) return;

        const confirm = confirm(`确定对选中的 ${itemsToProcess.length} 项进行快速调整，数量统一为 ${quantity}，原因: ${reason}？`);
        if (!confirm) return;

        const data = {
            action: 'batch_quick_adjust',
            items: itemsToProcess.map(item => ({
                id: item.id,
                quantity: parseInt(quantity),
                reason: reason
            }))
        };
        fetch('/inventory/batch-operation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                alert('批量快速调整成功！');
                window.location.reload();
            } else {
                alert('批量快速调整失败: ' + response.message);
            }
        })
        .catch(error => {
            console.error('批量快速调整失败:', error);
            alert('批量快速调整失败，请稍后再试。');
        });
    } else if (action === 'check') {
        const quantity = prompt('请输入统一的盘点数量:');
        if (quantity === null) return;
        const reason = prompt('请输入盘点原因:');
        if (reason === null) return;

        const confirm = confirm(`确定对选中的 ${itemsToProcess.length} 项进行盘点，数量统一为 ${quantity}，原因: ${reason}？`);
        if (!confirm) return;

        const data = {
            action: 'batch_check',
            items: itemsToProcess.map(item => ({
                id: item.id,
                quantity: parseInt(quantity),
                reason: reason
            }))
        };
        fetch('/inventory/batch-operation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                alert('批量盘点成功！');
                window.location.reload();
            } else {
                alert('批量盘点失败: ' + response.message);
            }
        })
        .catch(error => {
            console.error('批量盘点失败:', error);
            alert('批量盘点失败，请稍后再试。');
        });
    } else if (action === 'delete') {
        const confirm = confirm(`确定删除选中的 ${itemsToProcess.length} 项库存吗？此操作不可逆！`);
        if (!confirm) return;

        const data = {
            action: 'batch_delete',
            items: itemsToProcess.map(item => ({
                id: item.id
            }))
        };
        fetch('/inventory/batch-operation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                alert('批量删除成功！');
                window.location.reload();
            } else {
                alert('批量删除失败: ' + response.message);
            }
        })
        .catch(error => {
            console.error('批量删除失败:', error);
            alert('批量删除失败，请稍后再试。');
        });
    } else {
        alert('未知的批量操作类型！');
    }
}

// 全选/取消全选
function selectAll() {
    const checkboxes = document.querySelectorAll('tbody tr input[type="checkbox"]');
    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}

// 导出数据
function exportData() {
    window.location.href = '{{ route("inventory.export") }}';
}

// 打印数据
function printData() {
    window.print();
}

// 打开盘点弹窗
function openCheckModal(id, name, quantity) {
    window.dispatchEvent(new CustomEvent('open-check-modal', {
        detail: {
            id: id,
            name: name,
            quantity: quantity
        }
    }));
}

// 页面加载完成后的初始化
document.addEventListener('DOMContentLoaded', function() {
    console.log('库存管理页面加载完成');
    
    // 检查所有必要的元素是否存在
    const checkboxes = document.querySelectorAll('.item-checkbox');
    console.log('找到复选框数量:', checkboxes.length);
    
    // 检查Alpine.js组件是否正确初始化
    if (typeof Alpine !== 'undefined') {
        console.log('Alpine.js已加载');
    } else {
        console.error('Alpine.js未加载');
    }
    
    // 检查快速操作按钮
    const quickActionButtons = document.querySelectorAll('.grid.grid-cols-2 a');
    console.log('快速操作按钮数量:', quickActionButtons.length);
    
    // 为每个快速操作按钮添加点击事件监听
    quickActionButtons.forEach((button, index) => {
        button.addEventListener('click', function(e) {
            console.log('点击了快速操作按钮:', index, this.href);
        });
    });
});
</script>
@endsection 