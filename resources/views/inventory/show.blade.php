@extends('layouts.app')

@section('title', '库存详情')
@section('header', '库存详情')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- 页面头部 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-box text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">{{ $inventory->product->name ?? '未知商品' }}</h3>
                        <p class="text-sm text-gray-500">库存详情</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('inventory.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="bi bi-arrow-left mr-2"></i>返回列表
                    </a>
                    <a href="{{ route('inventory.edit', $inventory) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="bi bi-pencil mr-2"></i>编辑
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 主要内容 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 基本信息 -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-900">基本信息</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <span class="text-sm font-medium text-gray-700">商品名称</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $inventory->product->name ?? '未知商品' }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <span class="text-sm font-medium text-gray-700">商品编码</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $inventory->product->code ?? '未知编码' }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <span class="text-sm font-medium text-gray-700">商品类型</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $inventory->product->type ?? '未知类型' }}</span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                                <span class="text-sm font-medium text-blue-700">当前库存</span>
                                <span class="text-lg font-bold text-blue-900">{{ number_format($inventory->quantity) }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-xl">
                                <span class="text-sm font-medium text-yellow-700">最低库存</span>
                                <span class="text-lg font-bold text-yellow-900">{{ number_format($inventory->min_quantity) }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                                <span class="text-sm font-medium text-green-700">最高库存</span>
                                <span class="text-lg font-bold text-green-900">{{ number_format($inventory->max_quantity) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 库存状态 -->
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">库存状态</span>
                            <span class="text-sm font-medium text-gray-500">
                                @if($inventory->quantity < $inventory->min_quantity)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="bi bi-exclamation-triangle mr-1"></i>库存不足
                                    </span>
                                @elseif($inventory->quantity > $inventory->max_quantity)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="bi bi-exclamation-circle mr-1"></i>库存过高
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="bi bi-check-circle mr-1"></i>库存正常
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            @php
                                $percentage = $inventory->max_quantity > 0 ? ($inventory->quantity / $inventory->max_quantity) * 100 : 0;
                                $color = $inventory->quantity < $inventory->min_quantity ? 'bg-red-500' : 
                                       ($inventory->quantity > $inventory->max_quantity ? 'bg-yellow-500' : 'bg-green-500');
                            @endphp
                            <div class="{{ $color }} h-3 rounded-full transition-all duration-300" style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>0</span>
                            <span>{{ number_format($inventory->max_quantity) }}</span>
                        </div>
                    </div>

                    <!-- 其他信息 -->
                    <div class="mt-6 space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700">最后盘点日期</span>
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $inventory->last_check_date ? date('Y-m-d H:i', strtotime($inventory->last_check_date)) : '未盘点' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700">库存总值</span>
                            <span class="text-sm font-semibold text-gray-900">¥{{ number_format($inventory->total_value, 2) }}</span>
                        </div>
                        @if($inventory->remark)
                        <div class="flex items-start justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700">备注</span>
                            <span class="text-sm text-gray-900 text-right">{{ $inventory->remark }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 侧边栏 -->
        <div class="space-y-6">
            <!-- 快速操作 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-900">快速操作</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <button onclick="openQuickAdjust()" 
                                class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <i class="bi bi-plus-circle mr-2"></i>快速调整
                        </button>
                        <button onclick="openInventoryCheck()" 
                                class="w-full inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <i class="bi bi-clipboard-check mr-2"></i>库存盘点
                        </button>
                        <a href="{{ route('inventory.edit', $inventory) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <i class="bi bi-gear mr-2"></i>设置库存
                        </a>
                    </div>
                </div>
            </div>

            <!-- 统计信息 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-900">统计信息</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">盘点次数</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $inventory->checkRecords()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">最后盘点</span>
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $inventory->last_check_date ? $inventory->last_check_date->diffForHumans() : '从未盘点' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">库存状态</span>
                            <span class="text-sm font-semibold">
                                @if($inventory->quantity < $inventory->min_quantity)
                                    <span class="text-red-600">库存不足</span>
                                @elseif($inventory->quantity > $inventory->max_quantity)
                                    <span class="text-yellow-600">库存过高</span>
                                @else
                                    <span class="text-green-600">库存正常</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 最近盘点记录 -->
    <div class="mt-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">最近盘点记录</h3>
                    @if($inventory->checkRecords()->count() > 5)
                    <a href="{{ route('inventory.check-history') }}?inventory_id={{ $inventory->id }}" 
                       class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        查看全部 <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">盘点日期</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">系统库存</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">实际库存</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">差异</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">盘点人</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($inventory->checkRecords()->with('inventoryCheckRecord.user')->latest()->take(5)->get() as $record)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $record->inventoryCheckRecord->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($record->system_quantity) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($record->actual_quantity) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $record->difference > 0 ? 'bg-green-100 text-green-800' : 
                                       ($record->difference < 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $record->difference > 0 ? '+' : '' }}{{ $record->difference }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $record->inventoryCheckRecord->user->real_name ?? '未知用户' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="bi bi-clipboard text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">暂无盘点记录</h3>
                                    <p class="text-gray-500">还没有进行过库存盘点</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 快速调整弹窗 -->
<div id="quickAdjustModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-2xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">快速调整库存</h3>
            <form id="quickAdjustForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">调整数量</label>
                    <input type="number" id="adjustQuantity" name="adjust_quantity" 
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           placeholder="输入调整数量（正数为增加，负数为减少）">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">调整原因</label>
                    <textarea id="adjustReason" name="reason" rows="3"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                              placeholder="请输入调整原因"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeQuickAdjust()" 
                            class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        取消
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        确认调整
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openQuickAdjust() {
    document.getElementById('quickAdjustModal').classList.remove('hidden');
}

function closeQuickAdjust() {
    document.getElementById('quickAdjustModal').classList.add('hidden');
}

document.getElementById('quickAdjustForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const quantity = document.getElementById('adjustQuantity').value;
    const reason = document.getElementById('adjustReason').value;
    
    if (!quantity) {
        alert('请输入调整数量');
        return;
    }
    
    // 发送AJAX请求
    fetch(`/inventory/{{ $inventory->id }}/quick-adjust`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            adjust_quantity: quantity,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('库存调整成功！');
            location.reload();
        } else {
            alert('库存调整失败：' + data.message);
        }
    })
    .catch(error => {
        alert('请求失败：' + error.message);
    });
    
    closeQuickAdjust();
});

// 点击模态框外部关闭
document.getElementById('quickAdjustModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQuickAdjust();
    }
});
</script>
@endsection 