@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4" x-data="returnForm" x-init="init()">
    <!-- 标题 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">↩️ <x-lang key="mobile.returns.title"/></h1>
        <p class="text-gray-600"><x-lang key="mobile.returns.subtitle"/></p>
    </div>

    @if (session('success'))
        <div class="card p-4 border-l-4 border-green-500 bg-green-50">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="card p-4 border-l-4 border-red-500 bg-red-50">
            <ul class="text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>�?{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 退货表�?-->
    <form action="{{ route('mobile.returns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 基本信息 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="mobile.returns.basic_info"/></h2>
            <div class="space-y-4">
                <!-- 仓库选择 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.select_store"/></label>
                    <select name="store_id" class="form-input w-full px-3 py-2 rounded-lg border" required 
                            @change="loadStoreProducts($event.target.value)">
                        <option value=""><x-lang key="mobile.returns.please_select_store"/></option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 客户信息 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.customer_info"/></label>
                    <input type="text" name="customer" value="{{ old('customer') }}" 
                        class="form-input w-full px-3 py-2 rounded-lg border" placeholder="<x-lang key="mobile.returns.customer_placeholder"/>">
                </div>

                <!-- 退货照�?-->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.return_photo"/></label>
                    <input type="file" name="image" accept="image/*" 
                        class="form-input w-full px-3 py-2 rounded-lg border">
                    <p class="text-xs text-gray-500 mt-1"><x-lang key="mobile.returns.photo_desc"/></p>
                </div>

                <!-- 备注 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.return_reason"/></label>
                    <textarea name="remark" rows="2" class="form-input w-full px-3 py-2 rounded-lg border" 
                        placeholder="<x-lang key="mobile.returns.reason_placeholder"/>">{{ old('remark') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 商品选择 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 <x-lang key="mobile.returns.return_products"/></h2>
            <div class="grid grid-cols-2 gap-4" id="products-container">
                @foreach($products as $product)
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200 product-item" data-product-id="{{ $product->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ $product->name }}</span>
                            <span class="badge-warning text-xs px-2 py-1 rounded-full"><x-lang key="mobile.returns.price"/>: ¥{{ number_format($product->price, 2) }}</span>
                        </div>
                        <input type="hidden" name="products[{{ $loop->index }}][id]" value="{{ $product->id }}">
                        <input type="hidden" name="products[{{ $loop->index }}][unit_price]" value="{{ $product->price }}">
                        <input type="hidden" name="products[{{ $loop->index }}][cost_price]" value="{{ $product->cost_price }}">
                        <input type="number" 
                            name="products[{{ $loop->index }}][quantity]"
                            x-model="formData.products['{{ $product->id }}']?.quantity"
                            @input="updateQuantity('{{ $product->id }}', $event.target.value)"
                            class="form-input w-full px-3 py-2 rounded-lg border text-center text-lg font-semibold" 
                            placeholder="0" min="0" step="1">
                        <p class="text-xs text-gray-500 mt-1 text-center"><x-lang key="mobile.returns.return_quantity"/></p>
                    </div>
                @endforeach
            </div>
            <!-- 无商品提示 -->
            <div id="no-products-message" class="text-center py-8 text-gray-500" style="display: none;">
                <i class="bi bi-box text-4xl mb-2"></i>
                <p>该仓库暂无商品</p>
            </div>
            <!-- 退货统?-->
            <div class="bg-red-50 rounded-lg p-4 mt-4">
                <h4 class="text-md font-semibold text-red-900 mb-3"><x-lang key="mobile.returns.return_stats"/></h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="mobile.returns.return_quantity"/></p>
                        <p class="text-lg font-bold text-red-700" x-text="totalQuantity + ' <x-lang key="mobile.returns.pieces"/>'"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="mobile.returns.return_amount"/></p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalAmount.toFixed(2)"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="mobile.returns.cost_loss"/></p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalCost.toFixed(2)"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 提交按钮 -->
        <div class="card p-6">
            <button type="submit" class="btn-warning w-full py-4 text-white font-semibold rounded-lg shadow-lg">
                <i class="bi bi-arrow-return-left mr-2"></i>
                <x-lang key="mobile.returns.confirm_return"/>
            </button>
        </div>
    </form>

    <!-- 最近退货记�?-->
    @if(isset($recentRecords) && $recentRecords->count() > 0)
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 <x-lang key="mobile.returns.recent_records"/></h2>
            <div class="space-y-3">
                @foreach($recentRecords as $record)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-sm font-medium text-gray-700">
                                {{ date('m-d H:i', strtotime($record->created_at)) }}
                            </span>
                            <span class="badge-warning text-xs px-2 py-1 rounded-full">
                                ¥{{ number_format($record->total_amount, 2) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @foreach($record->returnDetails as $detail)
                                <span class="text-xs bg-white px-2 py-1 rounded border">
                                    {{ $detail->product->name ?? __('mobile.returns.unknown_product') }} × {{ $detail->quantity }}
                                </span>
                            @endforeach
                        </div>
                        @if($record->customer)
                            <p class="text-xs text-gray-500 mt-1"><x-lang key="mobile.returns.customer"/>: {{ $record->customer }}</p>
                        @endif
                        @if($record->remark)
                            <p class="text-xs text-gray-500 mt-1"><x-lang key="mobile.returns.reason"/>: {{ $record->remark }}</p>
                        @endif
                        <div class="mt-2 flex justify-end space-x-2">
                            <a href="{{ route('mobile.returns.edit', $record->id) }}" class="inline-flex items-center px-3 py-1 bg-yellow-400 text-white text-xs font-semibold rounded shadow hover:bg-yellow-500">
                                <i class="bi bi-pencil mr-1"></i> <x-lang key="mobile.returns.edit"/>
                            </a>
                            @if($record->canDelete())
                                <form action="{{ route('mobile.returns.destroy', $record->id) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('<x-lang key="mobile.returns.confirm_delete"/>')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded shadow hover:bg-red-600">
                                        <i class="bi bi-trash mr-1"></i> <x-lang key="mobile.returns.delete"/>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <div class="h-24"></div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('returnForm', () => ({
        formData: {
            products: {}
        },
        get totalQuantity() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
        },
        get totalAmount() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.price) || 0)), 0);
        },
        get totalCost() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.cost_price) || 0)), 0);
        },
        updateQuantity(id, quantity) {
            if (!this.formData.products[id]) {
                this.formData.products[id] = { quantity: 0, price: 0, cost_price: 0 };
            }
            this.formData.products[id].quantity = quantity;
        },
        init() {
            // 初始化所有商品的价格和成本
            @foreach($products as $product)
                this.formData.products['{{ $product->id }}'] = {
                    quantity: 0,
                    price: {{ $product->price }},
                    cost_price: {{ $product->cost_price }}
                };
            @endforeach
            
            // 如果有默认仓库，自动加载商品
            const defaultStoreId = '{{ $storeId }}';
            if (defaultStoreId && defaultStoreId !== '') {
                this.loadStoreProducts(defaultStoreId);
            }
        },
        loadStoreProducts(storeId) {
            const productsContainer = document.getElementById('products-container');
            const noProductsMessage = document.getElementById('no-products-message');

            if (storeId) {
                // 清空现有商品
                this.formData.products = {};
                productsContainer.innerHTML = '';
                noProductsMessage.style.display = 'none';

                // 显示加载状态
                productsContainer.innerHTML = '<div class="col-span-2 text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500 mx-auto"></div><p class="mt-2 text-gray-500">加载商品中...</p></div>';

                // AJAX调用获取商品
                fetch(`{{ route('mobile.returns.store-products') }}?store_id=${storeId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.products && data.products.length > 0) {
                            // 清空加载状态
                            productsContainer.innerHTML = '';
                            
                            // 渲染商品
                            data.products.forEach((product, index) => {
                                const productHtml = `
                                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200 product-item" data-product-id="${product.id}">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">${product.name}</span>
                                            <span class="badge-warning text-xs px-2 py-1 rounded-full">价格: ¥${parseFloat(product.price).toFixed(2)}</span>
                                        </div>
                                        <input type="hidden" name="products[${index}][id]" value="${product.id}">
                                        <input type="hidden" name="products[${index}][unit_price]" value="${product.price}">
                                        <input type="hidden" name="products[${index}][cost_price]" value="${product.cost_price}">
                                        <input type="number" 
                                            name="products[${index}][quantity]"
                                            class="form-input w-full px-3 py-2 rounded-lg border text-center text-lg font-semibold product-quantity" 
                                            data-product-id="${product.id}"
                                            placeholder="0" min="0" step="1">
                                        <p class="text-xs text-gray-500 mt-1 text-center">退货数量</p>
                                    </div>
                                `;
                                productsContainer.innerHTML += productHtml;
                                
                                // 初始化商品数据
                                this.formData.products[product.id] = {
                                    quantity: 0,
                                    price: parseFloat(product.price),
                                    cost_price: parseFloat(product.cost_price)
                                };
                            });
                            
                            // 为动态生成的输入框添加事件监听
                            productsContainer.querySelectorAll('.product-quantity').forEach(input => {
                                input.addEventListener('input', (e) => {
                                    const productId = e.target.dataset.productId;
                                    const quantity = e.target.value;
                                    this.updateQuantity(productId, quantity);
                                });
                            });
                        } else {
                            // 显示无商品提示
                            productsContainer.innerHTML = '';
                            noProductsMessage.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('加载商品失败:', error);
                        productsContainer.innerHTML = '<div class="col-span-2 text-center py-8 text-red-500"><p>加载商品失败，请重试</p></div>';
                    });
            } else {
                // 清空商品
                this.formData.products = {};
                productsContainer.innerHTML = '';
                noProductsMessage.style.display = 'block';
            }
        }
    }));
});
</script>
@endpush

<style>
.badge-warning {
    background: rgba(217, 119, 6, 0.1);
    color: #D97706;
}

.btn-warning {
    background: linear-gradient(135deg, #F59E0B, #D97706);
    transition: all 0.2s ease;
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}
</style>
@endsection 
