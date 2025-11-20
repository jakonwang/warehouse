@extends('layouts.mobile')

@section('title', '编辑销售记录')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- 顶部导航 -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('mobile.sales.show', $sale) }}" class="text-gray-600">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <h1 class="text-xl font-semibold text-gray-900">编辑销售记录</h1>
            <div class="w-6"></div>
        </div>
        <p class="text-gray-600">订单 #{{ $sale->id }}</p>
    </div>

    @if ($errors->any())
        <div class="card p-4 border-l-4 border-red-500 bg-red-50">
            <ul class="text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mobile.sales.update', $sale) }}" method="POST" enctype="multipart/form-data" 
          x-data="editSaleForm()">
        @csrf
        @method('PUT')

        <!-- 销售模式显示 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 销售模式</h2>
            <div class="flex items-center p-4 rounded-lg border-2 
                @if($sale->sale_type === 'standard')
                    border-blue-500 bg-blue-50
                @else
                    border-purple-500 bg-purple-50
                @endif">
                <div class="w-12 h-12 
                    @if($sale->sale_type === 'standard')
                        bg-blue-500
                    @else
                        bg-purple-500
                    @endif
                    rounded-xl flex items-center justify-center mr-4">
                    <i class="
                        @if($sale->sale_type === 'standard')
                            bi bi-box
                        @else
                            bi bi-gift
                        @endif
                        text-white text-2xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-lg text-gray-900">
                        @if($sale->sale_type === 'standard')
                            标品销售
                        @else
                            盲袋销售
                        @endif
                    </h4>
                    <p class="text-gray-600 text-sm">
                        @if($sale->sale_type === 'standard')
                            直接销售固定价格的商品
                        @else
                            销售盲袋，由主播决定发货内容
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- 标品销售编辑 -->
        @if($sale->sale_type === 'standard')
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🛍️ 标品商品</h2>
            <p class="text-sm text-gray-600 mb-4">修改商品数量，设置为0则从销售记录中移除</p>
            <div class="space-y-4">
                @foreach($standardProducts as $product)
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                            <p class="text-sm text-gray-500">¥{{ number_format($product->price, 2) }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            标品
                        </span>
                    </div>

                    <div class="flex items-center justify-center space-x-3">
                        <button type="button" 
                                class="w-10 h-10 flex items-center justify-center bg-red-100 rounded-full text-red-600"
                                @click="decreaseQuantity('product_{{ $product->id }}')">
                            <i class="bi bi-dash text-lg"></i>
                        </button>
                        
                        <div class="text-center">
                            <input type="number" 
                                   name="standard_products[{{ $product->id }}][quantity]"
                                   x-model="quantities.product_{{ $product->id }}"
                                   value="{{ $sale->saleDetails->where('product_id', $product->id)->first()->quantity ?? 0 }}"
                                   class="w-16 text-center text-xl font-bold bg-white border-2 border-gray-300 rounded-lg py-2" 
                                   min="0">
                            <p class="text-xs text-gray-500 mt-1">数量</p>
                        </div>
                        
                        <button type="button" 
                                class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-full text-green-600"
                                @click="increaseQuantity('product_{{ $product->id }}')">
                            <i class="bi bi-plus text-lg"></i>
                        </button>
                    </div>
                    
                    <input type="hidden" name="standard_products[{{ $product->id }}][id]" value="{{ $product->id }}">
                    
                    <!-- 小计显示 -->
                    <div class="mt-3 pt-3 border-t border-gray-200 text-center">
                        <span class="text-sm text-gray-600">小计: </span>
                        <span class="text-sm font-bold text-blue-600">¥<span x-text="((quantities.product_{{ $product->id }} || 0) * {{ $product->price }}).toFixed(2)"></span></span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 盲袋销售编辑 -->
        @if($sale->sale_type === 'blind_bag')
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🎁 盲袋销售信息</h2>
            <p class="text-sm text-gray-600 mb-4">盲袋销售金额：¥{{ number_format($sale->total_amount, 2) }}（不可修改）</p>

            <!-- 显示已售盲袋商品 -->
            <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">已售盲袋商品</h4>
                @foreach($sale->saleDetails as $detail)
                    @if($detail->product && $detail->product->type === 'blind_bag')
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ $detail->product->name }}</span>
                        <span class="text-sm font-medium text-gray-900">{{ $detail->quantity }}个 × ¥{{ number_format($detail->price, 2) }} = ¥{{ number_format($detail->total, 2) }}</span>
                    </div>
                    @endif
                @endforeach
            </div>

            <!-- 编辑发货内容 -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-4">修改实际发货内容</h4>
                <div class="space-y-4">
                    @foreach($standardProducts as $product)
                    <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                                <p class="text-sm text-gray-500">成本：¥{{ number_format($product->cost_price, 2) }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                发货商品
                            </span>
                        </div>

                        <div class="flex items-center justify-center space-x-3">
                            <button type="button" 
                                    class="w-10 h-10 flex items-center justify-center bg-red-100 rounded-full text-red-600"
                                    @click="decreaseQuantity('delivery_{{ $product->id }}')">
                                <i class="bi bi-dash text-lg"></i>
                            </button>
                            
                            <div class="text-center">
                                <input type="number"
                                       name="blind_bag_delivery[{{ $product->id }}][quantity]"
                                       x-model="quantities.delivery_{{ $product->id }}"
                                       value="{{ $sale->blindBagDeliveries->where('delivery_product_id', $product->id)->first()->quantity ?? 0 }}"
                                       class="w-16 text-center text-xl font-bold bg-white border-2 border-gray-300 rounded-lg py-2" 
                                       min="0">
                                <p class="text-xs text-gray-500 mt-1">数量</p>
                            </div>
                            
                            <button type="button" 
                                    class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-full text-green-600"
                                    @click="increaseQuantity('delivery_{{ $product->id }}')">
                                <i class="bi bi-plus text-lg"></i>
                            </button>
                        </div>
                        
                        <input type="hidden" name="blind_bag_delivery[{{ $product->id }}][product_id]" value="{{ $product->id }}">
                        
                        <!-- 成本小计显示 -->
                        <div class="mt-3 pt-3 border-t border-gray-200 text-center">
                            <span class="text-sm text-gray-600">成本小计: </span>
                            <span class="text-sm font-bold text-green-600">¥<span x-text="((quantities.delivery_{{ $product->id }} || 0) * {{ $product->cost_price }}).toFixed(2)"></span></span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- 客户信息 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">👤 客户信息</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">客户姓名</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', $sale->customer_name) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="请输入客户姓名">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">客户电话</label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone', $sale->customer_phone) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="请输入客户电话">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">备注</label>
                    <textarea name="remark" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                              placeholder="请输入备注信息">{{ old('remark', $sale->remark) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 销售凭证 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📷 销售凭证</h2>
            
            @if($sale->image_path)
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">当前图片：</p>
                    <img src="{{ get_image_url($sale->image_path) }}" alt="销售凭证" 
                         class="w-full max-h-60 object-contain rounded-lg border border-gray-200">
                </div>
            @endif
            
            <input type="file" name="image" accept="image/*" capture="environment" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            <p class="mt-1 text-xs text-gray-500">支持 JPG、PNG、GIF 格式，最大 10MB，或者从相机拍摄</p>
        </div>

        <!-- 统计汇总 -->
        @if($sale->sale_type === 'standard')
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📊 销售统计</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="text-lg font-bold text-blue-600" x-text="'¥' + totalAmount.toFixed(2)">¥0.00</div>
                    <p class="text-xs text-gray-500">销售金额</p>
                </div>
                @if(auth()->user()->canViewProfitAndCost())
                <div class="text-center p-3 bg-orange-50 rounded-lg border border-orange-200">
                    <div class="text-lg font-bold text-orange-600" x-text="'¥' + totalCost.toFixed(2)">¥0.00</div>
                    <p class="text-xs text-gray-500">销售成本</p>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="text-lg font-bold text-green-600" x-text="'¥' + totalProfit.toFixed(2)">¥0.00</div>
                    <p class="text-xs text-gray-500">销售利润</p>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="text-lg font-bold text-purple-600" x-text="profitRate.toFixed(1) + '%'">0.0%</div>
                    <p class="text-xs text-gray-500">利润率</p>
                </div>
                @endif
            </div>
        </div>
        @elseif($sale->sale_type === 'blind_bag')
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📊 盲袋统计</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="text-lg font-bold text-blue-600">¥{{ number_format($sale->total_amount, 2) }}</div>
                    <p class="text-xs text-gray-500">销售收入</p>
                </div>
                @if(auth()->user()->canViewProfitAndCost())
                <div class="text-center p-3 bg-orange-50 rounded-lg border border-orange-200">
                    <div class="text-lg font-bold text-orange-600" x-text="'¥' + deliveryCost.toFixed(2)">¥0.00</div>
                    <p class="text-xs text-gray-500">发货成本</p>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="text-lg font-bold text-green-600" x-text="'¥' + ({{ $sale->total_amount }} - deliveryCost).toFixed(2)">¥0.00</div>
                    <p class="text-xs text-gray-500">销售利润</p>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="text-lg font-bold text-purple-600" x-text="({{ $sale->total_amount }} > 0 ? (({{ $sale->total_amount }} - deliveryCost) / {{ $sale->total_amount }} * 100) : 0).toFixed(1) + '%'">0.0%</div>
                    <p class="text-xs text-gray-500">利润率</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- 提交按钮 -->
        <div class="card p-6">
            <div class="flex space-x-3">
                <a href="{{ route('mobile.sales.show', $sale) }}" 
                   class="flex-1 text-center py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg">
                    取消
                </a>
                <button type="submit" 
                        class="flex-1 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-semibold rounded-lg">
                    <i class="bi bi-check-circle mr-2"></i>
                    保存修改
                </button>
            </div>
        </div>
    </form>
    <div class="h-24"></div>
</div>

<script>
function editSaleForm() {
    return {
        quantities: {},
        
        init() {
            // 初始化所有数量值
            @foreach($standardProducts as $product)
                this.quantities.product_{{ $product->id }} = {{ $sale->saleDetails->where('product_id', $product->id)->first()->quantity ?? 0 }};
                @if($sale->sale_type === 'blind_bag')
                this.quantities.delivery_{{ $product->id }} = {{ $sale->blindBagDeliveries->where('delivery_product_id', $product->id)->first()->quantity ?? 0 }};
                @endif
            @endforeach
        },
        
        increaseQuantity(key) {
            if (!this.quantities[key]) this.quantities[key] = 0;
            this.quantities[key]++;
        },
        
        decreaseQuantity(key) {
            if (!this.quantities[key]) this.quantities[key] = 0;
            if (this.quantities[key] > 0) {
                this.quantities[key]--;
            }
        },
        
        @if($sale->sale_type === 'standard')
        get totalAmount() {
            let total = 0;
            @foreach($standardProducts as $product)
                total += (this.quantities.product_{{ $product->id }} || 0) * {{ $product->price }};
            @endforeach
            return total;
        },
        
        get totalCost() {
            let total = 0;
            @foreach($standardProducts as $product)
                total += (this.quantities.product_{{ $product->id }} || 0) * {{ $product->cost_price }};
            @endforeach
            return total;
        },
        
        get totalProfit() {
            return this.totalAmount - this.totalCost;
        },
        
        get profitRate() {
            return this.totalAmount > 0 ? (this.totalProfit / this.totalAmount) * 100 : 0;
        }
        @else
        get deliveryCost() {
            let total = 0;
            @foreach($standardProducts as $product)
                total += (this.quantities.delivery_{{ $product->id }} || 0) * {{ $product->cost_price }};
            @endforeach
            return total;
        }
        @endif
    };
}
</script>
@endsection 