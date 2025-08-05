@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- 测试标题 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">🧪 退货界面测试</h1>
        <p class="text-gray-600">测试商品显示功能</p>
    </div>

    <!-- 数据信息 -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">📊 数据信息</h2>
        <div class="space-y-2">
            <p class="text-sm text-gray-600">仓库ID: {{ $storeId ?? '未选择' }}</p>
            <p class="text-sm text-gray-600">商品数量: {{ $products->count() }}</p>
            <p class="text-sm text-gray-600">显示条件: {{ ($storeId && $products->count() > 0) ? '满足' : '不满足' }}</p>
        </div>
    </div>

    <!-- 商品显示测试 -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 商品显示测试</h2>
        
        @if($storeId && $products->count() > 0)
            <div class="space-y-4">
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
            </div>
        @elseif($storeId && $products->count() == 0)
            <div class="text-center py-8">
                <i class="bi bi-box-seam text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">该仓库暂无可退货的商品</p>
                <p class="text-xs text-gray-400 mt-2">仓库ID: {{ $storeId }}</p>
            </div>
        @else
            <div class="text-center py-8">
                <i class="bi bi-box-seam text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">请选择仓库以查看可退货的商品</p>
                <p class="text-xs text-gray-400 mt-2">当前仓库ID: {{ $storeId ?? '未选择' }}</p>
                <p class="text-xs text-gray-400">商品数量: {{ $products->count() }}</p>
            </div>
        @endif
    </div>

    <!-- 返回按钮 -->
    <div class="flex justify-center">
        <a href="{{ route('mobile.returns.create') }}" class="btn-primary px-8 py-3 text-lg">
            返回退货界面
        </a>
    </div>
</div>
@endsection 