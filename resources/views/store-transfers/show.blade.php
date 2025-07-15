@extends('layouts.app')

@section('title', '调拨详情')
@section('header', '调拨详情')

@section('content')
<div class="space-y-6">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">调拨详情</h1>
                <p class="mt-1 text-sm text-gray-600">调拨单号: {{ $storeTransfer->transfer_no }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('store-transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <i class="bi bi-arrow-left mr-2"></i>
                    返回列表
                </a>
            </div>
        </div>
    </div>

    <!-- 调拨信息 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 基本信息 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">基本信息</h3>
            
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-500">调拨单号</span>
                    <span class="text-sm text-gray-900">{{ $storeTransfer->transfer_no }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-500">调拨状态</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $storeTransfer->status_color }}-100 text-{{ $storeTransfer->status_color }}-800">
                        {{ $storeTransfer->status_text }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-500">申请时间</span>
                    <span class="text-sm text-gray-900">{{ $storeTransfer->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
                
                @if($storeTransfer->approved_at)
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-500">审批时间</span>
                    <span class="text-sm text-gray-900">{{ $storeTransfer->approved_at->format('Y-m-d H:i:s') }}</span>
                </div>
                @endif
                
                @if($storeTransfer->completed_at)
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-500">完成时间</span>
                    <span class="text-sm text-gray-900">{{ $storeTransfer->completed_at->format('Y-m-d H:i:s') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- 仓库信息 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">仓库信息</h3>
            
            <div class="space-y-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">源仓库</span>
                    <p class="text-sm text-gray-900 mt-1">{{ $storeTransfer->sourceStore->name }}</p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">目标仓库</span>
                    <p class="text-sm text-gray-900 mt-1">{{ $storeTransfer->targetStore->name }}</p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">调拨商品</span>
                    <p class="text-sm text-gray-900 mt-1">{{ $storeTransfer->product->name }}</p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">调拨数量</span>
                    <p class="text-sm text-gray-900 mt-1">{{ $storeTransfer->quantity }} 个</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 成本信息 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">成本信息</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <span class="text-sm font-medium text-gray-500">单位成本</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">¥{{ number_format($storeTransfer->unit_cost, 2) }}</p>
            </div>
            
            <div class="text-center">
                <span class="text-sm font-medium text-gray-500">调拨数量</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $storeTransfer->quantity }}</p>
            </div>
            
            <div class="text-center">
                <span class="text-sm font-medium text-gray-500">总成本</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">¥{{ number_format($storeTransfer->total_cost, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- 申请信息 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">申请信息</h3>
        
        <div class="space-y-4">
            <div>
                <span class="text-sm font-medium text-gray-500">申请人</span>
                <p class="text-sm text-gray-900 mt-1">{{ $storeTransfer->requester->real_name }}</p>
            </div>
            
            @if($storeTransfer->approver)
            <div>
                <span class="text-sm font-medium text-gray-500">审批人</span>
                <p class="text-sm text-gray-900 mt-1">{{ $storeTransfer->approver->real_name }}</p>
            </div>
            @endif
            
            <div>
                <span class="text-sm font-medium text-gray-500">调拨原因</span>
                <p class="text-sm text-gray-900 mt-1">{{ $storeTransfer->reason }}</p>
            </div>
            
            @if($storeTransfer->remark)
            <div>
                <span class="text-sm font-medium text-gray-500">备注</span>
                <p class="text-sm text-gray-900 mt-1">{{ $storeTransfer->remark }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- 操作按钮 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">操作</h3>
        
        <div class="flex items-center space-x-3">
            @if($storeTransfer->canBeApproved() && auth()->user()->isSuperAdmin())
            <form method="POST" action="{{ route('store-transfers.approve', $storeTransfer) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200" onclick="return confirm('确认审批通过？')">
                    <i class="bi bi-check-circle mr-2"></i>
                    审批通过
                </button>
            </form>
            
            <form method="POST" action="{{ route('store-transfers.reject', $storeTransfer) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200" onclick="return confirm('确认拒绝？')">
                    <i class="bi bi-x-circle mr-2"></i>
                    拒绝申请
                </button>
            </form>
            @endif
            
            @if($storeTransfer->canBeCompleted())
            <form method="POST" action="{{ route('store-transfers.complete', $storeTransfer) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200" onclick="return confirm('确认完成调拨？')">
                    <i class="bi bi-check2-all mr-2"></i>
                    完成调拨
                </button>
            </form>
            @endif
            
            @if($storeTransfer->canBeCancelled())
            <form method="POST" action="{{ route('store-transfers.cancel', $storeTransfer) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-lg font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200" onclick="return confirm('确认取消？')">
                    <i class="bi bi-x-lg mr-2"></i>
                    取消申请
                </button>
            </form>
            @endif
            
            @if(in_array($storeTransfer->status, ['rejected', 'cancelled']))
            <form method="POST" action="{{ route('store-transfers.destroy', $storeTransfer) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200" onclick="return confirm('确认删除？')">
                    <i class="bi bi-trash mr-2"></i>
                    删除记录
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection 