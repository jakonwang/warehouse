@extends('layouts.app')

@section('title', '新增库存')
@section('header', '新增库存')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">新增库存记录</h3>
                <a href="{{ route('inventory.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    <i class="bi bi-arrow-left mr-1"></i>返回列表
                </a>
            </div>
        </div>

        <form action="{{ route('inventory.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 商品选择 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">选择商品 *</label>
                    <select name="product_id" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">请选择商品</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 仓库选择 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">选择仓库 *</label>
                    <select name="store_id" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value=""><x-lang key="messages.stores.please_select"/></option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 初始库存数量 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">初始库存数量 *</label>
                    <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           placeholder="请输入初始库存数量">
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 最小库存 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">最小库存 *</label>
                    <input type="number" name="min_quantity" value="{{ old('min_quantity', 0) }}" min="0" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           placeholder="请输入最小库存">
                    @error('min_quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 最大库存 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">最大库存 *</label>
                    <input type="number" name="max_quantity" value="{{ old('max_quantity', 100) }}" min="0" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           placeholder="请输入最大库存">
                    @error('max_quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 备注 -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">备注</label>
                    <textarea name="remark" rows="3"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                              placeholder="请输入备注信息">{{ old('remark') }}</textarea>
                    @error('remark')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 提示信息 -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-start">
                    <i class="bi bi-info-circle text-blue-500 mr-3 mt-0.5"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-1">注意事项：</p>
                        <ul class="space-y-1">
                            <li>• 同一商品在同一仓库中只能有一条库存记录</li>
                            <li>• 初始库存数量将作为入库记录保存</li>
                            <li>• 最小库存和最大库存用于库存预警</li>
                            <li>• 创建后可以随时调整库存设置</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 操作按钮 -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('inventory.index') }}" 
                   class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all duration-200">
                    取消
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="bi bi-plus-lg mr-2"></i>创建库存
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// 表单验证
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const maxQuantityInput = document.querySelector('input[name="max_quantity"]');
    const minQuantityInput = document.querySelector('input[name="min_quantity"]');
    
    // 验证最大库存必须大于等于最小库存
    function validateQuantity() {
        const minQuantity = parseInt(minQuantityInput.value) || 0;
        const maxQuantity = parseInt(maxQuantityInput.value) || 0;
        
        if (maxQuantity < minQuantity) {
            maxQuantityInput.setCustomValidity('最大库存不能小于最小库存');
        } else {
            maxQuantityInput.setCustomValidity('');
        }
    }
    
    minQuantityInput.addEventListener('input', validateQuantity);
    maxQuantityInput.addEventListener('input', validateQuantity);
    
    // 表单提交验证
    form.addEventListener('submit', function(e) {
        validateQuantity();
        if (!form.checkValidity()) {
            e.preventDefault();
        }
    });
});
</script>
@endsection 