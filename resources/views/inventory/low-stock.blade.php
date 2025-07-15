@extends('layouts.app')

@section('title', '库存预警')
@section('header', '库存预警')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- 统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-600 text-sm font-medium">低库存商品</p>
                    <p class="text-2xl font-bold text-red-700">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                    <i class="bi bi-exclamation-triangle text-white text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-6 border border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-600 text-sm font-medium">需要补货</p>
                    <p class="text-2xl font-bold text-orange-700">{{ $stats['out_of_stock'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center">
                    <i class="bi bi-box-seam text-white text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl p-6 border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-600 text-sm font-medium">预警商品</p>
                    <p class="text-2xl font-bold text-yellow-700">{{ $stats['low_stock'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center">
                    <i class="bi bi-exclamation-circle text-white text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-600 text-sm font-medium">总价值</p>
                    <p class="text-2xl font-bold text-blue-700">¥{{ number_format($stats['total_value'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                    <i class="bi bi-currency-yen text-white text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选栏 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">搜索商品</label>
                <input type="text" id="search" placeholder="输入商品系列编码或名称..." 
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
            </div>
            <div class="flex items-end">
                <button onclick="exportLowStock()" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl font-medium hover:from-green-600 hover:to-emerald-600 transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="bi bi-download mr-2"></i>导出报告
                </button>
            </div>
        </div>
    </div>

    <!-- 主要内容 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">低库存商品列表</h3>
                <a href="{{ route('inventory.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    <i class="bi bi-arrow-left mr-1"></i>返回库存管理
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品信息</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">库存状态</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">库存数量</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">价值</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">最后盘点</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($inventory as $item)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mr-4">
                                    <i class="bi bi-box text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product->code ?? '未知编码' }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->product->name ?? '未知商品' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($item->quantity == 0)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="bi bi-x-circle mr-1"></i>缺货
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="bi bi-exclamation-triangle mr-1"></i>库存不足
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">{{ $item->quantity }}</span>
                                <span class="text-gray-500">/ {{ $item->max_quantity }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                @php
                                    $percentage = $item->max_quantity > 0 ? ($item->quantity / $item->max_quantity) * 100 : 0;
                                @endphp
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">¥{{ number_format($item->total_value, 2) }}</div>
                            <div class="text-xs text-gray-500">单价: ¥{{ number_format($item->product->cost_price ?? 0, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                                                            {{ $item->last_check_date ? date('Y-m-d H:i', strtotime($item->last_check_date)) : '未盘点' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('inventory.edit', $item) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <i class="bi bi-gear mr-1"></i>设置
                                </a>
                                <a href="{{ route('inventory.show', $item) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                    <i class="bi bi-eye mr-1"></i>查看
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="bi bi-check-circle text-green-600 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">暂无低库存商品</h3>
                                <p class="text-gray-500">所有商品的库存都在正常范围内</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inventory->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    显示第 {{ $inventory->firstItem() }} 到 {{ $inventory->lastItem() }} 条，共 {{ $inventory->total() }} 条记录
                </div>
                <div>
                    {{ $inventory->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
// 搜索功能
document.getElementById('search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// 导出功能
function exportLowStock() {
    // 这里可以添加导出逻辑
    alert('导出功能开发中...');
}
</script>
@endsection 