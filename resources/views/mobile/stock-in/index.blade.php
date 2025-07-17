@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4" x-data="stockInManager()" x-init="init()">
    <!-- 标题 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">📦 <x-lang key="mobile.stock_in.title"/></h1>
        <p class="text-gray-600"><x-lang key="mobile.stock_in.subtitle"/></p>
    </div>

    @if (session('success'))
        <div class="card p-4 border-l-4 border-green-500 bg-green-50">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="card p-4 border-l-4 border-red-500 bg-red-50">
            <ul class="text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>�?{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 入库表单 -->
    <form action="{{ route('mobile.stock-in.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 基本信息 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="mobile.stock_in.basic_info"/></h2>
            <div class="space-y-4">
                <!-- 仓库选择 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.stock_in.select_store"/></label>
                    <select name="store_id" class="form-input w-full px-3 py-2 rounded-lg border" required>
                        <option value=""><x-lang key="mobile.stock_in.please_select_store"/></option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 供应�?-->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.stock_in.supplier"/></label>
                    <input type="text" name="supplier" value="{{ old('supplier') }}" 
                        class="form-input w-full px-3 py-2 rounded-lg border" placeholder="<x-lang key="mobile.stock_in.supplier_placeholder"/>">
                </div>

                <!-- 备注 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.stock_in.remark"/></label>
                    <textarea name="remark" rows="2" class="form-input w-full px-3 py-2 rounded-lg border" 
                        placeholder="<x-lang key="mobile.stock_in.remark_placeholder"/>">{{ old('remark') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 商品入库 -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">💰 <x-lang key="mobile.stock_in.products"/></h2>
                <button type="button" @click="addProduct()" class="btn-primary px-4 py-2 text-sm">
                    <i class="bi bi-plus mr-1"></i>
                    <x-lang key="mobile.stock_in.add_product"/>
                </button>
            </div>
            
            <!-- 商品列表 -->
            <div class="space-y-4">
                <template x-for="(item, index) in formData.products" :key="index">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                        <div class="grid grid-cols-1 gap-4">
                            <!-- 商品选择 -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="mobile.stock_in.select_product"/></label>
                                <select :name="'products[' + index + '][id]'" x-model="item.id" @change="updateProductInfo(index)" required 
                                        class="form-input w-full px-3 py-2 rounded-lg border">
                                    <option value=""><x-lang key="mobile.stock_in.please_select_product"/></option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-cost="{{ $product->cost_price }}"
                                                data-name="{{ $product->name }}">
                                            {{ $product->name }} (<x-lang key="mobile.stock_in.cost"/>¥{{ $product->cost_price }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- 入库数量 -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="mobile.stock_in.stock_in_quantity"/></label>
                                <input type="number" :name="'products[' + index + '][quantity]'" x-model="item.quantity" 
                                       @input="calculateAmount(index)" required min="1"
                                       class="form-input w-full px-3 py-2 rounded-lg border text-center text-lg font-semibold" 
                                       placeholder="0">
                            </div>
                            
                            <!-- 小计金额 -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="mobile.stock_in.subtotal"/></label>
                                <div class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700 font-medium text-center" x-text="'¥' + (item.total_amount || 0).toFixed(2)"></div>
                            </div>
                            
                            <!-- 删除按钮 -->
                            <div>
                                <button type="button" @click="removeProduct(index)" class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                                    <i class="bi bi-trash mr-1"></i>
                                    <x-lang key="mobile.stock_in.remove"/>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- 空状�?-->
                <div x-show="formData.products.length === 0" class="text-center py-8 text-gray-500">
                    <i class="bi bi-box text-4xl mb-2"></i>
                    <p><x-lang key="mobile.stock_in.no_products"/></p>
                </div>
            </div>
            
            <!-- 汇总信�?-->
            <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h4 class="text-md font-semibold text-blue-900 mb-3"><x-lang key="mobile.stock_in.summary"/></h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-sm text-blue-600"><x-lang key="mobile.stock_in.total_quantity"/></div>
                        <div class="text-lg font-bold text-blue-700" x-text="getTotalQuantity()"></div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-blue-600"><x-lang key="mobile.stock_in.total_amount"/></div>
                        <div class="text-lg font-bold text-blue-700" x-text="'¥' + getTotalAmount().toFixed(2)"></div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-blue-600"><x-lang key="mobile.stock_in.product_types"/></div>
                        <div class="text-lg font-bold text-blue-700" x-text="formData.products.length"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 提交按钮 -->
        <div class="card p-6">
            <button type="submit" class="btn-primary w-full py-4 text-white font-semibold rounded-lg shadow-lg">
                <i class="bi bi-box-arrow-in-down mr-2"></i>
                <x-lang key="mobile.stock_in.confirm_stock_in"/>
            </button>
        </div>
    </form>

    <!-- 最近入库记�?-->
    @if(isset($recentRecords) && $recentRecords->count() > 0)
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 <x-lang key="mobile.stock_in.recent_records"/></h2>
            <div class="space-y-3">
                @foreach($recentRecords as $record)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-sm font-medium text-gray-700">
                                {{ date('m-d H:i', strtotime($record->created_at)) }}
                            </span>
                            <span class="badge-success text-xs px-2 py-1 rounded-full">
                                ¥{{ number_format($record->total_amount, 2) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @foreach($record->stockInDetails as $detail)
                                <span class="text-xs bg-white px-2 py-1 rounded border">
                                    {{ $detail->product->name ?? __('mobile.stock_in.unknown_product') }} × {{ $detail->quantity }}
                                </span>
                            @endforeach
                        </div>
                        @if($record->supplier)
                            <p class="text-xs text-gray-500 mt-1"><x-lang key="mobile.stock_in.supplier"/>: {{ $record->supplier }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <div class="h-24"></div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('stockInManager', () => ({
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
        },
        
        init() {
            // 初始化时添加一个空商品
            this.addProduct();
        }
    }));
});
</script>
@endpush

<style>
.badge-success {
    background: rgba(5, 150, 105, 0.1);
    color: var(--secondary);
}
</style>
@endsection 
