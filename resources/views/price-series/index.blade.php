@extends('layouts.app')

@section('title', '价格系列配置')
@section('header', '价格系列配置')

@section('content')
<div class="space-y-6">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">价格系列配置</h2>
                <p class="mt-1 text-sm text-gray-600">管理盲袋商品的价格系列和成本配置</p>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" @click="showAddModal = true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-lg font-medium text-white hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="bi bi-plus-lg mr-2"></i>
                    添加价格系列
                </button>
            </div>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-tags text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-blue-100 text-sm">总系列数</p>
                    <p class="text-2xl font-bold">{{ $priceSeries->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-currency-dollar text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-green-100 text-sm">平均成本</p>
                    <p class="text-2xl font-bold">¥{{ number_format($priceSeries->avg('cost') ?? 0, 1) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-arrow-up-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-purple-100 text-sm">最高售价</p>
                    <p class="text-2xl font-bold">¥{{ $priceSeries->max('code') ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 价格系列列表 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200" x-data="{ 
        showAddModal: false, 
        editingItem: null,
        newSeries: { code: '', cost: '' },
        resetForm() {
            this.newSeries = { code: '', cost: '' };
            this.editingItem = null;
        },
        editItem(item) {
            this.editingItem = { ...item };
        }
    }">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">价格系列列表</h3>
            <p class="mt-1 text-sm text-gray-600"><x-lang key="messages.price_series.current_store_config"/></p>
        </div>

        <div class="p-6">
            @if($priceSeries->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($priceSeries as $series)
                    <div class="relative group">
                        <!-- 价格系列卡片 -->
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border-2 border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all duration-200">
                            <!-- 系列标识 -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold text-lg">¥</span>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-lg font-bold text-gray-900">{{ $series->code }}元系列</h4>
                                        <p class="text-sm text-gray-500">价格系列</p>
                                    </div>
                                </div>
                                <!-- 操作按钮 -->
                                <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editItem({{ $series->toJson() }})" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors">
                                        <i class="bi bi-pencil text-sm"></i>
                                    </button>
                                    <form action="{{ route('price-series.destroy', $series->code) }}" method="POST" class="inline" onsubmit="return confirm('确定要删除这个系列吗？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                            <i class="bi bi-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- 成本信息 -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                    <span class="text-sm text-gray-600">成本价格</span>
                                    <span class="text-lg font-semibold text-gray-900">¥{{ number_format($series->cost, 2) }}</span>
                                </div>
                                @if(auth()->user()->canViewProfitAndCost())
                                <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                    <span class="text-sm text-gray-600">利润率</span>
                                    <span class="text-lg font-semibold text-green-600">{{ number_format((($series->code - $series->cost) / $series->code) * 100, 1) }}%</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                    <span class="text-sm text-gray-600">利润金额</span>
                                    <span class="text-lg font-semibold text-blue-600">¥{{ number_format($series->code - $series->cost, 2) }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- 创建时间 -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-xs text-gray-500">
                                    <i class="bi bi-clock mr-1"></i>
                                    创建于 {{ $series->created_at->format('Y-m-d H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <!-- 空状态 -->
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-tags text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">暂无价格系列</h3>
                    <p class="text-gray-500 mb-6">开始添加第一个价格系列配置</p>
                    <button @click="showAddModal = true" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="bi bi-plus-lg mr-2"></i>
                        添加价格系列
                    </button>
                </div>
            @endif
        </div>

        <!-- 添加系列模态框 -->
        <div x-show="showAddModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showAddModal = false; resetForm()"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <form action="{{ route('price-series.store') }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="bi bi-plus-lg text-blue-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">添加价格系列</h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">系列编号</label>
                                            <input type="text" name="series_code" x-model="newSeries.code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('series_code') border-red-300 @enderror" placeholder="例如：29、59、89" required>
                                            @error('series_code')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">成本价格</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">¥</span>
                                                </div>
                                                <input type="number" step="0.01" name="cost" x-model="newSeries.cost" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('cost') border-red-300 @enderror" placeholder="0.00" required>
                            </div>
                                    @error('cost')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                        
                                        <!-- 预览计算 -->
                                        <div x-show="newSeries.code && newSeries.cost" class="p-4 bg-gray-50 rounded-lg">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">预览信息</h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">售价：</span>
                                                    <span class="font-medium">¥<span x-text="newSeries.code"></span></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">成本：</span>
                                                    <span class="font-medium">¥<span x-text="newSeries.cost"></span></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">利润：</span>
                                                    <span class="font-medium text-green-600">¥<span x-text="(newSeries.code - newSeries.cost).toFixed(2)"></span></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">利润率：</span>
                                                    <span class="font-medium text-blue-600"><span x-text="(((newSeries.code - newSeries.cost) / newSeries.code) * 100).toFixed(1)"></span>%</span>
                                                </div>
                                            </div>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                确认添加
                            </button>
                            <button type="button" @click="showAddModal = false; resetForm()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                取消
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 编辑系列模态框 -->
        <div x-show="editingItem" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="editingItem = null"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form x-bind:action="`{{ route('price-series.update', '') }}/${editingItem.code}`" method="POST">
                                                @csrf
                                                @method('PUT')
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="bi bi-pencil text-blue-600"></i>
                                                    </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">编辑价格系列</h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">系列编号</label>
                                            <input type="text" x-bind:value="editingItem?.code" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" disabled>
                                                </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">成本价格</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">¥</span>
                                                </div>
                                                <input type="number" step="0.01" name="cost" x-model="editingItem.cost" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                保存修改
                            </button>
                            <button type="button" @click="editingItem = null" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                取消
                            </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 处理表单验证错误时自动打开模态框
    @if($errors->any())
        // 如果有错误，重新打开模态框
        const addModal = document.querySelector('[x-data]').__x.$data;
        addModal.showAddModal = true;
        addModal.newSeries.code = '{{ old('series_code') }}';
        addModal.newSeries.cost = '{{ old('cost') }}';
    @endif
});
</script>
@endpush
@endsection 