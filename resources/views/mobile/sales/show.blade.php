@extends('layouts.mobile')

@section('title', __('mobile.sales.details'))

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- 顶部导航 -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('mobile.sales.index') }}" class="text-gray-600">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <h1 class="text-xl font-semibold text-gray-900">{{ __('mobile.sales.details') }}</h1>
            <div class="w-6"></div>
        </div>
        <p class="text-gray-600">{{ __('mobile.sales.order') }} #{{ $sale->id }}</p>
    </div>

    <!-- 基本信息 -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 {{ __('mobile.sales.basic_info') }}</h2>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">{{ __('mobile.sales.sale_time') }}</span>
                <span class="text-sm font-medium">{{ $sale->created_at->format('Y-m-d H:i:s') }}</span>
            </div>
            
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">{{ __('mobile.sales.sale_type') }}</span>
                @if($sale->sale_type === 'blind_bag')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        🎁 {{ __('mobile.sales.blind_bag_sale') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        🛍️ {{ __('mobile.sales.standard_sale') }}
                    </span>
                @endif
            </div>
            
            @if($sale->store->name)
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">{{ __('mobile.sales.store') }}</span>
                <span class="text-sm font-medium">{{ $sale->store->name }}</span>
            </div>
            @endif
            
            @if($sale->customer_name)
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">{{ __('mobile.sales.customer_name') }}</span>
                <span class="text-sm font-medium">{{ $sale->customer_name }}</span>
            </div>
            @endif
            
            @if($sale->customer_phone)
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">{{ __('mobile.sales.customer_phone') }}</span>
                <span class="text-sm font-medium">{{ $sale->customer_phone }}</span>
            </div>
            @endif
            
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">{{ __('mobile.sales.operator') }}</span>
                <span class="text-sm font-medium">{{ $sale->user->real_name }}</span>
            </div>
            
            @if($sale->image_path)
            <div class="mt-4">
                <span class="text-sm text-gray-600 block mb-2">{{ __('mobile.sales.sale_proof') }}</span>
                <img src="{{ asset('storage/' . $sale->image_path) }}" 
                     alt="{{ __('mobile.sales.sale_proof') }}" 
                     class="w-full max-w-xs rounded-lg border border-gray-200"
                     onerror="this.style.display='none'">
            </div>
            @endif
        </div>
    </div>

    <!-- 销售明细 -->
    @if($sale->saleDetails && count($sale->saleDetails) > 0)
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">🛍️ {{ __('mobile.sales.sale_details') }}</h2>
        <div class="space-y-3">
            @foreach($sale->saleDetails as $detail)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="font-medium text-gray-900">{{ $detail->product->name }}</div>
                        <div class="text-sm text-gray-500">{{ __('mobile.sales.unit_price') }}: ¥{{ number_format($detail->price, 2) }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-900">× {{ $detail->quantity }}</div>
                        <div class="text-sm text-gray-500">{{ __('mobile.sales.subtotal') }}: ¥{{ number_format($detail->total, 2) }}</div>
                    </div>
                </div>
                
                @if(auth()->user()->canViewProfitAndCost() && $detail->cost > 0)
                <div class="flex justify-between text-sm text-gray-600">
                    <span>{{ __('mobile.sales.cost') }}: ¥{{ number_format($detail->cost, 2) }}</span>
                    <span>{{ __('mobile.sales.profit') }}: ¥{{ number_format($detail->profit, 2) }}</span>
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
        <h2 class="text-lg font-semibold text-gray-900 mb-4">📦 {{ __('mobile.sales.delivery_details') }}</h2>
        <p class="text-sm text-gray-600 mb-4">{{ __('mobile.sales.delivery_description') }}</p>
        <div class="space-y-3">
            @foreach($sale->blindBagDeliveries as $delivery)
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="font-medium text-gray-900">{{ $delivery->deliveryProduct->name }}</div>
                        @if(auth()->user()->canViewProfitAndCost())
                        <div class="text-sm text-gray-500">{{ __('mobile.sales.cost') }}: ¥{{ number_format($delivery->unit_cost, 2) }}</div>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-900">× {{ $delivery->quantity }}</div>
                        @if(auth()->user()->canViewProfitAndCost())
                        <div class="text-sm text-gray-500">{{ __('mobile.sales.cost_subtotal') }}: ¥{{ number_format($delivery->total_cost, 2) }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 财务汇总 -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 {{ __('mobile.sales.financial_summary') }}</h2>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">{{ __('mobile.sales.total_amount') }}</span>
                <span class="text-lg font-bold text-green-600">¥{{ number_format($sale->total_amount, 2) }}</span>
            </div>
            
            @if(auth()->user()->canViewProfitAndCost())
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">{{ __('mobile.sales.total_cost') }}</span>
                <span class="text-lg font-bold text-red-600">¥{{ number_format($sale->total_cost, 2) }}</span>
            </div>
            
            <div class="border-t pt-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ __('mobile.sales.net_profit') }}</span>
                    <span class="text-lg font-bold text-blue-600">¥{{ number_format($sale->total_profit, 2) }}</span>
                </div>
            </div>
            
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">{{ __('mobile.sales.profit_rate') }}</span>
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
                {{ __('mobile.common.back_to_list') }}
            </a>
            
            @if(auth()->user()->isAdmin() || auth()->id() == $sale->user_id)
            <form action="{{ route('mobile.sales.destroy', $sale->id) }}" method="POST" class="flex-1" 
                  onsubmit="return confirm('{{ __('mobile.sales.confirm_delete') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full btn-danger py-3 rounded-lg">
                    <i class="bi bi-trash mr-2"></i>
                    {{ __('mobile.sales.delete_record') }}
                </button>
            </form>
            @endif
        </div>
    </div>
    <div class="h-24"></div>
</div>
@endsection 
