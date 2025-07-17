@extends('layouts.mobile')

@section('title', '销售详�?)

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- 顶部导航 -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('mobile.sales.index') }}" class="text-gray-600">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <h1 class="text-xl font-semibold text-gray-900">销售详�?/h1>
            <div class="w-6"></div>
        </div>
        <p class="text-gray-600">订单 #{{ $sale->id }}</p>
    </div>

    <!-- 基本信息 -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 基本信息</h2>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">销售时�?/span>
                <span class="text-sm font-medium">{{ $sale->created_at->format('Y-m-d H:i:s') }}</span>
            </div>
            
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">销售类�?/span>
                @if($sale->sale_type === 'blind_bag')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        🎁 盲袋销�?
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        🛍�?标品销�?
                    </span>
                @endif
            </div>
            
            @if($sale->store->name)
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">仓库</span>
                <span class="text-sm font-medium">{{ $sale->store->name }}</span>
            </div>
            @endif
            
            @if($sale->customer_name)
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">客户姓名</span>
                <span class="text-sm font-medium">{{ $sale->customer_name }}</span>
            </div>
            @endif
            
            @if($sale->customer_phone)
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">客户电话</span>
                <span class="text-sm font-medium">{{ $sale->customer_phone }}</span>
            </div>
            @endif
            
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">操作�?/span>
                <span class="text-sm font-medium">{{ $sale->user->real_name }}</span>
            </div>
            
            @if($sale->image_path)
            <div class="mt-4">
                <span class="text-sm text-gray-600 block mb-2">销售凭�?/span>
                <img src="{{ asset('storage/' . $sale->image_path) }}" 
                     alt="销售凭�? 
                     class="w-full max-w-xs rounded-lg border border-gray-200"
                     onerror="this.style.display='none'">
            </div>
            @endif
        </div>
    </div>

    <!-- 销售明�?-->
    @if($sale->saleDetails && count($sale->saleDetails) > 0)
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">🛍�?销售明�?/h2>
        <div class="space-y-3">
            @foreach($sale->saleDetails as $detail)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="font-medium text-gray-900">{{ $detail->product->name }}</div>
                        <div class="text-sm text-gray-500">单价: ¥{{ number_format($detail->price, 2) }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-900">× {{ $detail->quantity }}</div>
                        <div class="text-sm text-gray-500">小计: ¥{{ number_format($detail->total, 2) }}</div>
                    </div>
                </div>
                
                @if(auth()->user()->canViewProfitAndCost() && $detail->cost > 0)
                <div class="flex justify-between text-sm text-gray-600">
                    <span>成本: ¥{{ number_format($detail->cost, 2) }}</span>
                    <span>利润: ¥{{ number_format($detail->profit, 2) }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 盲袋发货明细 -->
    @if($sale->blindBagDeliveries && count($sale->blindBagDeliveries) > 0)
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">📦 发货明细</h2>
        <p class="text-sm text-gray-600 mb-4">主播实际发货的商品和数量</p>
        <div class="space-y-3">
            @foreach($sale->blindBagDeliveries as $delivery)
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="font-medium text-gray-900">{{ $delivery->deliveryProduct->name }}</div>
                        @if(auth()->user()->canViewProfitAndCost())
                        <div class="text-sm text-gray-500">成本: ¥{{ number_format($delivery->unit_cost, 2) }}</div>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-900">× {{ $delivery->quantity }}</div>
                        @if(auth()->user()->canViewProfitAndCost())
                        <div class="text-sm text-gray-500">成本小计: ¥{{ number_format($delivery->total_cost, 2) }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 财务汇�?-->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 财务汇�?/h2>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">销售总额</span>
                <span class="text-lg font-bold text-green-600">¥{{ number_format($sale->total_amount, 2) }}</span>
            </div>
            
            @if(auth()->user()->canViewProfitAndCost())
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">总成�?/span>
                <span class="text-lg font-bold text-red-600">¥{{ number_format($sale->total_cost, 2) }}</span>
            </div>
            
            <div class="border-t pt-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">净利润</span>
                    <span class="text-lg font-bold text-blue-600">¥{{ number_format($sale->total_profit, 2) }}</span>
                </div>
            </div>
            
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">利润�?/span>
                <span class="text-lg font-bold text-purple-600">{{ number_format($sale->profit_rate, 1) }}%</span>
            </div>
            @endif
        </div>
    </div>

    <!-- 操作按钮 -->
    <div class="card p-6">
        <div class="flex space-x-3">
            <a href="{{ route('mobile.sales.index') }}" 
               class="flex-1 btn-secondary py-3 text-center rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>
                返回列表
            </a>
            
            @if(auth()->user()->isAdmin() || auth()->id() == $sale->user_id)
            <form action="{{ route('mobile.sales.destroy', $sale->id) }}" method="POST" class="flex-1" 
                  onsubmit="return confirm('确定要删除这条销售记录吗�?)">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full btn-danger py-3 rounded-lg">
                    <i class="bi bi-trash mr-2"></i>
                    删除记录
                </button>
            </form>
            @endif
        </div>
    </div>
    <div class="h-24"></div>
</div>
@endsection 
