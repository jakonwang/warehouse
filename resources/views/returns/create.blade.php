@extends('layouts.app')

@section('title', '新增退货')
@section('header', '新增退货')

@section('content')
<div class="space-y-6" x-data="returnForm" x-init="init()">

    <form method="POST" action="{{ route('returns.store') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        <!-- 步骤指示器 -->
        <div class="flex items-center justify-center space-x-8 mb-8">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium" 
                     :class="currentStep >= 1 ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-500'">
                    1
                </div>
                <span class="ml-2 text-sm font-medium" :class="currentStep >= 1 ? 'text-red-600' : 'text-gray-500'">基本信息</span>
            </div>
            <div class="w-16 h-0.5 bg-gray-200"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium" 
                     :class="currentStep >= 2 ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-500'">
                    2
                </div>
                <span class="ml-2 text-sm font-medium" :class="currentStep >= 2 ? 'text-red-600' : 'text-gray-500'">退货商品</span>
            </div>
            <div class="w-16 h-0.5 bg-gray-200"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium" 
                     :class="currentStep >= 3 ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-500'">
                    3
                </div>
                <span class="ml-2 text-sm font-medium" :class="currentStep >= 3 ? 'text-red-600' : 'text-gray-500'">确认提交</span>
            </div>
        </div>

        <!-- 第1步：基本信息 -->
        <div x-show="currentStep === 1" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">基本信息</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 当前仓库显示 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">当前仓库</label>
                        <div class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                            @if($storeId && $stores->where('id', $storeId)->first())
                                {{ $stores->where('id', $storeId)->first()->name }}
                            @else
                                <span class="text-gray-500">请先选择仓库</span>
                            @endif
                        </div>
                        <input type="hidden" name="store_id" value="{{ $storeId }}">
                    </div>

                    <!-- 客户信息 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">客户信息</label>
                        <input type="text" name="customer" x-model="formData.customer" value="{{ old('customer') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('customer') border-red-300 @enderror" placeholder="客户姓名或联系方式">
                        @error('customer')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 退货原因 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">退货原因</label>
                        <select name="reason" x-model="formData.reason" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <option value="">请选择退货原因</option>
                            <option value="quality">质量问题</option>
                            <option value="damage">商品损坏</option>
                            <option value="wrong">发错商品</option>
                            <option value="customer">客户要求</option>
                            <option value="other">其他原因</option>
                        </select>
                    </div>

                    <!-- 退货类型 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">退货类型</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="return_type" value="restock" x-model="formData.return_type" class="mr-2">
                                <span class="text-sm">重新入库</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="return_type" value="damage" x-model="formData.return_type" class="mr-2">
                                <span class="text-sm">直接报废</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="return_type" value="transfer" x-model="formData.return_type" class="mr-2">
                                <span class="text-sm">调拨到其他仓库</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 备注信息 -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">备注信息</label>
                    <textarea name="remark" x-model="formData.remark" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('remark') border-red-300 @enderror" placeholder="详细说明退货情况..."></textarea>
                    @error('remark')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 上传凭证 -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">上传凭证</label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="bi bi-cloud-upload text-gray-400 text-3xl mb-2"></i>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">点击上传</span> 或拖拽文件</p>
                                <p class="text-xs text-gray-500">PNG, JPG, PDF (最大 2MB)</p>
                            </div>
                            <input type="file" name="image" class="hidden" accept="image/*,.pdf">
                        </label>
                    </div>
                </div>
            </div>

            <!-- 下一步按钮 -->
            <div class="flex justify-end">
                <button type="button" @click="nextStep()" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    下一步
                </button>
            </div>
        </div>

        <!-- 第2步：退货商品 -->
        <div x-show="currentStep === 2" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">退货商品</h3>
                
                <!-- 退货商品明细 -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">退货商品明细</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($products as $product)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h5 class="font-medium text-gray-900">{{ $product->name }}</h5>
                                    <p class="text-sm text-gray-500">售价: ¥{{ number_format($product->price, 2) }} | 成本: ¥{{ number_format($product->cost_price, 2) }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-gray-500">可退库存</span>
                                    <p class="text-sm font-medium text-green-600">{{ $product->getStockQuantity() ?? 0 }}件</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">退货数量</label>
                                <div class="relative">
                                    <input type="number" 
                                           name="products[{{ $loop->index }}][quantity]" 
                                           x-model="formData.products['{{ $product->id }}']?.quantity"
                                           @input="updateQuantity('{{ $product->id }}', $event.target.value)"
                                           class="w-full pl-4 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" 
                                           placeholder="0" 
                                           min="0">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">件</span>
                                    </div>
                                </div>
                                <input type="hidden" name="products[{{ $loop->index }}][id]" value="{{ $product->id }}">
                                <input type="hidden" name="products[{{ $loop->index }}][unit_price]" value="{{ $product->price }}">
                                <input type="hidden" name="products[{{ $loop->index }}][cost_price]" value="{{ $product->cost_price }}">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- 退货统计 -->
                <div class="bg-red-50 rounded-lg p-4">
                    <h4 class="text-md font-semibold text-red-900 mb-3">退货统计</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-sm text-red-600">退货数量</p>
                            <p class="text-lg font-bold text-red-700" x-text="totalQuantity + ' 件'"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-red-600">退货金额</p>
                            <p class="text-lg font-bold text-red-700" x-text="'¥' + totalAmount.toFixed(2)"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-red-600">成本损失</p>
                            <p class="text-lg font-bold text-red-700" x-text="'¥' + totalCost.toFixed(2)"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 导航按钮 -->
            <div class="flex justify-between">
                <button type="button" @click="prevStep()" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    上一步
                </button>
                <button type="button" @click="nextStep()" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    下一步
                </button>
            </div>
        </div>

        <!-- 第3步：确认提交 -->
        <div x-show="currentStep === 3" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">确认信息</h3>
                
                <!-- 基本信息确认 -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">基本信息</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">仓库</p>
                            <p class="text-base font-medium">
                                @if($storeId && $stores->where('id', $storeId)->first())
                                    {{ $stores->where('id', $storeId)->first()->name }}
                                @else
                                    未选择
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">客户信息</p>
                            <p class="text-base font-medium" x-text="formData.customer || '无'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">退货原因</p>
                            <p class="text-base font-medium" x-text="formData.reason || '未选择'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">退货类型</p>
                            <p class="text-base font-medium" x-text="formData.return_type || '未选择'"></p>
                        </div>
                    </div>
                </div>

                <!-- 退货商品确认 -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">退货商品</h4>
                    <div class="space-y-2">
                        <template x-for="(item, id) in formData.products" :key="id">
                            <div x-show="item.quantity > 0" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span x-text="'商品ID: ' + id + ' - 数量: ' + item.quantity + ' 件'"></span>
                                <span x-text="'¥' + (item.quantity * item.price).toFixed(2)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 退货统计 -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">退货数量</span>
                        <span class="text-lg font-bold text-red-600" x-text="totalQuantity + ' 件'"></span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">退货金额</span>
                        <span class="text-lg font-bold text-orange-600" x-text="'¥' + totalAmount.toFixed(2)"></span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">成本损失</span>
                        <span class="text-lg font-bold text-yellow-600" x-text="'¥' + totalCost.toFixed(2)"></span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">处理时间</span>
                        <span class="text-sm font-medium text-purple-600">{{ now()->format('Y-m-d H:i') }}</span>
                    </div>
                </div>

                <!-- 退货提醒 -->
                <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">注意事项</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3"></div>
                            <p class="text-sm text-gray-600">请上传退货凭证照片便于后续追溯</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-orange-500 rounded-full mt-2 mr-3"></div>
                            <p class="text-sm text-gray-600">质量问题退货请联系供应商协商</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></div>
                            <p class="text-sm text-gray-600">报废商品将从库存中直接扣除</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                            <p class="text-sm text-gray-600">调拨退货需要目标仓库确认接收</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 提交按钮 -->
            <div class="flex justify-between">
                <button type="button" @click="prevStep()" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    上一步
                </button>
                <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    确认提交退货
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('returnForm', () => ({
        formData: {
            store_id: '{{ $storeId }}',
            customer: '',
            reason: '',
            return_type: 'restock',
            target_store_id: '',
            remark: '',
            products: {}
        },
        currentStep: 1,
        get totalQuantity() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
        },
        get totalAmount() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.price) || 0)), 0);
        },
        get totalCost() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.cost_price) || 0)), 0);
        },
        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
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
        }
    }));
});
</script>
@endpush
@endsection 