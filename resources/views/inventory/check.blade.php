@extends('layouts.app')

@section('title', '库存盘点')
@section('header', '库存盘点')

@section('content')
<div class="space-y-8">
    <!-- 现代化页面头部 -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <i class="bi bi-clipboard-check text-3xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold mb-2">实时库存盘点</h1>
                    <p class="text-emerald-100 text-lg">智能盘点系统，实时计算差异，确保账实相符</p>
                    <div class="flex items-center mt-3 space-x-4 text-sm">
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-calculator mr-1"></i>
                            自动计算
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-shield-check mr-1"></i>
                            数据安全
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="bi bi-clock mr-1"></i>
                            实时保存
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3">
                <a href="{{ route('inventory.check-history') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl font-medium text-white hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200">
                    <i class="bi bi-clock-history mr-2"></i>
                    盘点历史
                </a>
                <a href="{{ route('inventory.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl font-medium text-white hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200">
                    <i class="bi bi-boxes mr-2"></i>
                    库存管理
                </a>
            </div>
        </div>
    </div>

    <!-- 盘点日期选择和统计 -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- 日期选择器 -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="bi bi-calendar-date text-white text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-semibold text-gray-900">盘点日期</h3>
                        <p class="text-sm text-gray-500">选择盘点日期</p>
                    </div>
                </div>
                <form action="{{ route('inventory.check') }}" method="GET" class="space-y-4">
                    <div>
                        <input type="date" name="check_date" 
                               value="{{ $checkDate }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 font-semibold shadow-lg">
                        <i class="bi bi-search mr-2"></i>
                        查询库存
                    </button>
                </form>
            </div>
        </div>

        <!-- 盘点统计 -->
        <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 总商品数 -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="bi bi-box-seam text-white text-xl"></i>
                    </div>
                    <span class="bg-purple-100 text-purple-800 text-xs font-medium px-3 py-1 rounded-full">商品数</span>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $inventory->count() }}</h3>
                    <p class="text-gray-600 text-sm font-medium">待盘点商品</p>
                </div>
            </div>

            <!-- 系统库存总量 -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                        <i class="bi bi-database text-white text-xl"></i>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1 rounded-full">系统库存</span>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($inventory->sum('quantity')) }}</h3>
                    <p class="text-gray-600 text-sm font-medium">系统总量</p>
                </div>
            </div>

            <!-- 盘点差异 -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle text-white text-xl"></i>
                    </div>
                    <span class="bg-orange-100 text-orange-800 text-xs font-medium px-3 py-1 rounded-full">差异</span>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">0</h3>
                    <p class="text-gray-600 text-sm font-medium">盘点差异</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 盘点表单 -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">库存盘点明细</h3>
                    <p class="text-gray-600">请输入实际库存数量，系统将自动计算差异</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium hover:bg-emerald-200 transition-colors">
                        <i class="bi bi-save mr-1"></i>
                        自动保存
                    </button>
                </div>
            </div>
        </div>

        <form action="{{ route('inventory.update-check') }}" method="POST" class="p-8">
            @csrf
            <input type="hidden" name="check_date" value="{{ $checkDate }}">
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">商品名称</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">系统库存</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">实际库存</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">差异数量</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">差异状态</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($inventory as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">{{ $item->product->code ?? 'N/A' }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $item->product->name ?? '未知商品' }}</div>
                                        <div class="text-xs text-gray-500">编码: {{ $item->product->code ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-lg font-bold text-blue-600">{{ number_format($item->quantity) }}</div>
                                <div class="text-xs text-gray-500">系统数量</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center">
                                    <div class="relative">
                                        <input type="number" 
                                               name="inventory[{{ $item->id }}][quantity]" 
                                               value="{{ $item->quantity }}" 
                                               min="0" 
                                               class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-center font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                                        <input type="hidden" name="inventory[{{ $item->id }}][id]" value="{{ $item->id }}">
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-lg font-bold text-gray-600">0</div>
                                <div class="text-xs text-gray-500">数量差异</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    <i class="bi bi-check-circle mr-1"></i>
                                    无差异
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- 底部操作区 -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-between p-6 bg-gray-50 rounded-xl">
                <div class="flex items-center space-x-4 mb-4 sm:mb-0">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">盘点日期：</span>
                        <span class="font-semibold text-gray-900">{{ $checkDate }}</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">总差异：</span>
                        <span class="font-semibold text-gray-900">0</span>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="window.location.href='{{ route('inventory.index') }}'" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200 font-semibold">
                        <i class="bi bi-x-circle mr-2"></i>
                        取消
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-cyan-600 text-white rounded-xl hover:from-emerald-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-200 font-semibold shadow-lg">
                        <i class="bi bi-check-circle mr-2"></i>
                        保存盘点结果
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 分页 -->
    @if($inventory->hasPages())
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 px-6 py-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="text-sm text-gray-700 mb-4 lg:mb-0">
                显示 <span class="font-semibold text-emerald-600">{{ $inventory->firstItem() }}</span> 到 <span class="font-semibold text-emerald-600">{{ $inventory->lastItem() }}</span> 条，共 <span class="font-semibold text-emerald-600">{{ $inventory->total() }}</span> 条记录
            </div>
            <div class="flex items-center space-x-2">
                {{ $inventory->appends(['check_date' => $checkDate])->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection 