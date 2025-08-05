@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- 标题 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">↩️ <x-lang key="mobile.returns.title"/></h1>
        <p class="text-gray-600"><x-lang key="mobile.returns.subtitle"/></p>
    </div>

    <!-- 退货表单 -->
    <form action="{{ route('mobile.returns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 基本信息 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="mobile.returns.basic_info"/></h2>
            <div class="space-y-4">
                <!-- 仓库选择 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.select_store"/></label>
                    <select name="store_id" class="form-input w-full px-3 py-2 rounded-lg border" required>
                        <option value=""><x-lang key="mobile.returns.please_select_store"/></option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" @if($storeId == $store->id) selected @endif>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 客户信息 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.customer_info"/></label>
                    <input type="text" name="customer" value="{{ old('customer') }}" 
                        class="form-input w-full px-3 py-2 rounded-lg border" placeholder="<x-lang key="mobile.returns.customer_placeholder"/>">
                </div>

                <!-- 退货照片 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2">📷 <x-lang key="mobile.returns.return_photo"/></label>
                    <input type="file" name="image" accept="image/*" 
                        class="form-input w-full px-3 py-2 rounded-lg border">
                    <p class="text-xs text-gray-500 mt-1">💡 <x-lang key="mobile.returns.photo_desc"/></p>
                </div>

                <!-- 退货原因 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.return_reason"/></label>
                    <textarea name="remark" rows="3" 
                              class="form-textarea w-full px-3 py-2 rounded-lg border" 
                              placeholder="<x-lang key="mobile.returns.reason_placeholder"/>">{{ old('remark') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 商品选择 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 <x-lang key="mobile.returns.return_products"/></h2>
            
            <!-- 商品列表 -->
            @if($storeId && $products->count() > 0)
                <div class="space-y-4">
                    @foreach($products as $product)
                        <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">{{ $product->name }}</span>
                                <span class="badge-warning text-xs px-2 py-1 rounded-full">
                                    <x-lang key="mobile.returns.price"/>: ¥{{ number_format($product->price, 2) }}
                                </span>
                            </div>
                            <input type="hidden" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                            <input type="number" name="products[{{ $product->id }}][quantity]" 
                                   min="0" max="999" value="0"
                                   class="form-input w-full px-3 py-2 rounded-lg border text-center text-lg font-semibold"
                                   data-product-id="{{ $product->id }}"
                                   data-product-price="{{ $product->price }}"
                                   placeholder="0">
                            <p class="text-xs text-gray-500 mt-1 text-center"><x-lang key="mobile.returns.return_quantity"/></p>
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
            
            <!-- 退货统计 -->
            <div class="bg-red-50 rounded-lg p-4 mt-4">
                <h4 class="text-md font-semibold text-red-900 mb-3"><x-lang key="mobile.returns.return_stats"/></h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="mobile.returns.return_quantity"/></p>
                        <p class="text-lg font-bold text-red-700" id="totalQuantity">0 <x-lang key="mobile.returns.pieces"/></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="mobile.returns.return_amount"/></p>
                        <p class="text-lg font-bold text-red-700" id="totalAmount">¥0.00</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="mobile.returns.cost_loss"/></p>
                        <p class="text-lg font-bold text-red-700" id="totalCost">¥0.00</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 提交按钮 -->
        <div class="flex justify-center">
            <button type="submit" class="btn-primary px-8 py-3 text-lg">
                <x-lang key="mobile.returns.confirm_return"/>
            </button>
        </div>
    </form>
</div>

<script>
// 简单的总计更新功能
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('input[name*="[quantity]"]');
    
    function updateTotals() {
        let totalQuantity = 0;
        let totalAmount = 0;
        let totalCost = 0;

        quantityInputs.forEach(input => {
            const quantity = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.productPrice) || 0;
            const cost = price * 0.6; // 假设成本为售价的60%

            totalQuantity += quantity;
            totalAmount += quantity * price;
            totalCost += quantity * cost;
        });

        document.getElementById('totalQuantity').textContent = totalQuantity + ' 件';
        document.getElementById('totalAmount').textContent = '¥' + totalAmount.toFixed(2);
        document.getElementById('totalCost').textContent = '¥' + totalCost.toFixed(2);
    }

    quantityInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });

    // 初始计算
    updateTotals();
});
</script>
@endsection 