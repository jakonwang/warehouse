@extends('layouts.app')

@section('title', __('messages.sales.create.title'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900"><x-lang key="messages.sales.create.title"/></h1>
                    <p class="mt-2 text-gray-600"><x-lang key="messages.sales.create.subtitle"/></p>
                </div>
                <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    <i class="bi bi-arrow-left mr-2"></i><x-lang key="messages.sales.create.back_to_list"/>
                </a>
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

        <form action="{{ route('sales.store') }}" method="POST" enctype="multipart/form-data" 
              x-data="{
                  salesMode: 'standard', // standard 或 blind_bag
                  currentStore: @js($currentStore),
                  stores: @js($stores),
                  standardProducts: @js($standardProducts),
                  blindBagProducts: @js($blindBagProducts),
                  selectedProducts: {},
                  blindBagDelivery: {},
                  customerInfo: { name: '', phone: '', remark: '' },
                  
                  // 标品销售计算
                  get standardTotalQuantity() {
                      return Object.values(this.selectedProducts).reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                  },
                  
                  get standardTotalAmount() {
                      return Object.values(this.selectedProducts).reduce((sum, item) => {
                          const product = this.standardProducts.find(p => p.id == item.id);
                          return sum + ((parseInt(item.quantity) || 0) * (product?.price || 0));
                      }, 0);
                  },
                  
                  get standardTotalCost() {
                      return Object.values(this.selectedProducts).reduce((sum, item) => {
                          const product = this.standardProducts.find(p => p.id == item.id);
                          return sum + ((parseInt(item.quantity) || 0) * (product?.cost_price || 0));
                      }, 0);
                  },
                  
                  get standardTotalProfit() {
                      return this.standardTotalAmount - this.standardTotalCost;
                  },
                  
                  get standardProfitRate() {
                      return this.standardTotalAmount > 0 ? (this.standardTotalProfit / this.standardTotalAmount) * 100 : 0;
                  },
                  
                  // 盲袋销售计算
                  get blindBagTotalQuantity() {
                      return Object.values(this.selectedBlindBags).reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                  },
                  
                  get blindBagSalesAmount() {
                      return Object.values(this.selectedBlindBags).reduce((sum, item) => {
                          const product = this.blindBagProducts.find(p => p.id == item.id);
                          return sum + ((parseInt(item.quantity) || 0) * (product?.price || 0));
                      }, 0);
                  },
                  
                  get blindBagTotalCost() {
                      return Object.values(this.blindBagDelivery).reduce((sum, item) => {
                          const product = this.standardProducts.find(p => p.id == item.productId);
                          return sum + ((parseInt(item.quantity) || 0) * (product?.cost_price || 0));
                      }, 0);
                  },
                  
                  get blindBagTotalProfit() {
                      return this.blindBagSalesAmount - this.blindBagTotalCost;
                  },
                  
                  get blindBagProfitRate() {
                      return this.blindBagSalesAmount > 0 ? (this.blindBagTotalProfit / this.blindBagSalesAmount) * 100 : 0;
                  },
                  
                  // 当前选中的盲袋商品（改为数组支持多选）
                  selectedBlindBags: {},
                  
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
                  },
                  
                  updateBlindBagQuantity(productId, quantity) {
                      quantity = parseInt(quantity) || 0;
                      if (quantity > 0) {
                          this.selectedBlindBags[productId] = { id: productId, quantity: quantity };
                      } else {
                          delete this.selectedBlindBags[productId];
                      }
                  },
                  
                  // 仓库切换时重新加载商品
                  async changeStore(storeId) {
                      try {
                          const response = await fetch(`/api/stores/${storeId}/products`);
                          const data = await response.json();
                          
                          if (data.success) {
                              this.standardProducts = data.standard_products;
                              this.blindBagProducts = data.blind_bag_products;
                              this.selectedProducts = {};
                              this.blindBagDelivery = {};
                              this.selectedBlindBags = {};
                          }
                      } catch (error) {
                          console.error('加载商品失败:', error);
                      }
                  }
              }">
            @csrf
            <input type="hidden" name="sales_mode" x-model="salesMode">
            <input type="hidden" name="store_id" x-model="currentStore && currentStore.id">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- 主要内容区域 -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- 仓库选择 -->
                    <div class="w-full max-w-lg mx-auto mt-8 mb-10">
                        <h3 class="text-xl font-bold text-gray-900 mb-1"><x-lang key="messages.sales.create.select_store"/></h3>
                        <p class="text-sm text-gray-500 mb-4"><x-lang key="messages.sales.create.select_store_description"/></p>
                        @if($currentStore)
                            <div class="flex items-center p-5 rounded-2xl border border-green-300 bg-green-50 shadow-sm">
                                <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center mr-4 shadow">
                                    <i class="bi bi-building text-white text-2xl"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-lg text-gray-900">{{ $currentStore->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $currentStore->code }}</div>
                                </div>
                            </div>
                        @else
                            <div class="p-6 rounded-xl border border-gray-200 bg-gray-50 text-gray-400 text-center">
                                <i class="bi bi-exclamation-circle text-2xl mb-2"></i>
                                <div>请先在侧边栏切换具体仓库</div>
                            </div>
                        @endif
                    </div>

                    <!-- 销售模式选择 -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><x-lang key="messages.sales.create.select_sales_mode"/></h3>
                            <p class="text-sm text-gray-600"><x-lang key="messages.sales.create.select_sales_mode_description"/></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="h-full">
                                <input type="radio" name="sales_mode" value="standard" x-model="salesMode" id="mode-standard" class="sr-only">
                                <label for="mode-standard" class="block cursor-pointer h-full">
                                    <div class="flex flex-col justify-center h-full p-8 rounded-2xl border-2 transition-all duration-200
                                        bg-white
                                        hover:border-blue-400 hover:shadow-lg"
                                        :class="salesMode === 'standard' ? 'border-blue-500 bg-blue-50 shadow-xl' : 'border-blue-200'">
                                        <div class="flex items-center mb-4">
                                            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mr-4 shadow">
                                                <i class="bi bi-box text-white text-2xl"></i>
                                            </div>
                                            <h4 class="font-bold text-xl text-gray-900"><x-lang key='messages.sales.create.standard_sales'/></h4>
                                        </div>
                                        <p class="text-gray-600 text-base mt-2"><x-lang key='messages.sales.create.standard_sales_description'/></p>
                                    </div>
                                </label>
                            </div>
                            <div class="h-full">
                                <input type="radio" name="sales_mode" value="blind_bag" x-model="salesMode" id="mode-blind-bag" class="sr-only">
                                <label for="mode-blind-bag" class="block cursor-pointer h-full">
                                    <div class="flex flex-col justify-center h-full p-8 rounded-2xl border-2 transition-all duration-200
                                        bg-white
                                        hover:border-purple-400 hover:shadow-lg"
                                        :class="salesMode === 'blind_bag' ? 'border-purple-500 bg-purple-50 shadow-xl' : 'border-purple-200'">
                                        <div class="flex items-center mb-4">
                                            <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center mr-4 shadow">
                                                <i class="bi bi-gift text-white text-2xl"></i>
                                            </div>
                                            <h4 class="font-bold text-xl text-gray-900"><x-lang key='messages.sales.create.blind_bag_sales'/></h4>
                                        </div>
                                        <p class="text-gray-600 text-base mt-2"><x-lang key='messages.sales.create.blind_bag_sales_description'/></p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 标品销售界面 -->
                    <div x-show="salesMode === 'standard'" class="space-y-6">
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><x-lang key="messages.sales.create.select_standard_products"/></h3>
                                <p class="text-sm text-gray-600"><x-lang key="messages.sales.create.select_standard_products_description"/></p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="product in standardProducts" :key="product.id">
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <template x-if="product.image_url">
                                                    <img :src="product.image_url" :alt="product.name" class="w-10 h-10 rounded-lg object-cover">
                                                </template>
                                                <template x-if="!product.image_url">
                                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                                        <span class="text-white font-bold text-sm" x-text="product.code ? product.code.slice(-2) : 'P'"></span>
                                                    </div>
                                                </template>
                                                <div class="ml-3">
                                                    <h4 class="font-semibold text-gray-900" x-text="product.name"></h4>
                                                    <p class="text-sm text-gray-500" x-text="'¥' + parseFloat(product.price).toFixed(2)"></p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <x-lang key="messages.sales.create.standard_product"/>
                                            </span>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sales.create.sales_quantity"/></label>
                                            <input type="number" 
                                                   :name="'standard_products[' + product.id + '][quantity]'"
                                                   min="0"
                                                   placeholder="0"
                                                   @input="updateStandardQuantity(product.id, $event.target.value)"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <input type="hidden" :name="'standard_products[' + product.id + '][id]'" :value="product.id">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- 盲袋销售界面 -->
                    <div x-show="salesMode === 'blind_bag'" class="space-y-6">
                        <!-- 步骤1：选择盲袋商品 -->
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><x-lang key="messages.sales.create.select_blind_bag_products"/></h3>
                                <p class="text-sm text-gray-600"><x-lang key="messages.sales.create.select_blind_bag_products_description"/></p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="product in blindBagProducts" :key="product.id">
                                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <template x-if="product.image_url">
                                                    <img :src="product.image_url" :alt="product.name" class="w-10 h-10 rounded-lg object-cover">
                                                </template>
                                                <template x-if="!product.image_url">
                                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                                        <span class="text-white font-bold text-sm" x-text="product.code ? product.code.slice(-2) : 'P'"></span>
                                                    </div>
                                                </template>
                                                <div class="ml-3">
                                                    <h4 class="font-semibold text-gray-900" x-text="product.name"></h4>
                                                    <p class="text-sm text-gray-500" x-text="'<x-lang key="messages.sales.create.sales_price"/>' + parseFloat(product.price).toFixed(2)"></p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <x-lang key="messages.sales.create.blind_bag_product"/>
                                            </span>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sales.create.sales_quantity"/></label>
                                            <input type="number" 
                                                   :name="'blind_bag_products[' + product.id + '][quantity]'"
                                                   min="1" 
                                                   placeholder="0"
                                                   @input="updateBlindBagQuantity(product.id, $event.target.value)"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <input type="hidden" :name="'blind_bag_products[' + product.id + '][id]'" :value="product.id">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <!-- 步骤2：主播决定发货内容（全局唯一） -->
                        <div x-show="Object.keys(selectedBlindBags).length > 0" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6 mt-6">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><x-lang key="messages.sales.create.decide_delivery_content"/></h3>
                                <p class="text-sm text-gray-600"><x-lang key="messages.sales.create.decide_delivery_content_description"/></p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="product in standardProducts" :key="product.id">
                                    <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <template x-if="product.image_url">
                                                    <img :src="product.image_url" :alt="product.name" class="w-10 h-10 rounded-lg object-cover">
                                                </template>
                                                <template x-if="!product.image_url">
                                                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                                                        <span class="text-white font-bold text-sm" x-text="product.code ? product.code.slice(-2) : 'P'"></span>
                                                    </div>
                                                </template>
                                                <div class="ml-3">
                                                    <h4 class="font-semibold text-gray-900" x-text="product.name"></h4>
                                                    <p class="text-sm text-gray-500" x-text="'<x-lang key="messages.sales.create.cost_price"/>' + parseFloat(product.cost_price).toFixed(2)"></p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <x-lang key="messages.sales.create.delivery_product"/>
                                            </span>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sales.create.delivery_quantity"/></label>
                                            <input type="number"
                                                   :name="'blind_bag_delivery[' + product.id + '][quantity]'"
                                                   min="0"
                                                   placeholder="0"
                                                   @input="updateBlindBagDelivery(product.id, $event.target.value)"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            <input type="hidden" :name="'blind_bag_delivery[' + product.id + '][product_id]'" :value="product.id">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- 客户信息 -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><x-lang key="messages.sales.create.customer_info"/></h3>
                            <p class="text-sm text-gray-600"><x-lang key="messages.sales.create.customer_info_description"/></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sales.create.customer_name"/></label>
                                <input type="text" name="customer_name" x-model="customerInfo.name" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       placeholder="<x-lang key="messages.sales.create.customer_name_placeholder"/>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sales.create.customer_phone"/></label>
                                <input type="tel" name="customer_phone" x-model="customerInfo.phone" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       placeholder="<x-lang key="messages.sales.create.customer_phone_placeholder"/>">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sales.create.sales_certificate"/></label>
                                <input type="file" name="image" accept="image/*" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="mt-1 text-xs text-gray-500"><x-lang key="messages.sales.create.file_format_hint"/></p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sales.create.remark_info"/></label>
                                <textarea name="remark" x-model="customerInfo.remark" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                          placeholder="<x-lang key="messages.sales.create.remark_placeholder"/>"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 操作按钮 -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('sales.index') }}" class="px-6 py-3 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                            <x-lang key="messages.sales.create.cancel"/>
                        </a>
                        <button type="submit" 
                                :disabled="(salesMode === 'standard' && standardTotalQuantity === 0) || (salesMode === 'blind_bag' && (Object.keys(selectedBlindBags).length === 0 || blindBagTotalQuantity === 0))"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 border border-transparent rounded-lg font-medium text-white hover:from-blue-600 hover:to-purple-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="bi bi-check-circle mr-2"></i><x-lang key="messages.sales.create.submit_sales"/>
                        </button>
                    </div>
                </div>

                <!-- 侧边栏统计 -->
                <div class="space-y-6">
                    <!-- 标品销售统计 -->
                    <div x-show="salesMode === 'standard'" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="messages.sales.create.standard_sales_statistics"/></h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.sales_quantity_label"/></span>
                                <span class="text-lg font-bold text-green-600" x-text="standardTotalQuantity + ' <x-lang key="messages.sales.create.pieces"/>'"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.sales_amount_label"/></span>
                                <span class="text-lg font-bold text-blue-600" x-text="'¥' + standardTotalAmount.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.sales_cost_label"/></span>
                                <span class="text-lg font-bold text-orange-600" x-text="'¥' + standardTotalCost.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.sales_profit_label"/></span>
                                <span class="text-lg font-bold text-purple-600" x-text="'¥' + standardTotalProfit.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.profit_rate_label"/></span>
                                <span class="text-lg font-bold text-yellow-600" x-text="standardProfitRate.toFixed(1) + '%'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- 盲袋销售统计 -->
                    <div x-show="salesMode === 'blind_bag' && Object.keys(selectedBlindBags).length > 0" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="messages.sales.create.blind_bag_sales_statistics"/></h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.blind_bag_sales_label"/></span>
                                <span class="text-lg font-bold text-purple-600" x-text="blindBagTotalQuantity + ' <x-lang key="messages.sales.create.pieces"/>'"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.sales_income_label"/></span>
                                <span class="text-lg font-bold text-blue-600" x-text="'¥' + blindBagSalesAmount.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.delivery_quantity_label"/></span>
                                <span class="text-lg font-bold text-green-600" x-text="blindBagTotalQuantity + ' <x-lang key="messages.sales.create.pieces"/>'"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.actual_cost_label"/></span>
                                <span class="text-lg font-bold text-orange-600" x-text="'¥' + blindBagTotalCost.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.net_profit_label"/></span>
                                <span class="text-lg font-bold text-purple-600" x-text="'¥' + blindBagTotalProfit.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700"><x-lang key="messages.sales.create.profit_rate_label"/></span>
                                <span class="text-lg font-bold text-yellow-600" x-text="blindBagProfitRate.toFixed(1) + '%'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- 销售提醒 -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="messages.sales.create.notes"/></h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600"><x-lang key="messages.sales.create.confirm_product_info"/></p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600"><x-lang key="messages.sales.create.upload_certificate_hint"/></p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600"><x-lang key="messages.sales.create.customer_info_hint"/></p>
                            </div>
                            <div x-show="salesMode === 'blind_bag'" class="flex items-start">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600"><x-lang key="messages.sales.create.blind_bag_delivery_hint"/></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 