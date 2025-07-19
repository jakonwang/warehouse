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

    <form action="{{ route('mobile.sales.store') }}" method="POST" class="space-y-6" x-data="saleForm()">
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
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.sales.sales_certificate"/></label>
                    <input type="file" name="image" accept="image/*" class="form-input w-full px-3 py-2 rounded-lg border">
                    <div class="flex space-x-2 mt-2">
                        <button type="button" onclick="document.querySelector('input[name=image]').click()" class="flex-1 text-center py-2 bg-blue-100 text-blue-700 rounded-lg">
                            <i class="bi bi-images mr-1"></i>从相册选择
                        </button>
                        <button type="button" onclick="document.querySelector('input[name=image]').setAttribute('capture', 'environment'); document.querySelector('input[name=image]').click()" class="flex-1 text-center py-2 bg-green-100 text-green-700 rounded-lg">
                            <i class="bi bi-camera mr-1"></i>拍照上传
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500"><x-lang key="mobile.sales.certificate_desc"/></p>
                </div>
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
