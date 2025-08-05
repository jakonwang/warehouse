@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- 调试信息 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">🔍 退货界面调试信息</h1>
        
        <div class="space-y-4">
            <div>
                <h3 class="font-semibold text-gray-900">用户信息</h3>
                <p class="text-sm text-gray-600">用户名: {{ auth()->user()->username }}</p>
                <p class="text-sm text-gray-600">角色: {{ auth()->user()->role ? auth()->user()->role->name : '无角色' }}</p>
            </div>
            
            <div>
                <h3 class="font-semibold text-gray-900">仓库信息</h3>
                <p class="text-sm text-gray-600">可访问仓库数量: {{ $stores->count() }}</p>
                <p class="text-sm text-gray-600">当前仓库ID: {{ $storeId ?? '未选择' }}</p>
                
                @if($stores->count() > 0)
                    <div class="mt-2">
                        <p class="text-sm font-medium text-gray-700">可访问仓库列表:</p>
                        @foreach($stores as $store)
                            <p class="text-sm text-gray-600 ml-4">- {{ $store->name }} (ID: {{ $store->id }})</p>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <div>
                <h3 class="font-semibold text-gray-900">商品信息</h3>
                <p class="text-sm text-gray-600">商品数量: {{ $products->count() }}</p>
                
                @if($products->count() > 0)
                    <div class="mt-2">
                        <p class="text-sm font-medium text-gray-700">商品列表:</p>
                        @foreach($products as $product)
                            <p class="text-sm text-gray-600 ml-4">- {{ $product->name }} (价格: ¥{{ $product->price }})</p>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <div>
                <h3 class="font-semibold text-gray-900">显示逻辑</h3>
                <p class="text-sm text-gray-600">storeId存在: {{ $storeId ? '是' : '否' }}</p>
                <p class="text-sm text-gray-600">商品数量 > 0: {{ $products->count() > 0 ? '是' : '否' }}</p>
                <p class="text-sm text-gray-600">显示商品: {{ ($storeId && $products->count() > 0) ? '是' : '否' }}</p>
            </div>
        </div>
    </div>
    
    <!-- 模拟商品显示 -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 商品显示测试</h2>
        
        <div class="grid grid-cols-2 gap-4">
            @if($storeId && $products->count() > 0)
                @foreach($products as $product)
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ $product->name }}</span>
                            <span class="badge-warning text-xs px-2 py-1 rounded-full">
                                价格: ¥{{ number_format($product->price, 2) }}
                            </span>
                        </div>
                        <input type="number" 
                               min="0" max="999" value="0"
                               class="form-input w-full px-3 py-2 rounded-lg border text-center text-lg font-semibold"
                               placeholder="0">
                        <p class="text-xs text-gray-500 mt-1 text-center">退货数量</p>
                    </div>
                @endforeach
            @elseif($storeId && $products->count() == 0)
                <div class="col-span-2 text-center py-8">
                    <i class="bi bi-box-seam text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">该仓库暂无可退货的商品</p>
                    <p class="text-xs text-gray-400 mt-2">仓库ID: {{ $storeId }}</p>
                </div>
            @else
                <div class="col-span-2 text-center py-8">
                    <i class="bi bi-box-seam text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">请选择仓库以查看可退货的商品</p>
                    <p class="text-xs text-gray-400 mt-2">当前仓库ID: {{ $storeId ?? '未选择' }}</p>
                    <p class="text-xs text-gray-400">商品数量: {{ $products->count() }}</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- 返回按钮 -->
    <div class="flex justify-center">
        <a href="{{ route('mobile.returns.create') }}" class="btn-primary px-8 py-3 text-lg">
            返回退货界面
        </a>
    </div>
</div>
@endsection 