@extends('layouts.app')

@section('title', '编辑销售记录')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">编辑销售记录</h1>
                    <p class="mt-2 text-gray-600">修改销售记录信息</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <i class="bi bi-eye mr-2"></i>查看详情
                    </a>
                    <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <i class="bi bi-arrow-left mr-2"></i>返回列表
                    </a>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('sales.update', $sale) }}" method="POST" enctype="multipart/form-data" 
              x-data="{
                  saleType: @js($sale->sale_type),
                  products: @js($products),
                  saleDetails: @js($sale->saleDetails->keyBy('product_id')),
                  blindBagDeliveries: @js($sale->blindBagDeliveries->keyBy('delivery_product_id')),
                  selectedProducts: {},
                  blindBagDelivery: {},
                  
                  init() {
                      // 初始化标品数据
                      if (this.saleType === 'standard') {
                          Object.entries(this.saleDetails).forEach(([productId, detail]) => {
                              this.selectedProducts[productId] = { 
                                  id: productId, 
                                  quantity: detail.quantity 
                              };
                          });
                      }
                      
                      // 初始化盲袋发货数据
                      if (this.saleType === 'blind_bag') {
                          Object.entries(this.blindBagDeliveries).forEach(([productId, delivery]) => {
                              this.blindBagDelivery[productId] = { 
                                  productId: productId, 
                                  quantity: delivery.quantity 
                              };
                          });
                      }
                  },
                  
                  // 标品销售计算
                  get standardTotalQuantity() {
                      return Object.values(this.selectedProducts).reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                  },
                  
                  get standardTotalAmount() {
                      return Object.values(this.selectedProducts).reduce((sum, item) => {
                          const product = this.products.find(p => p.id == item.id);
                          return sum + ((parseInt(item.quantity) || 0) * (product?.price || 0));
                      }, 0);
                  },
                  
                  get standardTotalCost() {
                      return Object.values(this.selectedProducts).reduce((sum, item) => {
                          const product = this.products.find(p => p.id == item.id);
                          return sum + ((parseInt(item.quantity) || 0) * (product?.cost_price || 0));
                      }, 0);
                  },
                  
                  get standardTotalProfit() {
                      return this.standardTotalAmount - this.standardTotalCost;
                  },
                  
                  get standardProfitRate() {
                      return this.standardTotalAmount > 0 ? (this.standardTotalProfit / this.standardTotalAmount) * 100 : 0;
                  },
                  
                  // 盲袋发货计算
                  get blindBagTotalCost() {
                      return Object.values(this.blindBagDelivery).reduce((sum, item) => {
                          const product = this.products.find(p => p.id == item.productId);
                          return sum + ((parseInt(item.quantity) || 0) * (product?.cost_price || 0));
                      }, 0);
                  },
                  
                  get blindBagTotalProfit() {
                      return {{ $sale->total_amount }} - this.blindBagTotalCost;
                  },
                  
                  get blindBagProfitRate() {
                      return {{ $sale->total_amount }} > 0 ? (this.blindBagTotalProfit / {{ $sale->total_amount }}) * 100 : 0;
                  },
                  
                  updateStandardQuantity(productId, quantity) {
                      quantity = parseInt(quantity) || 0;
                      if (quantity > 0) {
                          this.selectedProducts[productId] = { id: productId, quantity: quantity };
                      } else {
                          delete this.selectedProducts[productId];
                      }
                  },
                  
                  updateBlindBagDelivery(productId, quantity) {
                      quantity = parseInt(quantity) || 0;
                      if (quantity > 0) {
                          this.blindBagDelivery[productId] = { productId: productId, quantity: quantity };
                      } else {
                          delete this.blindBagDelivery[productId];
                      }
                  }
              }">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- 主要内容区域 -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- 销售模式显示 -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">销售模式</h3>
                            <p class="text-sm text-gray-600">当前销售记录的模式（不可更改）</p>
                        </div>

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
                                rounded-xl flex items-center justify-center mr-4 shadow">
                                <i class="
                                    @if($sale->sale_type === 'standard')
                                        bi bi-box
                                    @else
                                        bi bi-gift
                                    @endif
                                    text-white text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-xl text-gray-900">
                                    @if($sale->sale_type === 'standard')
                                        标品销售
                                    @else
                                        盲袋销售
                                    @endif
                                </h4>
                                <p class="text-gray-600 text-base mt-1">
                                    @if($sale->sale_type === 'standard')
                                        直接销售固定价格的商品
                                    @else
                                        销售盲袋，由主播决定发货内容
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 标品销售编辑界面 -->
                    @if($sale->sale_type === 'standard')
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">标品商品</h3>
                            <p class="text-sm text-gray-600">修改商品数量，设置为0则从销售记录中移除</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($products->where('type', 'standard') as $product)
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover">
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                                <span class="text-white font-bold text-sm">{{ substr($product->code ?? 'P', -2) }}</span>
                                            </div>
                                        @endif
                                        <div class="ml-3">
                                            <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                                            <p class="text-sm text-gray-500">¥{{ number_format($product->price, 2) }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        标品
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">销售数量</label>
                                    <input type="number" 
                                           name="products[{{ $product->id }}][quantity]"
                                           min="0"
                                           value="{{ $sale->saleDetails->where('product_id', $product->id)->first()->quantity ?? 0 }}"
                                           @input="updateStandardQuantity({{ $product->id }}, $event.target.value)"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <input type="hidden" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- 盲袋销售编辑界面 -->
                    @if($sale->sale_type === 'blind_bag')
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">盲袋销售信息</h3>
                            <p class="text-sm text-gray-600">盲袋销售金额：¥{{ number_format($sale->total_amount, 2) }}（不可修改）</p>
                        </div>

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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($products->where('type', 'standard') as $product)
                                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover">
                                            @else
                                                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                                                    <span class="text-white font-bold text-sm">{{ substr($product->code ?? 'P', -2) }}</span>
                                                </div>
                                            @endif
                                            <div class="ml-3">
                                                <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                                                <p class="text-sm text-gray-500">成本：¥{{ number_format($product->cost_price, 2) }}</p>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            发货商品
                                        </span>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">发货数量</label>
                                        <input type="number"
                                               name="blind_bag_delivery[{{ $product->id }}][quantity]"
                                               min="0"
                                               value="{{ $sale->blindBagDeliveries->where('delivery_product_id', $product->id)->first()->quantity ?? 0 }}"
                                               @input="updateBlindBagDelivery({{ $product->id }}, $event.target.value)"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <input type="hidden" name="blind_bag_delivery[{{ $product->id }}][product_id]" value="{{ $product->id }}">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 客户信息 -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">客户信息</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">客户姓名</label>
                                <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $sale->customer_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('customer_name') border-red-500 @enderror">
                                @error('customer_name')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">客户电话</label>
                                <input type="text" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $sale->customer_phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('customer_phone') border-red-500 @enderror">
                                @error('customer_phone')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label for="remark" class="block text-sm font-medium text-gray-700 mb-2">备注</label>
                                <textarea id="remark" name="remark" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('remark') border-red-500 @enderror">{{ old('remark', $sale->remark) }}</textarea>
                                @error('remark')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 销售凭证 -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">销售凭证</h3>
                        </div>
                        
                        @if($sale->image_path)
                            <div class="mb-4">
                                <img src="{{ asset('storage/' . $sale->image_path) }}" alt="销售凭证" class="w-full max-h-60 object-contain rounded-lg">
                            </div>
                        @endif
                        
                        <input type="file" id="image" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('image') border-red-500 @enderror">
                        @error('image')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">支持 JPG、PNG、GIF 格式，最大 2MB</p>
                    </div>

                    <!-- 操作按钮 -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('sales.show', $sale) }}" class="px-6 py-3 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                            取消
                        </a>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 border border-transparent rounded-lg font-medium text-white hover:from-blue-600 hover:to-purple-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="bi bi-check-circle mr-2"></i>保存修改
                        </button>
                    </div>
                </div>

                <!-- 侧边栏统计 -->
                <div class="space-y-6">
                    <!-- 标品销售统计 -->
                    @if($sale->sale_type === 'standard')
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">标品销售统计</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">销售数量</span>
                                <span class="text-lg font-bold text-green-600" x-text="standardTotalQuantity + ' 件'"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">销售金额</span>
                                <span class="text-lg font-bold text-blue-600" x-text="'¥' + standardTotalAmount.toFixed(2)"></span>
                            </div>
                            @if(auth()->user()->canViewProfitAndCost())
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">销售成本</span>
                                <span class="text-lg font-bold text-orange-600" x-text="'¥' + standardTotalCost.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">销售利润</span>
                                <span class="text-lg font-bold text-purple-600" x-text="'¥' + standardTotalProfit.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">利润率</span>
                                <span class="text-lg font-bold text-yellow-600" x-text="standardProfitRate.toFixed(2) + '%'"></span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- 盲袋销售统计 -->
                    @if($sale->sale_type === 'blind_bag')
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">盲袋销售统计</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">销售收入</span>
                                <span class="text-lg font-bold text-blue-600">¥{{ number_format($sale->total_amount, 2) }}</span>
                            </div>
                            @if(auth()->user()->canViewProfitAndCost())
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">发货成本</span>
                                <span class="text-lg font-bold text-orange-600" x-text="'¥' + blindBagTotalCost.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">销售利润</span>
                                <span class="text-lg font-bold text-purple-600" x-text="'¥' + blindBagTotalProfit.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">利润率</span>
                                <span class="text-lg font-bold text-yellow-600" x-text="blindBagProfitRate.toFixed(2) + '%'"></span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- 编辑说明 -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">编辑说明</h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600">销售模式不可更改</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600">修改数量会自动重新计算总额</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600">可以更新客户信息和销售凭证</p>
                            </div>
                            @if($sale->sale_type === 'blind_bag')
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600">盲袋销售只能修改发货内容</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush 