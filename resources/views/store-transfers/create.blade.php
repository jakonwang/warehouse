@extends('layouts.app')

@section('title', '新建调拨申请')
@section('header', '新建调拨申请')

@section('content')
<div class="space-y-6">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">新建调拨申请</h1>
                <p class="mt-1 text-sm text-gray-600">申请从其他仓库调拨商品到目标仓库</p>
            </div>
            <a href="{{ route('store-transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                <i class="bi bi-arrow-left mr-2"></i>
                返回列表
            </a>
        </div>
    </div>

    <!-- 调拨申请表单 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('store-transfers.store') }}" x-data="transferForm()">
            @csrf
            
            <!-- 调试信息 -->
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h3 class="text-sm font-medium text-red-800 mb-2">表单验证错误：</h3>
                    <ul class="text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 源仓库选择 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">源仓库 *</label>
                    <select name="source_store_id" x-model="sourceStoreId" @change="loadSourceStoreProducts()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">请选择源仓库</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                    @error('source_store_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 目标仓库选择 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">目标仓库 *</label>
                    <select name="target_store_id" x-model="targetStoreId" @change="loadTargetStoreProducts()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">请选择目标仓库</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                    @error('target_store_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 商品选择 -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">调拨商品 *</label>
                    <select name="product_id" x-model="selectedProductId" @change="loadProductComparison()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">请先选择源仓库和目标仓库</option>
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 库存对比信息 -->
                <div class="lg:col-span-2" x-show="comparison">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">库存对比</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- 源仓库库存 -->
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">源仓库库存</span>
                                    <span class="text-sm text-gray-500" x-text="sourceStoreName"></span>
                                </div>
                                <div class="text-2xl font-bold text-blue-600" x-text="comparison.source_store.quantity + ' 个'"></div>
                                <div class="text-xs text-gray-500">单位成本: ¥<span x-text="comparison.source_store.unit_cost"></span></div>
                            </div>
                            
                            <!-- 目标仓库库存 -->
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">目标仓库库存</span>
                                    <span class="text-sm text-gray-500" x-text="targetStoreName"></span>
                                </div>
                                <div class="text-2xl font-bold text-green-600" x-text="comparison.target_store.quantity + ' 个'"></div>
                                <div class="text-xs text-gray-500">单位成本: ¥<span x-text="comparison.target_store.unit_cost"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 数量输入 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">调拨数量 *</label>
                    <input type="number" name="quantity" x-model="quantity" min="1" :max="maxQuantity" @input="updateTotalCost()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="请输入调拨数量">
                    <p class="mt-1 text-sm text-gray-500" x-show="comparison">
                        可调拨数量: <span x-text="maxQuantity"></span> 个
                    </p>
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 成本信息 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">预计总成本</label>
                    <input type="text" x-model="totalCost" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                    <p class="mt-1 text-sm text-gray-500">单位成本: ¥<span x-text="unitCost"></span></p>
                </div>

                <!-- 调拨原因 -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">调拨原因 *</label>
                    <textarea name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="请说明调拨原因"></textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 备注 -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">备注</label>
                    <textarea name="remark" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="可选备注信息"></textarea>
                    @error('remark')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 提交按钮 -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('store-transfers.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    取消
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    提交申请
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function transferForm() {
    return {
        sourceStoreId: '',
        targetStoreId: '',
        selectedProductId: '',
        quantity: '',
        unitCost: 0,
        totalCost: '¥0.00',
        maxQuantity: 0,
        comparison: null,
        sourceStoreName: '',
        targetStoreName: '',
        
        loadSourceStoreProducts() {
            if (!this.sourceStoreId) {
                this.resetProductOptions();
                return;
            }
            
            // 获取源仓库名称
            const sourceSelect = document.querySelector('select[name="source_store_id"]');
            const selectedOption = sourceSelect.options[sourceSelect.selectedIndex];
            this.sourceStoreName = selectedOption.text;
            
            fetch(`/store-transfers/source-store-products?source_store_id=${this.sourceStoreId}`)
                .then(response => response.json())
                .then(data => {
                    this.updateProductOptions(data.products);
                })
                .catch(error => {
                    console.error('加载源仓库商品失败:', error);
                    this.resetProductOptions();
                });
        },
        
        loadTargetStoreProducts() {
            if (!this.targetStoreId) {
                return;
            }
            
            // 获取目标仓库名称
            const targetSelect = document.querySelector('select[name="target_store_id"]');
            const selectedOption = targetSelect.options[targetSelect.selectedIndex];
            this.targetStoreName = selectedOption.text;
        },
        
        resetProductOptions() {
            const select = document.querySelector('select[name="product_id"]');
            select.innerHTML = '<option value="">请先选择源仓库和目标仓库</option>';
            this.selectedProductId = '';
            this.comparison = null;
            this.unitCost = 0;
            this.maxQuantity = 0;
            this.updateTotalCost();
        },
        
        updateProductOptions(products) {
            const select = document.querySelector('select[name="product_id"]');
            select.innerHTML = '<option value="">请选择商品</option>';
            
            products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.name} (${product.code}) - 库存: ${product.quantity}个`;
                select.appendChild(option);
            });
        },
        
        loadProductComparison() {
            if (!this.selectedProductId || !this.sourceStoreId || !this.targetStoreId) {
                this.comparison = null;
                this.unitCost = 0;
                this.maxQuantity = 0;
                this.updateTotalCost();
                return;
            }
            
            fetch(`/store-transfers/product-comparison?product_id=${this.selectedProductId}&source_store_id=${this.sourceStoreId}&target_store_id=${this.targetStoreId}`)
                .then(response => response.json())
                .then(data => {
                    this.comparison = data.comparison;
                    this.unitCost = parseFloat(this.comparison.source_store.unit_cost) || 0;
                    this.maxQuantity = parseInt(this.comparison.source_store.quantity) || 0;
                    this.updateTotalCost();
                })
                .catch(error => {
                    console.error('加载商品对比失败:', error);
                    this.comparison = null;
                    this.unitCost = 0;
                    this.maxQuantity = 0;
                    this.updateTotalCost();
                });
        },
        
        updateTotalCost() {
            const quantity = parseFloat(this.quantity) || 0;
            const unitCost = parseFloat(this.unitCost) || 0;
            
            if (quantity > 0 && unitCost > 0) {
                const total = quantity * unitCost;
                this.totalCost = `¥${total.toFixed(2)}`;
            } else {
                this.totalCost = '¥0.00';
            }
        }
    }
}
</script>
@endpush
@endsection 