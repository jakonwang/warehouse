@extends('layouts.app')

@section('title', '商品详情')
@section('header', '商品详情')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- 页面头部 -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            <p class="text-gray-600 mt-1">商品编码：{{ $product->code }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-sm">
                <i class="bi bi-pencil mr-2"></i>
                编辑商品
            </a>
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                <i class="bi bi-arrow-left mr-2"></i>
                返回列表
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 左侧：商品图片和基本信息 -->
        <div class="lg:col-span-1 space-y-6">
            <!-- 商品图片 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="aspect-square">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                            <div class="text-center">
                                <i class="bi bi-image text-gray-400 text-6xl"></i>
                                <p class="text-gray-500 text-sm mt-2">暂无图片</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold 
                            {{ $product->type === 'standard' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            <i class="bi bi-{{ $product->type === 'standard' ? 'box' : 'gift' }} mr-1"></i>
                            {{ $product->type_name }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            <i class="bi bi-{{ $product->is_active ? 'check-circle' : 'x-circle' }} mr-1"></i>
                            {{ $product->is_active ? '上架中' : '已下架' }}
                        </span>
                    </div>
                    @if($product->description)
                        <p class="text-gray-600 text-sm mt-3">{{ $product->description }}</p>
                    @endif
                </div>
            </div>

            <!-- 快速统计 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-graph-up text-blue-600 mr-2"></i>
                    销售概览
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $product->saleDetails->sum('quantity') }}</div>
                        <div class="text-xs text-gray-500 mt-1">总销售数量</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">¥{{ number_format($product->saleDetails->sum('total'), 0) }}</div>
                        <div class="text-xs text-gray-500 mt-1">总销售额</div>
                    </div>
                    @if(auth()->user()->canViewProfitAndCost())
                    <div class="text-center">
                        <div class="text-2xl font-bold text-{{ $product->saleDetails->sum('profit') >= 0 ? 'emerald' : 'red' }}-600">¥{{ number_format($product->saleDetails->sum('profit'), 0) }}</div>
                        <div class="text-xs text-gray-500 mt-1">总利润</div>
                    </div>
                    <div class="text-center">
                        @php
                            $totalSales = $product->saleDetails->sum('total');
                            $totalProfit = $product->saleDetails->sum('profit');
                            $avgProfitRate = $totalSales > 0 ? ($totalProfit / $totalSales * 100) : 0;
                        @endphp
                        <div class="text-2xl font-bold text-{{ $avgProfitRate >= 0 ? 'emerald' : 'red' }}-600">{{ number_format($avgProfitRate, 1) }}%</div>
                        <div class="text-xs text-gray-500 mt-1">平均利润率</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 右侧：详细信息 -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 价格信息 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-currency-dollar text-green-600 mr-2"></i>
                    价格信息
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                        <div class="text-sm text-green-600 font-medium mb-1">售价</div>
                        <div class="text-2xl font-bold text-green-700">¥{{ number_format($product->price, 2) }}</div>
                    </div>
                    
                    @if($product->isStandard() && auth()->user()->canViewProfitAndCost())
                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-lg p-4 border border-orange-200">
                        <div class="text-sm text-orange-600 font-medium mb-1">成本价</div>
                        <div class="text-2xl font-bold text-orange-700">¥{{ number_format($product->cost_price, 2) }}</div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                        <div class="text-sm text-blue-600 font-medium mb-1">利润率</div>
                        @php
                            $profitRate = $product->price > 0 ? 
                                (($product->price - $product->cost_price) / $product->price * 100) : 0;
                        @endphp
                        <div class="text-2xl font-bold text-{{ $profitRate >= 0 ? 'blue' : 'red' }}-700">
                            {{ number_format($profitRate, 2) }}%
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 库存信息 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-boxes text-purple-600 mr-2"></i>
                    库存信息
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-lg p-4 border border-purple-200">
                        <div class="text-sm text-purple-600 font-medium mb-1">当前库存</div>
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-purple-700 mr-2">{{ $product->stock }}</div>
                            @if($product->isLowStock())
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="bi bi-exclamation-triangle mr-1"></i>
                                    库存不足
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-lg p-4 border border-gray-200">
                        <div class="text-sm text-gray-600 font-medium mb-1">库存警戒值</div>
                        <div class="text-2xl font-bold text-gray-700">{{ $product->alert_stock }}</div>
                    </div>
                    
                    @if($product->isStandard())
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-lg p-4 border border-emerald-200">
                        <div class="text-sm text-emerald-600 font-medium mb-1">库存金额</div>
                        <div class="text-2xl font-bold text-emerald-700">¥{{ number_format($product->stock * $product->cost_price, 2) }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 销售记录 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="bi bi-receipt text-indigo-600 mr-2"></i>
                        销售记录
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $product->saleDetails->count() }} 条
                        </span>
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">销售时间</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">单价</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">总金额</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">利润</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($product->saleDetails as $detail)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $detail->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $detail->quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ¥{{ number_format($detail->price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    ¥{{ number_format($detail->total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($detail->profit !== null)
                                        <span class="text-{{ $detail->profit >= 0 ? 'green' : 'red' }}-600">
                                            ¥{{ number_format($detail->profit, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center">
                                    <div class="text-center">
                                        <i class="bi bi-inbox text-gray-400 text-4xl"></i>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">暂无销售记录</h3>
                                        <p class="mt-1 text-sm text-gray-500">当前商品还没有任何销售记录</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 其他信息 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-info-circle text-gray-600 mr-2"></i>
                    其他信息
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-500">排序权重</span>
                            <span class="text-sm text-gray-900">{{ $product->sort_order ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-500">创建时间</span>
                            <span class="text-sm text-gray-900">{{ $product->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-500">最后更新</span>
                            <span class="text-sm text-gray-900">{{ $product->updated_at->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-500">商品ID</span>
                            <span class="text-sm text-gray-900">#{{ $product->id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 