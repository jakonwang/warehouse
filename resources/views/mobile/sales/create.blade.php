@extends('layouts.mobile')

@section('title', __('mobile.sales.create_title'))

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- 顶部导航 -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('mobile.sales.index') }}" class="text-gray-600">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <h1 class="text-xl font-semibold text-gray-900"><x-lang key="mobile.sales.create_title"/></h1>
            <div class="w-6"></div>
        </div>
        <p class="text-gray-600"><x-lang key="mobile.sales.create_subtitle"/></p>
    </div>

    @if ($errors->any())
        <div class="card p-4 border-l-4 border-red-500 bg-red-50">
            <ul class="text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>�?{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mobile.sales.store') }}" method="POST" class="space-y-6" x-data="saleForm()" enctype="multipart/form-data">
        @csrf

        <!-- 仓库选择 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🏪 <x-lang key="mobile.sales.select_store"/></h2>
            <div class="space-y-4">
                <select name="store_id" x-model="selectedStore" class="form-select w-full px-3 py-2 rounded-lg border" required>
                    <option value=""><x-lang key="messages.stores.please_select"/></option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 客户信息 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">👤 <x-lang key="mobile.sales.customer_info"/></h2>
            <div class="space-y-4">
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.sales.customer_name"/></label>
                    <input type="text" name="customer_name" class="form-input w-full px-3 py-2 rounded-lg border" placeholder="<x-lang key="mobile.sales.customer_name_placeholder"/>">
                </div>
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.sales.customer_phone"/></label>
                    <input type="tel" name="customer_phone" class="form-input w-full px-3 py-2 rounded-lg border" placeholder="<x-lang key="mobile.sales.customer_phone_placeholder"/>">
                </div>
                <div>
                    <label class="form-label block text-sm font-medium mb-2">📷 <x-lang key="mobile.sales.sales_certificate"/></label>
                    
                    <!-- 隐藏的文件输入框 -->
                    <input type="file" name="image" accept="image/*" class="hidden" id="imageInput">
                    
                    <!-- 上传选项按钮 -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <button type="button" onclick="selectFromGallery()" class="flex flex-col items-center justify-center py-4 bg-blue-50 text-blue-700 rounded-lg border border-blue-200 hover:bg-blue-100 transition-all duration-200 transform hover:scale-105">
                            <i class="bi bi-image text-2xl mb-1"></i>
                            <span class="text-sm font-medium">选择相册</span>
                        </button>
                        <button type="button" onclick="takePhoto()" class="flex flex-col items-center justify-center py-4 bg-green-50 text-green-700 rounded-lg border border-green-200 hover:bg-green-100 transition-all duration-200 transform hover:scale-105">
                            <i class="bi bi-camera text-2xl mb-1"></i>
                            <span class="text-sm font-medium">拍照</span>
                        </button>
                    </div>
                    
                    <!-- 图片预览 -->
                    <div id="image-preview" class="mt-2 hidden">
                        <div class="relative inline-block">
                            <img src="" alt="预览图" class="max-w-full h-48 rounded-lg border border-gray-200 object-cover shadow-md">
                            <button type="button" onclick="removeImage()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600 transition-colors duration-200 shadow-lg">
                                ×
                            </button>
                        </div>
                        <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded p-2" id="image-info"></div>
                    </div>
                    
                    <p class="mt-1 text-xs text-gray-500">💡 <x-lang key="mobile.sales.certificate_desc"/></p>
                </div>

                <script>
                    function selectFromGallery() {
                        const input = document.getElementById('imageInput');
                        input.removeAttribute('capture');
                        input.click();
                    }
                    
                    function takePhoto() {
                        const input = document.getElementById('imageInput');
                        input.setAttribute('capture', 'environment');
                        input.click();
                    }
                    
                    function removeImage() {
                        const input = document.getElementById('imageInput');
                        const preview = document.getElementById('image-preview');
                        const info = document.getElementById('image-info');
                        
                        input.value = '';
                        preview.classList.add('hidden');
                        info.innerHTML = '';
                    }
                    
                    document.getElementById('imageInput').addEventListener('change', function(e) {
                        if (this.files && this.files[0]) {
                            const file = this.files[0];
                            const reader = new FileReader();
                            
                            reader.onload = function(e) {
                                const preview = document.getElementById('image-preview');
                                const info = document.getElementById('image-info');
                                
                                preview.querySelector('img').src = e.target.result;
                                preview.classList.remove('hidden');
                                
                                // 显示文件信息
                                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                                info.innerHTML = `文件名: ${file.name}<br>大小: ${fileSize} MB<br>类型: ${file.type}`;
                            }
                            
                            reader.readAsDataURL(file);
                        }
                    });
                </script>
            </div>
        </div>

        <!-- 销售模式选择 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 <x-lang key="mobile.sales.sales_mode"/></h2>
            <div class="space-y-4">
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="sales_mode" value="standard" x-model="salesMode" class="mr-2">
                        <span class="text-sm"><x-lang key="mobile.sales.standard_sales"/></span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="sales_mode" value="blind_bag" x-model="salesMode" class="mr-2">
                        <span class="text-sm"><x-lang key="mobile.sales.blind_bag_sales"/></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- 标品销售模�?-->
        <div x-show="salesMode === 'standard'" class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🛍�?<x-lang key="mobile.sales.standard_products"/></h2>
            <div class="space-y-4">
                @foreach($standardProducts as $product)
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="text-lg font-bold text-gray-900">{{ $product->name }}</span>
                            <p class="text-xs text-gray-500"><x-lang key="mobile.sales.price"/>: ¥{{ $product->price }}@if(auth()->user()->canViewProfitAndCost()) | <x-lang key="mobile.sales.cost"/>: ¥{{ $product->cost_price }}@endif</p>
                        </div>
                        <span class="text-sm text-gray-500"><x-lang key="mobile.sales.stock"/>: {{ $product->getStockQuantity() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-center space-x-3">
                        <button type="button" 
                                class="quantity-btn minus w-10 h-10 flex items-center justify-center bg-red-100 rounded-full text-red-600 hover:bg-red-200 transition-colors"
                                @click="decreaseQuantity('standard_{{ $product->id }}')">
                            <i class="bi bi-dash text-lg"></i>
                        </button>
                        
                        <div class="text-center">
                            <input type="number" 
                                   name="standard_products[{{ $loop->index }}][quantity]" 
                                   x-model="standardProducts[{{ $loop->index }}].quantity"
                                   class="quantity-input w-16 text-center text-xl font-bold bg-white border-2 border-gray-300 rounded-lg py-2 focus:border-blue-500 focus:outline-none" 
                                   min="0" step="1">
                            <p class="text-xs text-gray-500 mt-1"><x-lang key="mobile.sales.quantity"/></p>
                        </div>
                        
                        <button type="button" 
                                class="quantity-btn plus w-10 h-10 flex items-center justify-center bg-green-100 rounded-full text-green-600 hover:bg-green-200 transition-colors"
                                @click="increaseQuantity('standard_{{ $product->id }}')">
                            <i class="bi bi-plus text-lg"></i>
                        </button>
                    </div>
                    
                    <input type="hidden" name="standard_products[{{ $loop->index }}][id]" value="{{ $product->id }}">
                    
                    <!-- 小计显示 -->
                    <div class="mt-3 pt-3 border-t border-gray-200 text-center">
                        <span class="text-sm text-gray-600"><x-lang key="mobile.sales.subtotal"/>: </span>
                        <span class="text-sm font-bold text-indigo-600">¥<span x-text="(standardProducts[{{ $loop->index }}].quantity * {{ $product->price }}).toFixed(2)"></span></span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 盲袋销售模�?-->
        <div x-show="salesMode === 'blind_bag'" class="space-y-6">
            <!-- 盲袋商品选择 -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">🎁 <x-lang key="mobile.sales.blind_bag_products"/></h2>
                <div class="space-y-4">
                    @foreach($blindBagProducts as $product)
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <span class="text-lg font-bold text-gray-900">{{ $product->name }}</span>
                                <p class="text-xs text-gray-500"><x-lang key="mobile.sales.price"/>: ¥{{ $product->price }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-center space-x-3">
                            <button type="button" 
                                    class="quantity-btn minus w-10 h-10 flex items-center justify-center bg-red-100 rounded-full text-red-600 hover:bg-red-200 transition-colors"
                                    @click="decreaseQuantity('blind_{{ $product->id }}')">
                                <i class="bi bi-dash text-lg"></i>
                            </button>
                            
                            <div class="text-center">
                                <input type="number" 
                                       name="blind_bag_products[{{ $loop->index }}][quantity]" 
                                       x-model="blindBagProducts[{{ $loop->index }}].quantity"
                                       class="quantity-input w-16 text-center text-xl font-bold bg-white border-2 border-gray-300 rounded-lg py-2 focus:border-purple-500 focus:outline-none" 
                                       min="0" step="1">
                                <p class="text-xs text-gray-500 mt-1"><x-lang key="mobile.sales.quantity"/></p>
                            </div>
                            
                            <button type="button" 
                                    class="quantity-btn plus w-10 h-10 flex items-center justify-center bg-green-100 rounded-full text-green-600 hover:bg-green-200 transition-colors"
                                    @click="increaseQuantity('blind_{{ $product->id }}')">
                                <i class="bi bi-plus text-lg"></i>
                            </button>
                        </div>
                        
                        <input type="hidden" name="blind_bag_products[{{ $loop->index }}][id]" value="{{ $product->id }}">
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 盲袋发货内容 -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">📦 <x-lang key="mobile.sales.delivery_content_title"/></h2>
                <p class="text-sm text-gray-600 mb-4"><x-lang key="mobile.sales.delivery_content_desc"/></p>
                <div class="space-y-4">
                    @foreach($standardProducts as $product)
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <span class="text-lg font-bold text-gray-900">{{ $product->name }}</span>
                                <p class="text-xs text-gray-500">@if(auth()->user()->canViewProfitAndCost())<x-lang key="mobile.sales.cost"/>: ¥{{ $product->cost_price }} | @endif<x-lang key="mobile.sales.stock"/>: {{ $product->getStockQuantity() }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-center space-x-3">
                            <button type="button" 
                                    class="quantity-btn minus w-10 h-10 flex items-center justify-center bg-red-100 rounded-full text-red-600 hover:bg-red-200 transition-colors"
                                    @click="decreaseQuantity('delivery_{{ $product->id }}')">
                                <i class="bi bi-dash text-lg"></i>
                            </button>
                            
                            <div class="text-center">
                                <input type="number" 
                                       name="blind_bag_delivery[{{ $loop->index }}][quantity]" 
                                       x-model="deliveryProducts[{{ $loop->index }}].quantity"
                                       class="quantity-input w-16 text-center text-xl font-bold bg-white border-2 border-gray-300 rounded-lg py-2 focus:border-green-500 focus:outline-none" 
                                       min="0" step="1">
                                <p class="text-xs text-gray-500 mt-1"><x-lang key="mobile.sales.quantity"/></p>
                            </div>
                            
                            <button type="button" 
                                    class="quantity-btn plus w-10 h-10 flex items-center justify-center bg-green-100 rounded-full text-green-600 hover:bg-green-200 transition-colors"
                                    @click="increaseQuantity('delivery_{{ $product->id }}')">
                                <i class="bi bi-plus text-lg"></i>
                            </button>
                        </div>
                        
                        <input type="hidden" name="blind_bag_delivery[{{ $loop->index }}][product_id]" value="{{ $product->id }}">
                        
                        <!-- 小计显示 -->
                        <div class="mt-3 pt-3 border-t border-gray-200 text-center">
                            <span class="text-sm text-gray-600"><x-lang key="mobile.sales.cost_subtotal"/>: </span>
                            <span class="text-sm font-bold text-green-600">¥<span x-text="(deliveryProducts[{{ $loop->index }}].quantity * {{ $product->cost_price }}).toFixed(2)"></span></span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 销售汇�?-->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📊 <x-lang key="mobile.sales.sales_summary"/></h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="text-lg font-bold text-blue-600">¥<span x-text="totalRevenue.toFixed(2)"></span></div>
                    <p class="text-xs text-gray-500"><x-lang key="mobile.sales.sales_amount"/></p>
                </div>
                @if(auth()->user()->canViewProfitAndCost())
                <div class="text-center p-3 bg-red-50 rounded-lg border border-red-200">
                    <div class="text-lg font-bold text-red-600">¥<span x-text="totalCost.toFixed(2)"></span></div>
                    <p class="text-xs text-gray-500"><x-lang key="mobile.sales.total_cost"/></p>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="text-lg font-bold text-green-600">¥<span x-text="totalProfit.toFixed(2)"></span></div>
                    <p class="text-xs text-gray-500"><x-lang key="mobile.sales.total_profit"/></p>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="text-lg font-bold text-purple-600"><span x-text="profitRate.toFixed(1)"></span>%</div>
                    <p class="text-xs text-gray-500"><x-lang key="mobile.sales.profit_rate"/></p>
                </div>
                @endif
            </div>
        </div>

        <!-- 提交按钮 -->
        <div class="card p-6">
            <button type="submit" class="btn-primary w-full py-4 text-white font-semibold rounded-lg shadow-lg">
                <i class="bi bi-check-circle mr-2"></i>
                <x-lang key="mobile.sales.save_sales_record"/>
            </button>
        </div>
    </form>
    <div class="h-24"></div>
</div>

<script>
function saleForm() {
    return {
        selectedStore: '',
        salesMode: 'standard',
        standardProducts: @json($standardProductsArr),
        blindBagProducts: @json($blindBagProductsArr),
        deliveryProducts: @json($deliveryProductsArr),
        
        get totalRevenue() {
            if (this.salesMode === 'standard') {
                return this.standardProducts.reduce((sum, product) => sum + (product.quantity * product.price), 0);
            } else {
                return this.blindBagProducts.reduce((sum, product) => sum + (product.quantity * product.price), 0);
            }
        },
        
        get totalCost() {
            if (this.salesMode === 'standard') {
                return this.standardProducts.reduce((sum, product) => sum + (product.quantity * product.cost), 0);
            } else {
                return this.deliveryProducts.reduce((sum, product) => sum + (product.quantity * product.cost), 0);
            }
        },
        
        get totalProfit() {
            return this.totalRevenue - this.totalCost;
        },
        
        get profitRate() {
            return this.totalRevenue > 0 ? (this.totalProfit / this.totalRevenue) * 100 : 0;
        },
        
        increaseQuantity(type) {
            const [category, id] = type.split('_');
            if (category === 'standard') {
                const product = this.standardProducts.find(p => p.id == id);
                if (product) product.quantity++;
            } else if (category === 'blind') {
                const product = this.blindBagProducts.find(p => p.id == id);
                if (product) product.quantity++;
            } else if (category === 'delivery') {
                const product = this.deliveryProducts.find(p => p.id == id);
                if (product) product.quantity++;
            }
        },
        
        decreaseQuantity(type) {
            const [category, id] = type.split('_');
            if (category === 'standard') {
                const product = this.standardProducts.find(p => p.id == id);
                if (product && product.quantity > 0) product.quantity--;
            } else if (category === 'blind') {
                const product = this.blindBagProducts.find(p => p.id == id);
                if (product && product.quantity > 0) product.quantity--;
            } else if (category === 'delivery') {
                const product = this.deliveryProducts.find(p => p.id == id);
                if (product && product.quantity > 0) product.quantity--;
            }
        }
    }
}
</script>
@endsection 
