@extends('layouts.app')

@section('title', '新增入库')
@section('header', '新增入库')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" x-data="{
        formData: {
            store_id: '{{ old('store_id', '') }}',
            supplier: '{{ old('supplier', '') }}',
            remark: '{{ old('remark', '') }}',
            priceSeries: [
                @foreach($priceSeries as $series)
                {
                    code: '{{ $series->code }}',
                    name: '{{ $series->code }}元系列',
                    cost: {{ $series->cost ?? $series->price * 0.7 }},
                    quantity: {{ old('price_series.'.$loop->index.'.quantity', 0) }}
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]
        },
        currentStep: 1,
        get totalQuantity() {
            return this.formData.priceSeries.reduce((total, series) => total + parseInt(series.quantity || 0), 0);
        },
        get totalCost() {
            return this.formData.priceSeries.reduce((total, series) => total + (parseInt(series.quantity || 0) * parseFloat(series.cost)), 0);
        },
        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        submitForm() {
            document.getElementById('stockInForm').submit();
        }
    }" x-init="console.log('Alpine.js initialized', formData)">
        
        <!-- 顶部面包屑和仓库选择 -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-center space-x-4">
                <div class="flex items-center text-sm text-gray-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">控制台</a>
                    <i class="bi bi-chevron-right mx-2"></i>
                    <a href="{{ route('stock-ins.index') }}" class="hover:text-blue-600 transition-colors">入库管理</a>
                    <i class="bi bi-chevron-right mx-2"></i>
                    <span class="text-gray-900 font-medium">新增入库</span>
                </div>
            </div>
            
            <a href="{{ route('stock-ins.index') }}" class="inline-flex items-center px-6 py-3 bg-white/80 backdrop-blur-lg border border-white/20 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 text-gray-700 font-medium">
                <i class="bi bi-arrow-left mr-2"></i>
                返回列表
            </a>
        </div>

        <!-- 今日入库汇总 -->
        <div class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-graph-up-arrow text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-bold text-gray-900">新增入库记录</h2>
                        <p class="text-gray-600">{{ now()->format('Y年m月d日') }} • 录入商品入库信息</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 错误提示 -->
        @if($errors->any())
            <div class="bg-red-500/10 backdrop-blur-lg border border-red-500/20 rounded-2xl p-6 shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-exclamation-triangle text-white"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-red-800">请修正以下错误：</h3>
                </div>
                <ul class="space-y-2">
                    @foreach($errors->all() as $error)
                        <li class="flex items-start">
                            <i class="bi bi-dot text-red-500 text-xl mr-2 mt-0.5"></i>
                            <span class="text-red-700">{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 增强的步骤指示器 -->
        <div class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between relative">
                <!-- 背景连接线 -->
                <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 rounded-full"></div>
                <div class="absolute top-5 left-0 h-1 bg-gradient-to-r from-blue-500 via-green-500 to-purple-500 rounded-full transition-all duration-500" 
                     :style="`width: ${((currentStep - 1) / 2) * 100}%`"></div>
                
                <!-- 步骤1 -->
                <div class="relative flex flex-col items-center z-10">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-lg" 
                         :class="currentStep >= 1 ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white scale-110' : 'bg-white text-gray-400 border-2 border-gray-200'">
                        <i class="bi bi-building text-xl"></i>
                    </div>
                    <div class="mt-4 text-center">
                        <span class="block text-sm font-semibold transition-colors duration-200" :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-400'">仓库选择</span>
                        <p class="text-xs text-gray-500 mt-1">选择入库仓库</p>
                    </div>
                </div>

                <!-- 步骤2 -->
                <div class="relative flex flex-col items-center z-10">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-lg" 
                         :class="currentStep >= 2 ? 'bg-gradient-to-r from-green-500 to-green-600 text-white scale-110' : 'bg-white text-gray-400 border-2 border-gray-200'">
                        <i class="bi bi-box-seam text-xl"></i>
                    </div>
                    <div class="mt-4 text-center">
                        <span class="block text-sm font-semibold transition-colors duration-200" :class="currentStep >= 2 ? 'text-green-600' : 'text-gray-400'">商品数量</span>
                        <p class="text-xs text-gray-500 mt-1">录入各系列数量</p>
                    </div>
                </div>

                <!-- 步骤3 -->
                <div class="relative flex flex-col items-center z-10">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-lg" 
                         :class="currentStep >= 3 ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white scale-110' : 'bg-white text-gray-400 border-2 border-gray-200'">
                        <i class="bi bi-check-circle text-xl"></i>
                    </div>
                    <div class="mt-4 text-center">
                        <span class="block text-sm font-semibold transition-colors duration-200" :class="currentStep >= 3 ? 'text-purple-600' : 'text-gray-400'">确认入库</span>
                        <p class="text-xs text-gray-500 mt-1">核对并提交</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 表单内容 -->
        <form id="stockInForm" action="{{ route('stock-ins.store') }}" method="POST">
                        @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- 主要内容区域 -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- 步骤1：仓库选择 -->
                    <div x-show="currentStep === 1" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform translate-y-4" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-8 py-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                                    <i class="bi bi-building text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">选择入库仓库</h3>
                                    <p class="text-blue-100 text-sm mt-1">选择商品要入库的目标仓库</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-lg font-semibold text-gray-900 mb-4">选择仓库</label>
                                    @if($stores->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($stores as $store)
                                                <label class="relative group">
                                                    <input type="radio" 
                                                           name="store_id" 
                                                           value="{{ $store->id }}" 
                                                           x-model="formData.store_id"
                                                           class="peer sr-only" 
                                                           {{ old('store_id') == $store->id ? 'checked' : '' }}>
                                                    <div class="flex items-center p-6 rounded-2xl border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all cursor-pointer hover:border-blue-300 hover:shadow-md group-hover:scale-[1.02]">
                                                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                                                            <i class="bi bi-shop text-white text-xl"></i>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="font-bold text-gray-900 text-lg">{{ $store->name }}</div>
                                                            <div class="text-sm text-gray-500">仓库编码: {{ $store->code ?? 'N/A' }}</div>
                                                            <div class="text-xs text-blue-600 mt-1">活跃仓库</div>
                                                        </div>
                                                        <div class="peer-checked:block hidden">
                                                            <i class="bi bi-check-circle-fill text-blue-500 text-2xl"></i>
                                                        </div>
                                                    </div>
                                                </label>
                                @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-8">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <i class="bi bi-building text-gray-400 text-2xl"></i>
                                            </div>
                                            <p class="text-gray-500"><x-lang key="messages.stores.no_available_stores"/></p>
                                            <p class="text-sm text-gray-400 mt-2">请联系管理员分配仓库权限</p>
                                        </div>
                                    @endif
                            @error('store_id')
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="bi bi-exclamation-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                            @enderror
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="button" 
                                        @click="nextStep()" 
                                        :disabled="!formData.store_id"
                                        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-2xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/20 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:scale-105">
                                    下一步：录入商品数量
                                    <i class="bi bi-arrow-right ml-3 text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 步骤2：商品数量 -->
                    <div x-show="currentStep === 2" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform translate-y-4" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 px-8 py-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                                    <i class="bi bi-box-seam text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">商品数量录入</h3>
                                    <p class="text-green-100 text-sm mt-1">输入各价格系列的入库数量</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8">
                            @if($priceSeries->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <template x-for="(series, index) in formData.priceSeries" :key="series.code">
                                        <div class="group relative bg-gradient-to-br from-blue-50 via-blue-50 to-indigo-100 rounded-2xl p-6 border border-blue-200/50 hover:shadow-lg transition-all duration-300 hover:scale-[1.02]" 
                                             :class="{
                                                 'from-blue-50 to-blue-100 border-blue-200': series.code === '29',
                                                 'from-green-50 to-green-100 border-green-200': series.code === '59',
                                                 'from-purple-50 to-purple-100 border-purple-200': series.code === '89',
                                                 'from-orange-50 to-orange-100 border-orange-200': series.code === '159'
                                             }">
                                            <div class="flex items-center mb-4">
                                                <div class="w-12 h-12 bg-gradient-to-r rounded-2xl flex items-center justify-center shadow-lg"
                                                     :class="{
                                                         'from-blue-500 to-blue-600': series.code === '29',
                                                         'from-green-500 to-green-600': series.code === '59',
                                                         'from-purple-500 to-purple-600': series.code === '89',
                                                         'from-orange-500 to-red-500': series.code === '159'
                                                     }">
                                                    <span class="text-white text-lg font-bold" x-text="series.code"></span>
                                                </div>
                                                <div class="ml-4">
                                                    <label class="block text-lg font-bold text-gray-800" x-text="series.name"></label>
                                                    <p class="text-sm font-medium" 
                                                       :class="{
                                                           'text-blue-600': series.code === '29',
                                                           'text-green-600': series.code === '59',
                                                           'text-purple-600': series.code === '89',
                                                           'text-orange-600': series.code === '159'
                                                       }"
                                                       x-text="'成本单价: ¥' + series.cost.toFixed(2)"></p>
                                                </div>
                                            </div>
                                            <div class="relative">
                                                <input type="number" 
                                                       :name="`price_series[${index}][quantity]`" 
                                                       x-model="series.quantity" 
                                                       class="w-full pl-6 pr-16 py-4 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:border-gray-500 transition-all duration-200 text-lg font-medium bg-white/80 backdrop-blur-sm"
                                                       :class="{
                                                           'focus:ring-blue-500/20 focus:border-blue-500': series.code === '29',
                                                           'focus:ring-green-500/20 focus:border-green-500': series.code === '59',
                                                           'focus:ring-purple-500/20 focus:border-purple-500': series.code === '89',
                                                           'focus:ring-orange-500/20 focus:border-orange-500': series.code === '159'
                                                       }"
                                                       placeholder="0" 
                                                       min="0">
                                                <input type="hidden" :name="`price_series[${index}][code]`" :value="series.code">
                                                <input type="hidden" :name="`price_series[${index}][unit_price]`" :value="series.cost">
                                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 text-sm font-medium bg-gray-100 px-2 py-1 rounded-lg">件</span>
                                                </div>
                                            </div>
                                            <div class="mt-3 flex justify-between items-center">
                                                <span class="text-sm text-gray-600">数量小计</span>
                                                <span class="text-lg font-bold px-3 py-1 rounded-lg"
                                                      :class="{
                                                          'text-blue-600 bg-blue-100': series.code === '29',
                                                          'text-green-600 bg-green-100': series.code === '59',
                                                          'text-purple-600 bg-purple-100': series.code === '89',
                                                          'text-orange-600 bg-orange-100': series.code === '159'
                                                      }"
                                                      x-text="'¥' + ((parseInt(series.quantity) || 0) * series.cost).toFixed(2)"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="bi bi-box-seam text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500">暂无价格系列</p>
                                    <p class="text-sm text-gray-400 mt-2">请先配置价格系列</p>
                                </div>
                            @endif

                            <div class="mt-8 flex justify-between">
                                <button type="button" 
                                        @click="prevStep()" 
                                        class="inline-flex items-center px-8 py-4 bg-gray-100/80 hover:bg-gray-200/80 text-gray-700 font-semibold rounded-2xl transition-all duration-200 backdrop-blur-sm">
                                    <i class="bi bi-arrow-left mr-3 text-lg"></i>
                                    上一步：选择仓库
                                </button>
                                <button type="button" 
                                        @click="nextStep()" 
                                        :disabled="totalQuantity === 0"
                                        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-2xl hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-4 focus:ring-green-500/20 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:scale-105">
                                    下一步：确认入库
                                    <i class="bi bi-arrow-right ml-3 text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 步骤3：确认入库 -->
                    <div x-show="currentStep === 3" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform translate-y-4" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-8 py-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                                    <i class="bi bi-check-circle text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">确认入库信息</h3>
                                    <p class="text-purple-100 text-sm mt-1">请仔细核对所有信息，确认无误后提交入库</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="space-y-8">
                                <!-- 数量汇总展示 -->
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200/50">
                                    <h4 class="font-bold text-gray-900 mb-6 flex items-center text-lg">
                                        <i class="bi bi-bar-chart text-purple-600 mr-3 text-xl"></i>
                                        商品数量汇总
                                    </h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <template x-for="series in formData.priceSeries.filter(s => parseInt(s.quantity) > 0)">
                                            <div class="text-center p-6 rounded-xl border"
                                                 :class="{
                                                     'bg-blue-100 border-blue-200': series.code === '29',
                                                     'bg-green-100 border-green-200': series.code === '59',
                                                     'bg-purple-100 border-purple-200': series.code === '89',
                                                     'bg-orange-100 border-orange-200': series.code === '159'
                                                 }">
                                                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3"
                                                     :class="{
                                                         'bg-blue-500': series.code === '29',
                                                         'bg-green-500': series.code === '59',
                                                         'bg-purple-500': series.code === '89',
                                                         'bg-orange-500': series.code === '159'
                                                     }">
                                                    <span class="text-white font-bold" x-text="series.code"></span>
                                                </div>
                                                <div class="text-3xl font-bold mb-1"
                                                     :class="{
                                                         'text-blue-600': series.code === '29',
                                                         'text-green-600': series.code === '59',
                                                         'text-purple-600': series.code === '89',
                                                         'text-orange-600': series.code === '159'
                                                     }"
                                                     x-text="series.quantity"></div>
                                                <div class="text-sm text-gray-600" x-text="series.name"></div>
                                                <div class="text-xs font-medium mt-1"
                                                     :class="{
                                                         'text-blue-600': series.code === '29',
                                                         'text-green-600': series.code === '59',
                                                         'text-purple-600': series.code === '89',
                                                         'text-orange-600': series.code === '159'
                                                     }"
                                                     x-text="'¥' + ((parseInt(series.quantity) || 0) * series.cost).toFixed(2)"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- 备注信息 -->
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-200/50">
                                    <h4 class="font-bold text-gray-900 mb-6 flex items-center text-lg">
                                        <i class="bi bi-chat-text text-purple-600 mr-3 text-xl"></i>
                                        补充信息
                                    </h4>
                                    <div class="space-y-6">
                                        <!-- 供应商字段 -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-3">供应商信息（可选）</label>
                                            <input type="text" 
                                                   name="supplier" 
                                                   x-model="formData.supplier" 
                                                   class="w-full px-6 py-4 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-200 bg-white/80 backdrop-blur-sm placeholder-gray-400" 
                                                   placeholder="请输入供应商名称（可选）...">
                                            @error('supplier')
                                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                                    <i class="bi bi-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                            @enderror
                        </div>

                                        <!-- 备注字段 -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-3">备注信息（可选）</label>
                                            <textarea name="remark" 
                                                      x-model="formData.remark" 
                                                      rows="4" 
                                                      class="w-full px-6 py-4 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-200 bg-white/80 backdrop-blur-sm placeholder-gray-400 resize-none" 
                                                      placeholder="请输入备注信息（可选）..."></textarea>
                                            @error('remark')
                                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                                    <i class="bi bi-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-between">
                                <button type="button" 
                                        @click="prevStep()" 
                                        class="inline-flex items-center px-8 py-4 bg-gray-100/80 hover:bg-gray-200/80 text-gray-700 font-semibold rounded-2xl transition-all duration-200 backdrop-blur-sm">
                                    <i class="bi bi-arrow-left mr-3 text-lg"></i>
                                    上一步：修改数量
                                </button>
                                <button type="submit" 
                                        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-2xl hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-4 focus:ring-purple-500/20 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="bi bi-check-circle mr-3 text-lg"></i>
                                    确认提交入库
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 增强的侧边栏信息 -->
                <div class="space-y-8">
                    <!-- 实时统计 -->
                    <div class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg p-6 sticky top-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-3">
                                <i class="bi bi-graph-up text-white"></i>
                            </div>
                            入库统计
                        </h3>
                        <div class="space-y-4">
                            <div class="group bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200/50 hover:shadow-lg transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center mr-4">
                                            <i class="bi bi-box text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-medium text-gray-600">总数量</span>
                                            <span class="block text-xs text-blue-600">本次入库商品总计</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl font-bold text-blue-600" x-text="totalQuantity"></span>
                                        <span class="text-sm text-blue-600 ml-1">件</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="group bg-gradient-to-r from-green-50 to-green-100 rounded-2xl p-6 border border-green-200/50 hover:shadow-lg transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center mr-4">
                                            <i class="bi bi-currency-dollar text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-medium text-gray-600">总成本</span>
                                            <span class="block text-xs text-green-600">预估采购成本</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl font-bold text-green-600" x-text="'¥' + totalCost.toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="group bg-gradient-to-r from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200/50 hover:shadow-lg transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-purple-500 rounded-2xl flex items-center justify-center mr-4">
                                            <i class="bi bi-clock text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-medium text-gray-600">入库时间</span>
                                            <span class="block text-xs text-purple-600">系统记录时间</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-semibold text-purple-600">{{ now()->format('m-d H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="group bg-gradient-to-r from-orange-50 to-orange-100 rounded-2xl p-6 border border-orange-200/50 hover:shadow-lg transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-orange-500 rounded-2xl flex items-center justify-center mr-4">
                                            <i class="bi bi-person text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-medium text-gray-600">操作员</span>
                                            <span class="block text-xs text-orange-600">当前登录用户</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-semibold text-orange-600">{{ auth()->user()->real_name ?? auth()->user()->username }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

                    <!-- 操作提示 -->
                    <div class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center mr-3">
                                <i class="bi bi-lightbulb text-white"></i>
                            </div>
                            操作提示
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start p-4 bg-blue-50 rounded-xl border border-blue-200/50">
                                <i class="bi bi-check-circle text-blue-500 mr-3 text-lg mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">数量核对</p>
                                    <p class="text-xs text-gray-600 mt-1">请确保输入数量与实际到货数量一致</p>
                                </div>
                            </div>
                            <div class="flex items-start p-4 bg-green-50 rounded-xl border border-green-200/50">
                                <i class="bi bi-building text-green-500 mr-3 text-lg mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">仓库选择</p>
                                    <p class="text-xs text-gray-600 mt-1">选择正确的目标仓库进行入库</p>
                                </div>
                            </div>
                            <div class="flex items-start p-4 bg-orange-50 rounded-xl border border-orange-200/50">
                                <i class="bi bi-arrow-repeat text-orange-500 mr-3 text-lg mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">自动更新</p>
                                    <p class="text-xs text-gray-600 mt-1">入库后将自动更新库存统计</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 