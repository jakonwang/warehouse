@extends('layouts.app')

@section('title', '新增入库')
@section('header', '新增入库')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8 relative overflow-hidden">
    <!-- 装饰性背景元素 -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-200/30 to-purple-200/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-indigo-200/30 to-blue-200/30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-purple-200/20 to-pink-200/20 rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 relative z-10" x-data="stockInManager()">
        
        <!-- 顶部面包屑 -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-center space-x-4">
                <div class="flex items-center text-sm text-gray-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">控制台</a>
                    <i class="bi bi-chevron-right mx-2"></i>
                    <a href="{{ route('stock-ins.index') }}" class="hover:text-blue-600 transition-colors">入库管理</a>
                    <i class="bi bi-chevron-right mx-2"></i>
                    <span class="text-gray-900 font-medium">新增入库</span>
                </div>
            </div>
            
            <a href="{{ route('stock-ins.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100/80 hover:bg-gray-200/80 text-gray-700 font-medium rounded-xl transition-all duration-200 backdrop-blur-sm">
                <i class="bi bi-arrow-left mr-2"></i>
                返回列表
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 入库表单 -->
        <form action="{{ route('stock-ins.store') }}" method="POST" id="stockInForm">
            @csrf
            
            <!-- 基本信息 -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-blue-500/10 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-info-circle text-blue-600 mr-2"></i>
                    基本信息
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">仓库选择</label>
                        <select name="store_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80">
                            <option value=""><x-lang key="messages.stores.please_select"/></option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">供应商</label>
                        <input type="text" name="supplier" value="{{ old('supplier') }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80"
                               placeholder="请输入供应商名称">
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">备注</label>
                    <textarea name="remark" rows="3" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80"
                              placeholder="请输入备注信息">{{ old('remark') }}</textarea>
                </div>
            </div>

            <!-- 商品入库明细 -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-purple-500/10 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="bi bi-box-seam text-blue-600 mr-2"></i>
                        商品入库明细
                    </h3>
                    <button type="button" @click="addProduct()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                        <i class="bi bi-plus mr-2"></i>
                        添加商品
                    </button>
                </div>

                <!-- 商品列表 -->
                <div class="space-y-4">
                    <template x-for="(item, index) in formData.products" :key="index">
                        <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">商品</label>
                                    <select :name="'products[' + index + '][id]'" x-model="item.id" @change="updateProductInfo(index)" required 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">请选择商品</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-cost="{{ $product->cost_price }}"
                                                    data-name="{{ $product->name }}">
                                                {{ $product->name }} (成本¥{{ $product->cost_price }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">入库数量</label>
                                    <input type="number" :name="'products[' + index + '][quantity]'" x-model="item.quantity" 
                                           @input="calculateAmount(index)" required min="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="0">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">小计金额</label>
                                    <div class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700 font-medium" x-text="'¥' + (item.total_amount || 0).toFixed(2)"></div>
                                </div>
                                
                                <div>
                                    <button type="button" @click="removeProduct(index)" class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <!-- 空状态 -->
                    <div x-show="formData.products.length === 0" class="text-center py-8 text-gray-500">
                        <i class="bi bi-box text-4xl mb-2"></i>
                        <p>暂无入库商品，点击"添加商品"开始录入</p>
                    </div>
                </div>

                <!-- 汇总信息 -->
                <div class="mt-6 bg-blue-50/80 rounded-xl p-4 border border-blue-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-sm text-blue-600 font-medium">总数量</div>
                            <div class="text-2xl font-bold text-blue-700" x-text="getTotalQuantity()"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-blue-600 font-medium">总金额</div>
                            <div class="text-2xl font-bold text-blue-700" x-text="'¥' + getTotalAmount().toFixed(2)"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-blue-600 font-medium">商品种类</div>
                            <div class="text-2xl font-bold text-blue-700" x-text="formData.products.length"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 提交按钮 -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('stock-ins.index') }}" class="px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-colors">
                    取消
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                    <i class="bi bi-check-circle mr-2"></i>
                    确认入库
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function stockInManager() {
    return {
        formData: {
            products: []
        },
        
        addProduct() {
            this.formData.products.push({
                id: '',
                quantity: 0,
                cost_price: 0,
                total_amount: 0
            });
        },
        
        removeProduct(index) {
            this.formData.products.splice(index, 1);
        },
        
        updateProductInfo(index) {
            const item = this.formData.products[index];
            const productSelect = document.querySelector(`select[name="products[${index}][id]"]`);
            const selectedOption = productSelect.querySelector(`option[value="${item.id}"]`);
            
            if (selectedOption) {
                item.cost_price = parseFloat(selectedOption.dataset.cost) || 0;
                this.calculateAmount(index);
            }
        },
        
        calculateAmount(index) {
            const item = this.formData.products[index];
            item.total_amount = (parseFloat(item.quantity || 0) * parseFloat(item.cost_price || 0));
        },
        
        getTotalQuantity() {
            return this.formData.products.reduce((total, item) => {
                return total + parseInt(item.quantity || 0);
            }, 0);
        },
        
        getTotalAmount() {
            return this.formData.products.reduce((total, item) => {
                return total + parseFloat(item.total_amount || 0);
            }, 0);
        }
    }
}
</script>
@endsection 