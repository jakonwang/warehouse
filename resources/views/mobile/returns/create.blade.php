@extends('layouts.mobile')

@section('content')
<div class="min-h-screen bg-gray-50 py-4">
    <div class="max-w-4xl mx-auto px-4">
        <!-- 页面标题 -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">↩️ <x-lang key="messages.mobile.returns.title"/></h1>
            <p class="text-gray-600"><x-lang key="messages.mobile.returns.subtitle"/></p>
        </div>

        <!-- 退货表单 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="{{ route('mobile.returns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- 基本信息 -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="messages.mobile.returns.basic_info"/></h2>
                    
                    <!-- 仓库选择 -->
                    @php $currentStore = $stores->firstWhere('id', $storeId); @endphp
                    <div>
                        <label class="form-label block text-sm font-medium mb-2"><x-lang key="messages.mobile.returns.current_store"/></label>
                        <select name="store_id" class="form-input w-full px-3 py-2 rounded-lg border bg-gray-50 text-gray-700">
                            <option value=""><x-lang key="messages.mobile.returns.please_select"/></option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" @if($storeId == $store->id) selected @endif>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 客户信息 -->
                    <div class="mb-4">
                        <label class="form-label block text-sm font-medium mb-2"><x-lang key="messages.mobile.returns.customer_info"/></label>
                        <input type="text" name="customer" value="{{ old('customer') }}" 
                               class="form-input w-full px-3 py-2 rounded-lg border" 
                               placeholder="<x-lang key="messages.mobile.returns.customer_placeholder"/>">
                        @error('customer')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 退货照片 -->
                    <div class="mb-4">
                        <label class="form-label block text-sm font-medium mb-2"><x-lang key="messages.mobile.returns.return_photo"/></label>
                        <input type="file" name="image" accept="image/*" 
                               class="form-input w-full px-3 py-2 rounded-lg border">
                        <p class="text-xs text-gray-500 mt-1"><x-lang key="messages.mobile.returns.photo_desc"/></p>
                        @error('image')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 退货原因 -->
                    <div class="mb-4">
                        <label class="form-label block text-sm font-medium mb-2"><x-lang key="messages.mobile.returns.return_reason"/></label>
                        <textarea name="remark" rows="3" 
                                  class="form-textarea w-full px-3 py-2 rounded-lg border" 
                                  placeholder="<x-lang key="messages.mobile.returns.reason_placeholder"/>">{{ old('remark') }}</textarea>
                        @error('remark')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 退货商品 -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 <x-lang key="messages.mobile.returns.return_products"/></h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        @foreach($products as $product)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium text-gray-900">{{ $product->name }}</h3>
                                    <span class="badge-warning text-xs px-2 py-1 rounded-full">
                                        <x-lang key="messages.mobile.returns.price"/>: ¥{{ number_format($product->price, 2) }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600"><x-lang key="messages.mobile.returns.return_quantity"/>:</label>
                                    <input type="number" name="products[{{ $product->id }}][quantity]" 
                                           min="0" max="999" value="0"
                                           class="form-input w-20 px-2 py-1 rounded border text-center"
                                           data-product-id="{{ $product->id }}"
                                           data-product-price="{{ $product->price }}">
                                    <input type="hidden" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                                </div>
                                
                                <p class="text-xs text-gray-500 mt-1 text-center"><x-lang key="messages.mobile.returns.return_quantity"/></p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 退货统计 -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <h4 class="text-md font-semibold text-red-900 mb-3"><x-lang key="messages.mobile.returns.return_stats"/></h4>
                    
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-sm text-red-600"><x-lang key="messages.mobile.returns.return_quantity"/></p>
                            <p class="text-lg font-bold text-red-700" id="totalQuantity">0 <x-lang key="messages.mobile.returns.pieces"/></p>
                        </div>
                        <div>
                            <p class="text-sm text-red-600"><x-lang key="messages.mobile.returns.return_amount"/></p>
                            <p class="text-lg font-bold text-red-700" id="totalAmount">¥0.00</p>
                        </div>
                        <div>
                            <p class="text-sm text-red-600"><x-lang key="messages.mobile.returns.cost_loss"/></p>
                            <p class="text-lg font-bold text-red-700" id="totalCost">¥0.00</p>
                        </div>
                    </div>
                </div>

                <!-- 提交按钮 -->
                <div class="flex justify-center">
                    <button type="submit" class="btn-primary px-8 py-3 text-lg">
                        <x-lang key="messages.mobile.returns.confirm_return"/>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('input[name*="[quantity]"]');
    let totalQuantity = 0;
    let totalAmount = 0;
    let totalCost = 0;

    function updateTotals() {
        totalQuantity = 0;
        totalAmount = 0;
        totalCost = 0;

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