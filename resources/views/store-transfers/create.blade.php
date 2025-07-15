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
                <p class="mt-1 text-sm text-gray-600">申请从其他仓库调拨商品到当前仓库</p>
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
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 源仓库选择 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">源仓库 *</label>
                    <select name="source_store_id" x-model="sourceStoreId" @change="loadAvailableProducts()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                    <select name="target_store_id" x-model="targetStoreId" @change="loadAvailableProducts()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">调拨商品 *</label>
                    <select name="product_id" x-model="selectedProductId" @change="loadProductStock()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">请先选择源仓库和目标仓库</option>
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 数量输入 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">调拨数量 *</label>
                    <input type="number" name="quantity" x-model="quantity" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="请输入调拨数量">
                    <p class="mt-1 text-sm text-gray-500" x-show="sourceStock > 0">
                        源仓库库存: <span x-text="sourceStock"></span> 个
                    </p>
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 成本信息 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">单位成本</label>
                    <input type="text" x-model="unitCost" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                </div>

                <!-- 调拨原因 -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">调拨原因 *</label>
                    <textarea name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="请说明调拨原因"></textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 备注 -->
                <div class="md:col-span-2">
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
        sourceStock: 0,
        unitCost: '',
        
        loadAvailableProducts() {
            if (!this.sourceStoreId || !this.targetStoreId) {
                return;
            }
            
            fetch(`/store-transfers/available-products?source_store_id=${this.sourceStoreId}&target_store_id=${this.targetStoreId}`)
                .then(response => response.json())
                .then(data => {
                    const select = document.querySelector('select[name="product_id"]');
                    select.innerHTML = '<option value="">请选择商品</option>';
                    
                    data.products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = `${product.name} (${product.price}元)`;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('加载商品失败:', error);
                });
        },
        
        loadProductStock() {
            if (!this.selectedProductId || !this.sourceStoreId) {
                return;
            }
            
            fetch(`/store-transfers/product-stock?product_id=${this.selectedProductId}&store_id=${this.sourceStoreId}`)
                .then(response => response.json())
                .then(data => {
                    this.sourceStock = data.quantity;
                    this.unitCost = data.unit_cost;
                })
                .catch(error => {
                    console.error('加载库存信息失败:', error);
                });
        }
    }
}
</script>
@endpush
@endsection 