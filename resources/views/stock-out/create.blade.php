@extends('layouts.app')

@section('title', '批量出库')
@section('header', '批量出库')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">批量出库</h3>
                <a href="{{ route('stock-outs.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    <i class="bi bi-arrow-left mr-1"></i>返回列表
                </a>
            </div>
        </div>

        <form action="{{ route('stock-outs.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 基本信息 -->
                <div class="md:col-span-2">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">基本信息</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">选择仓库 *</label>
                            <select name="store_id" id="store-select" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                <option value=""><x-lang key="messages.stores.please_select"/></option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ old('store_id', $currentStoreId) == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('store_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">客户信息</label>
                            <input type="text" name="customer" value="{{ old('customer') }}" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                   placeholder="请输入客户信息">
                            @error('customer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 商品选择 -->
                <div class="md:col-span-2">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">出库商品</h4>
                    <div id="products-container">
                        <div class="product-item bg-gray-50 rounded-xl p-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">选择商品 *</label>
                                <select name="products[0][id]" class="product-select w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" required>
                                    <option value="">请先选择仓库</option>
                                    @if($products->count() > 0)
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('products.0.id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->code }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">出库数量 *</label>
                                    <input type="number" name="price_series[0][quantity]" value="{{ old('price_series.0.quantity', 0) }}" min="0" required
                                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                           placeholder="请输入数量">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">单价 *</label>
                                    <input type="number" name="price_series[0][unit_price]" value="{{ old('price_series.0.unit_price', 0) }}" min="0" step="0.01" required
                                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                           placeholder="请输入单价">
                                </div>
                                
                                <div class="flex items-end">
                                    <button type="button" onclick="removeProduct(this)" class="w-full px-4 py-3 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-all duration-200">
                                        <i class="bi bi-trash mr-2"></i>删除
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" onclick="addProduct()" class="w-full px-6 py-3 border-2 border-dashed border-gray-300 text-gray-600 rounded-xl font-medium hover:border-gray-400 hover:text-gray-700 transition-all duration-200">
                        <i class="bi bi-plus-lg mr-2"></i>添加商品
                    </button>
                </div>

                <!-- 备注信息 -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">备注信息</label>
                    <textarea name="remark" rows="3"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                              placeholder="请输入备注信息">{{ old('remark') }}</textarea>
                    @error('remark')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 图片上传 -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">上传图片</label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all duration-200">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="bi bi-cloud-upload text-gray-400 text-3xl mb-2"></i>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">点击上传</span> 或拖拽文件到此处
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF 最大 2MB</p>
                            </div>
                            <input type="file" name="image" class="hidden" accept="image/*">
                        </label>
                    </div>
                    @error('image')
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
                            <li>• 出库数量不能超过当前库存</li>
                            <li>• 单价为必填项，用于计算总金额</li>
                            <li>• 可以添加多个商品进行批量出库</li>
                            <li>• 图片为可选，用于记录出库凭证</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 操作按钮 -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('stock-outs.index') }}" 
                   class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all duration-200">
                    取消
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="bi bi-box-arrow-up mr-2"></i>确认出库
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let productIndex = 1;
let currentProducts = [];

// 加载仓库商品
function loadStoreProducts(storeId) {
    if (!storeId) {
        // 清空所有商品选择
        document.querySelectorAll('.product-select').forEach(select => {
            select.innerHTML = '<option value="">请先选择仓库</option>';
        });
        return;
    }
    
    fetch(`/api/stock-outs/store-products?store_id=${storeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.products) {
                currentProducts = data.products;
                updateProductOptions();
            }
        })
        .catch(error => {
            console.error('加载商品失败:', error);
            alert('加载商品失败，请重试');
        });
}

// 更新所有商品选择框的选项
function updateProductOptions() {
    const productSelects = document.querySelectorAll('.product-select');
    productSelects.forEach(select => {
        const currentValue = select.value;
        select.innerHTML = '<option value="">请选择商品</option>';
        
        currentProducts.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.display_name;
            if (product.id == currentValue) {
                option.selected = true;
            }
            select.appendChild(option);
        });
    });
}

// 监听仓库选择变化
document.addEventListener('DOMContentLoaded', function() {
    const storeSelect = document.getElementById('store-select');
    if (storeSelect) {
        storeSelect.addEventListener('change', function() {
            loadStoreProducts(this.value);
        });
        
        // 如果页面加载时已有选中的仓库，加载商品
        if (storeSelect.value) {
            loadStoreProducts(storeSelect.value);
        }
    }
});

function addProduct() {
    const container = document.getElementById('products-container');
    const newProduct = document.createElement('div');
    newProduct.className = 'product-item bg-gray-50 rounded-xl p-4 mb-4';
    newProduct.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">选择商品 *</label>
                <select name="products[${productIndex}][id]" class="product-select w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" required>
                    <option value="">请选择商品</option>
                    ${currentProducts.map(product => 
                        `<option value="${product.id}">${product.display_name}</option>`
                    ).join('')}
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">出库数量 *</label>
                <input type="number" name="price_series[${productIndex}][quantity]" value="0" min="0" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                       placeholder="请输入数量">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">单价 *</label>
                <input type="number" name="price_series[${productIndex}][unit_price]" value="0" min="0" step="0.01" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                       placeholder="请输入单价">
            </div>
            
            <div class="flex items-end">
                <button type="button" onclick="removeProduct(this)" class="w-full px-4 py-3 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-all duration-200">
                    <i class="bi bi-trash mr-2"></i>删除
                </button>
            </div>
        </div>
    `;
    container.appendChild(newProduct);
    productIndex++;
}

function removeProduct(button) {
    const productItem = button.closest('.product-item');
    if (document.querySelectorAll('.product-item').length > 1) {
        productItem.remove();
    } else {
        alert('至少需要保留一个商品项');
    }
}

// 文件上传预览
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const uploadLabel = fileInput.closest('label');
                    if (uploadLabel) {
                        uploadLabel.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-32 object-cover rounded-xl">
                        `;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection 