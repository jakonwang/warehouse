@extends('layouts.app')

@section('title', '仓库调拨管理')
@section('header', '仓库调拨管理')

@section('content')
<div class="space-y-6">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">仓库调拨管理</h1>
                <p class="mt-1 text-sm text-gray-600">管理仓库间的商品调拨申请和审批</p>
            </div>
            <div class="mt-4 lg:mt-0 flex items-center space-x-3">
                <a href="{{ route('store-transfers.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="bi bi-plus-circle mr-2"></i>
                    新建调拨
                </a>
            </div>
        </div>
    </div>

    <!-- 筛选表单 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('store-transfers.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">调拨状态</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">全部状态</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>待审批</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>已审批</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>已拒绝</option>
                    <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>调拨中</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>已完成</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>已取消</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">源仓库</label>
                <select name="source_store_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('messages.stores.all_stores') }}</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('source_store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">目标仓库</label>
                <select name="target_store_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('messages.stores.all_stores') }}</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('target_store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">申请时间</label>
                <input type="text" name="date_range" value="{{ request('date_range') }}" placeholder="选择日期范围" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" readonly>
            </div>
            
            <div class="md:col-span-2 lg:col-span-4 flex items-center space-x-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="bi bi-search mr-2"></i>筛选
                </button>
                <a href="{{ route('store-transfers.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i class="bi bi-arrow-clockwise mr-2"></i>重置
                </a>
            </div>
        </form>
    </div>

    <!-- 调拨列表 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">调拨记录</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">调拨单号</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">源仓库</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">目标仓库</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">申请人</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">申请时间</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transfers as $transfer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $transfer->transfer_no }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transfer->sourceStore->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transfer->targetStore->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transfer->product->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transfer->quantity }}个
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $transfer->status_color }}-100 text-{{ $transfer->status_color }}-800">
                                {{ $transfer->status_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transfer->requester->real_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transfer->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('store-transfers.show', $transfer) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @if($transfer->canBeApproved() && auth()->user()->isSuperAdmin())
                                <form method="POST" action="{{ route('store-transfers.approve', $transfer) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('确认审批通过？')">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('store-transfers.reject', $transfer) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('确认拒绝？')">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                @endif
                                
                                @if($transfer->canBeCompleted())
                                <form method="POST" action="{{ route('store-transfers.complete', $transfer) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-purple-600 hover:text-purple-900" onclick="return confirm('确认完成调拨？')">
                                        <i class="bi bi-check2-all"></i>
                                    </button>
                                </form>
                                @endif
                                
                                @if($transfer->canBeCancelled())
                                <form method="POST" action="{{ route('store-transfers.cancel', $transfer) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-orange-600 hover:text-orange-900" onclick="return confirm('确认取消？')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                @endif
                                
                                @if(in_array($transfer->status, ['rejected', 'cancelled']))
                                <form method="POST" action="{{ route('store-transfers.destroy', $transfer) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('确认删除？')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                            暂无调拨记录
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- 分页 -->
        @if($transfers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 初始化日期选择器
    flatpickr('input[name="date_range"]', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        placeholder: '选择日期范围'
    });
});
</script>
@endpush
@endsection 