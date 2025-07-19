@extends('layouts.app')

@section('title', '新增盘点')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8 relative overflow-hidden">
    <!-- 装饰性背景元素 -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-green-200/30 to-blue-200/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-purple-200/30 to-indigo-200/30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-cyan-200/20 to-teal-200/20 rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10" x-data="inventoryCheckManager()">
        
        <!-- 顶部面包屑 -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">
            <div class="flex items-center space-x-4">
                <div class="flex items-center text-sm text-gray-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">控制台</a>
                    <i class="bi bi-chevron-right mx-2"></i>
                    <a href="{{ route('inventory-check.index') }}" class="hover:text-blue-600 transition-colors">库存盘点</a>
                    <i class="bi bi-chevron-right mx-2"></i>
                    <span class="text-gray-900 font-medium">新增盘点</span>
                </div>
            </div>
            
            <a href="{{ route('inventory-check.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100/80 hover:bg-gray-200/80 text-gray-700 font-medium rounded-xl transition-all duration-200 backdrop-blur-sm">
                <i class="bi bi-arrow-left mr-2"></i>
                返回列表
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 盘点表单 -->
        <form action="{{ route('inventory-check.store') }}" method="POST" id="inventoryCheckForm">
            @csrf
            
            <!-- 基本信息 -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-green-500/10 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-info-circle text-blue-600 mr-2"></i>
                    基本信息
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">仓库选择 <span class="text-red-500">*</span></label>
                        <select name="store_id" x-model="formData.store_id" @change="loadInventory()" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80">
                            <option value=""><x-lang key="messages.stores.please_select"/></option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">备注</label>
                        <textarea name="remark" x-model="formData.remark" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80"
                                  placeholder="请输入备注信息">{{ old('remark') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 盘点明细 -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-xl shadow-teal-500/10 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="bi bi-clipboard-check text-blue-600 mr-2"></i>
                        盘点明细
                    </h3>
                    <button type="button" @click="addProduct()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                        <i class="bi bi-plus mr-2"></i>
                        添加商品
                    </button>
                </div>

                <!-- 商品盘点列表 -->
                <div class="space-y-4">
                    <template x-for="(item, index) in formData.details" :key="index">
                        <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">商品</label>
                                    <select :name="'details[' + index + '][product_id]'" x-model="item.product_id" 
                                            @change="updateSystemQuantity(index)"
                                            required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">请选择商品</option>
                                        <template x-for="product in storeProducts" :key="product.id">
                                            <option :value="product.id" 
                                                    :data-cost="product.cost_price"
                                                    :data-name="product.name">
                                                <span x-text="product.name"></span> (¥<span x-text="product.price"></span>)
                                            </option>
                                        </template>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">系统库存</label>
                                    <div class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700 font-medium" x-text="item.system_quantity"></div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">实际库存</label>
                                    <input type="number" :name="'details[' + index + '][actual_quantity]'" 
                                           x-model="item.actual_quantity" @input="calculateDifference(index)"
                                           required min="0" step="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="0">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">差异</label>
                                    <div class="px-3 py-2 rounded-lg font-medium" 
                                         :class="item.difference > 0 ? 'bg-green-100 text-green-700' : item.difference < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'"
                                         x-text="item.difference"></div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">单位成本</label>
                                    <div class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700 font-medium" x-text="'¥' + (item.unit_cost || 0).toFixed(2)"></div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">差异成本</label>
                                    <div class="px-3 py-2 rounded-lg font-medium"
                                         :class="item.total_cost > 0 ? 'bg-green-100 text-green-700' : item.total_cost < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'"
                                         x-text="'¥' + (item.total_cost || 0).toFixed(2)"></div>
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
                    <div x-show="formData.details.length === 0" class="text-center py-8 text-gray-500">
                        <i class="bi bi-clipboard text-4xl mb-2"></i>
                        <p>暂无盘点商品，点击"添加商品"开始盘点</p>
                    </div>
                </div>

                <!-- 汇总信息 -->
                <div class="mt-6 bg-blue-50/80 rounded-xl p-4 border border-blue-200">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-sm text-blue-600 font-medium">盘点商品</div>
                            <div class="text-2xl font-bold text-blue-700" x-text="formData.details.length"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-blue-600 font-medium">总差异</div>
                            <div class="text-2xl font-bold" 
                                 :class="getTotalDifference() > 0 ? 'text-green-700' : getTotalDifference() < 0 ? 'text-red-700' : 'text-blue-700'"
                                 x-text="getTotalDifference()"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-blue-600 font-medium">盈亏金额</div>
                            <div class="text-2xl font-bold"
                                 :class="getTotalCostDifference() > 0 ? 'text-green-700' : getTotalCostDifference() < 0 ? 'text-red-700' : 'text-blue-700'"
                                 x-text="'¥' + getTotalCostDifference().toFixed(2)"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-blue-600 font-medium">盘点时间</div>
                            <div class="text-lg font-bold text-blue-700">{{ now()->format('m-d H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 提交按钮 -->
            <div class="flex justify-end space-x-4 mt-6">
                <a href="{{ route('inventory-check.index') }}" class="px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-colors">
                    取消
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                    <i class="bi bi-check-circle mr-2"></i>
                    确认盘点
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function inventoryCheckManager() {
    return {
        formData: {
            store_id: '',
            remark: '',
            details: []
        },
        inventory: [], // 存储当前仓库的库存数据
        storeProducts: [], // 存储当前仓库的商品数据
        
        addProduct() {
            this.formData.details.push({
                product_id: '',
                system_quantity: 0,
                actual_quantity: 0,
                difference: 0,
                unit_cost: 0,
                total_cost: 0
            });
        },
        
        removeProduct(index) {
            this.formData.details.splice(index, 1);
        },
        
        updateSystemQuantity(index) {
            const item = this.formData.details[index];
            const productId = item.product_id;
            
            if (productId) {
                // 从库存数据中查找对应产品的库存
                const inventoryItem = this.inventory.find(inv => inv.product_id == productId);
                item.system_quantity = inventoryItem ? inventoryItem.quantity : 0;
                
                // 获取商品成本价
                const productSelect = document.querySelector(`select[name="details[${index}][product_id]"]`);
                const selectedOption = productSelect.querySelector(`option[value="${productId}"]`);
                if (selectedOption) {
                    item.unit_cost = parseFloat(selectedOption.dataset.cost) || 0;
                }
                
                this.calculateDifference(index);
            }
        },
        
        calculateDifference(index) {
            const item = this.formData.details[index];
            item.difference = parseInt(item.actual_quantity || 0) - parseInt(item.system_quantity || 0);
            item.total_cost = item.difference * item.unit_cost;
        },
        
        getTotalDifference() {
            return this.formData.details.reduce((total, item) => {
                return total + parseInt(item.difference || 0);
            }, 0);
        },
        
        getTotalCostDifference() {
            return this.formData.details.reduce((total, item) => {
                return total + parseFloat(item.total_cost || 0);
            }, 0);
        },
        
        async loadInventory() {
            if (!this.formData.store_id) {
                this.inventory = [];
                this.storeProducts = [];
                return;
            }
            
            try {
                // 同时加载库存和商品数据
                const [inventoryResponse, productsResponse] = await Promise.all([
                    fetch(`/api/stores/${this.formData.store_id}/inventory`),
                    fetch(`/api/stores/${this.formData.store_id}/products`)
                ]);
                
                if (inventoryResponse.ok) {
                    this.inventory = await inventoryResponse.json();
                }
                
                if (productsResponse.ok) {
                    const productsData = await productsResponse.json();
                    // 只显示标准商品，因为库存盘点不需要对盲袋商品进行操作
                    this.storeProducts = productsData.standard_products || [];
                }
                
                // 更新已选择商品的系统库存
                this.formData.details.forEach((item, index) => {
                    if (item.product_id) {
                        this.updateSystemQuantity(index);
                    }
                });
            } catch (error) {
                console.error('加载数据失败:', error);
            }
        }
    }
}
</script>
@endsection 