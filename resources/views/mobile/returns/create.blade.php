@extends('layouts.mobile')

@section('content')
<div class="min-h-screen bg-gray-50 py-4">
    <div class="max-w-4xl mx-auto px-4">
        <!-- 页面标题 -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">↩️ <x-lang key="mobile.returns.title"/></h1>
            <p class="text-gray-600"><x-lang key="mobile.returns.subtitle"/></p>
        </div>

        <!-- 退货表�?-->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="{{ route('mobile.returns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- 基本信息 -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="mobile.returns.basic_info"/></h2>
                    
                    <!-- 仓库选择 -->
                    @php $currentStore = $stores->firstWhere('id', $storeId); @endphp
                    <div>
                        <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.current_store"/></label>
                        <select name="store_id" id="store-select" class="form-input w-full px-3 py-2 rounded-lg border bg-gray-50 text-gray-700" onchange="onStoreChange()">
                            <option value=""><x-lang key="mobile.returns.please_select"/></option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" @if($storeId == $store->id) selected @endif>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 客户信息 -->
                    <div class="mb-4">
                        <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.customer_info"/></label>
                        <input type="text" name="customer" value="{{ old('customer') }}" 
                               class="form-input w-full px-3 py-2 rounded-lg border" 
                               placeholder="<x-lang key="mobile.returns.customer_placeholder"/>">
                        @error('customer')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 退货照片 -->
                    <div class="mb-4">
                        <label class="form-label block text-sm font-medium mb-2">📷 <x-lang key="mobile.returns.return_photo"/></label>
                        
                        <!-- 隐藏的文件输入框 -->
                        <input type="file" name="image" accept="image/*" class="hidden" id="returnImageInput">
                        
                        <!-- 上传选项按钮 -->
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <button type="button" onclick="selectFromGalleryReturn()" class="flex flex-col items-center justify-center py-4 bg-blue-50 text-blue-700 rounded-lg border border-blue-200 hover:bg-blue-100 transition-all duration-200 transform hover:scale-105">
                                <i class="bi bi-image text-2xl mb-1"></i>
                                <span class="text-sm font-medium">选择相册</span>
                            </button>
                            <button type="button" onclick="takePhotoReturn()" class="flex flex-col items-center justify-center py-4 bg-green-50 text-green-700 rounded-lg border border-green-200 hover:bg-green-100 transition-all duration-200 transform hover:scale-105">
                                <i class="bi bi-camera text-2xl mb-1"></i>
                                <span class="text-sm font-medium">拍照</span>
                            </button>
                        </div>
                        
                        <!-- 图片预览 -->
                        <div id="return-image-preview" class="mt-2 hidden">
                            <div class="relative inline-block">
                                <img src="" alt="预览图" class="max-w-full h-48 rounded-lg border border-gray-200 object-cover shadow-md">
                                <button type="button" onclick="removeReturnImage()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600 transition-colors duration-200 shadow-lg">
                                    ×
                                </button>
                            </div>
                            <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded p-2" id="return-image-info"></div>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-1">💡 <x-lang key="mobile.returns.photo_desc"/></p>
                        @error('image')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 退货原�?-->
                    <div class="mb-4">
                        <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.return_reason"/></label>
                        <textarea name="remark" rows="3" 
                                  class="form-textarea w-full px-3 py-2 rounded-lg border" 
                                  placeholder="<x-lang key="mobile.returns.reason_placeholder"/>">{{ old('remark') }}</textarea>
                        @error('remark')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 退货商�?-->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 <x-lang key="mobile.returns.return_products"/></h2>
                    
                    <!-- 仓库未选择时的提示 -->
                    <div id="no-store-message" class="text-center py-8" style="display: none;">
                        <i class="bi bi-box text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">请先选择仓库以查看可退货的商品</p>
                    </div>
                    
                    <!-- 加载中状态 -->
                    <div id="loading-products" class="text-center py-8" style="display: none;">
                        <i class="bi bi-arrow-clockwise animate-spin text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">正在加载商品...</p>
                    </div>
                    
                    <!-- 商品列表 -->
                    <div id="products-container" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        @foreach($products as $product)
                            <div class="border rounded-lg p-4 bg-gray-50 product-item" data-store-id="{{ $storeId }}">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium text-gray-900">{{ $product->name }}</h3>
                                    <span class="badge-warning text-xs px-2 py-1 rounded-full">
                                        <x-lang key="mobile.returns.price"/>: ¥{{ number_format($product->price, 2) }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600"><x-lang key="mobile.returns.return_quantity"/>:</label>
                                    <input type="number" name="products[{{ $product->id }}][quantity]" 
                                           min="0" max="999" value="0"
                                           class="form-input w-20 px-2 py-1 rounded border text-center"
                                           data-product-id="{{ $product->id }}"
                                           data-product-price="{{ $product->price }}">
                                    <input type="hidden" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                                </div>
                                
                                <p class="text-xs text-gray-500 mt-1 text-center"><x-lang key="mobile.returns.return_quantity"/></p>
                            </div>
                        @endforeach
                        
                        <!-- 无商品时的提示 -->
                        <div id="no-products-message" class="col-span-2 text-center py-8" style="display: none;">
                            <i class="bi bi-box-seam text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">该仓库暂无可退货的商品</p>
                        </div>
                    </div>
                </div>

                <!-- 退货统�?-->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <h4 class="text-md font-semibold text-red-900 mb-3"><x-lang key="mobile.returns.return_stats"/></h4>
                    
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-sm text-red-600"><x-lang key="mobile.returns.return_quantity"/></p>
                            <p class="text-lg font-bold text-red-700" id="totalQuantity">0 <x-lang key="mobile.returns.pieces"/></p>
                        </div>
                        <div>
                            <p class="text-sm text-red-600"><x-lang key="mobile.returns.return_amount"/></p>
                            <p class="text-lg font-bold text-red-700" id="totalAmount">¥0.00</p>
                        </div>
                        <div>
                            <p class="text-sm text-red-600"><x-lang key="mobile.returns.cost_loss"/></p>
                            <p class="text-lg font-bold text-red-700" id="totalCost">¥0.00</p>
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
    </div>
</div>

<script>
function selectFromGalleryReturn() {
    const input = document.getElementById('returnImageInput');
    input.removeAttribute('capture');
    input.click();
}

function takePhotoReturn() {
    const input = document.getElementById('returnImageInput');
    input.setAttribute('capture', 'environment');
    input.click();
}

function removeReturnImage() {
    const input = document.getElementById('returnImageInput');
    const preview = document.getElementById('return-image-preview');
    const info = document.getElementById('return-image-info');
    
    input.value = '';
    preview.classList.add('hidden');
    info.innerHTML = '';
}

document.getElementById('returnImageInput').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById('return-image-preview');
            const info = document.getElementById('return-image-info');
            
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('hidden');
            
            // 显示文件信息
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            info.innerHTML = `文件名: ${file.name}<br>大小: ${fileSize} MB<br>类型: ${file.type}`;
        }
        
        reader.readAsDataURL(file);
    }
});

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

        document.getElementById('totalQuantity').textContent = totalQuantity + ' �?;
        document.getElementById('totalAmount').textContent = '¥' + totalAmount.toFixed(2);
        document.getElementById('totalCost').textContent = '¥' + totalCost.toFixed(2);
    }

    quantityInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });

    // 初始计算
    updateTotals();
    
    // 如果有初始仓库，检查是否需要显示商品
    const storeSelect = document.getElementById('store-select');
    if (storeSelect.value) {
        onStoreChange();
    }
});

// 仓库变化处理函数
async function onStoreChange() {
    const storeSelect = document.getElementById('store-select');
    const storeId = storeSelect.value;
    const noStoreMessage = document.getElementById('no-store-message');
    const loadingProducts = document.getElementById('loading-products');
    const productsContainer = document.getElementById('products-container');
    const noProductsMessage = document.getElementById('no-products-message');
    
    // 隐藏所有状态
    noStoreMessage.style.display = 'none';
    loadingProducts.style.display = 'none';
    productsContainer.style.display = 'none';
    noProductsMessage.style.display = 'none';
    
    if (!storeId) {
        noStoreMessage.style.display = 'block';
        return;
    }
    
    loadingProducts.style.display = 'block';
    
    try {
        const response = await fetch(`/api/stores/${storeId}/products`);
        if (response.ok) {
            const data = await response.json();
            const products = data.standard_products || [];
            
            if (products.length === 0) {
                noProductsMessage.style.display = 'block';
            } else {
                // 清空现有商品
                productsContainer.innerHTML = '';
                
                // 添加新商品
                products.forEach(product => {
                    const productHtml = `
                        <div class="border rounded-lg p-4 bg-gray-50 product-item" data-store-id="${storeId}">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-medium text-gray-900">${product.name}</h3>
                                <span class="badge-warning text-xs px-2 py-1 rounded-full">
                                    价格: ¥${parseFloat(product.price).toFixed(2)}
                                </span>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600">退货数量:</label>
                                <input type="number" name="products[${product.id}][quantity]" 
                                       min="0" max="999" value="0"
                                       class="form-input w-20 px-2 py-1 rounded border text-center"
                                       data-product-id="${product.id}"
                                       data-product-price="${product.price}">
                                <input type="hidden" name="products[${product.id}][id]" value="${product.id}">
                            </div>
                            
                            <p class="text-xs text-gray-500 mt-1 text-center">退货数量</p>
                        </div>
                    `;
                    productsContainer.innerHTML += productHtml;
                });
                
                productsContainer.style.display = 'grid';
                
                // 重新绑定事件
                bindQuantityEvents();
            }
        } else {
            console.error('Failed to load products');
            noProductsMessage.style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading products:', error);
        noProductsMessage.style.display = 'block';
    }
}

// 绑定数量输入事件
function bindQuantityEvents() {
    const quantityInputs = document.querySelectorAll('input[name*="[quantity]"]');
    quantityInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });
}
</script>
@endsection 
