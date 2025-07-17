@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4" x-data="returnForm" x-init="init()">
    <!-- 标题 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">✏️ 编辑退�?/h1>
        <p class="text-gray-600">修改退货记录，自动更新库存</p>
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

    <!-- 退货表�?-->
    <form action="{{ route('mobile.returns.update', $returnRecord->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 基本信息 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">基本信息</h2>
            <div class="space-y-4">
                <!-- 仓库选择 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2">选择仓库</label>
                    <select name="store_id" class="form-input w-full px-3 py-2 rounded-lg border" required>
                        <option value=""><x-lang key="messages.stores.please_select"/></option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $returnRecord->store_id == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 客户信息 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2">客户信息（可选）</label>
                    <input type="text" name="customer" value="{{ $returnRecord->customer }}" 
                        class="form-input w-full px-3 py-2 rounded-lg border" placeholder="输入客户姓名或联系方�?>
                </div>

                <!-- 退货照�?-->
                <div>
                    <label class="form-label block text-sm font-medium mb-2">退货照片（可选）</label>
                    <input type="file" name="image" accept="image/*" 
                        class="form-input w-full px-3 py-2 rounded-lg border">
                    <p class="text-xs text-gray-500 mt-1">支持JPG、PNG格式，最�?MB</p>
                </div>

                <!-- 备注 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2">退货原因（可选）</label>
                    <textarea name="remark" rows="2" class="form-input w-full px-3 py-2 rounded-lg border" 
                        placeholder="输入退货原因或备注">{{ $returnRecord->remark }}</textarea>
                </div>
            </div>
        </div>

        <!-- 商品选择 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 退货商�?/h2>
            <div class="grid grid-cols-2 gap-4">
                @foreach($products as $product)
                    @php
                        $detail = $returnRecord->returnDetails->where('product_id', $product->id)->first();
                        $quantity = $detail ? $detail->quantity : 0;
                    @endphp
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ $product->name }}</span>
                            <span class="badge-warning text-xs px-2 py-1 rounded-full">售价: ¥{{ number_format($product->price, 2) }}</span>
                        </div>
                        <input type="hidden" name="products[{{ $loop->index }}][id]" value="{{ $product->id }}">
                        <input type="hidden" name="products[{{ $loop->index }}][unit_price]" value="{{ $product->price }}">
                        <input type="hidden" name="products[{{ $loop->index }}][cost_price]" value="{{ $product->cost_price }}">
                        <input type="number" 
                            name="products[{{ $loop->index }}][quantity]"
                            x-model="formData.products['{{ $product->id }}']?.quantity"
                            @input="updateQuantity('{{ $product->id }}', $event.target.value)"
                            class="form-input w-full px-3 py-2 rounded-lg border text-center text-lg font-semibold" 
                            placeholder="0" min="0" step="1">
                        <p class="text-xs text-gray-500 mt-1 text-center">退货数�?/p>
                    </div>
                @endforeach
            </div>
            <!-- 退货统�?-->
            <div class="bg-red-50 rounded-lg p-4 mt-4">
                <h4 class="text-md font-semibold text-red-900 mb-3">退货统�?/h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-red-600">退货数�?/p>
                        <p class="text-lg font-bold text-red-700" x-text="totalQuantity + ' �?"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600">退货金�?/p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalAmount.toFixed(2)"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600">成本损失</p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalCost.toFixed(2)"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 提交按钮 -->
        <div class="card p-6 space-y-3">
            <button type="submit" class="btn-warning w-full py-4 text-white font-semibold rounded-lg shadow-lg">
                <i class="bi bi-save mr-2"></i>
                保存修改
            </button>
            
            @if($returnRecord->canDelete())
                <form action="{{ route('mobile.returns.destroy', $returnRecord->id) }}" method="POST" 
                      onsubmit="return confirm('确定要删除这条退货记录吗�?)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-4 bg-red-500 text-white font-semibold rounded-lg shadow-lg hover:bg-red-600 transition-colors">
                        <i class="bi bi-trash mr-2"></i>
                        删除记录
                    </button>
                </form>
            @endif
        </div>
    </form>
    <div class="h-24"></div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('returnForm', () => ({
        formData: {
            products: {}
        },
        get totalQuantity() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
        },
        get totalAmount() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.price) || 0)), 0);
        },
        get totalCost() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.cost_price) || 0)), 0);
        },
        updateQuantity(id, quantity) {
            if (!this.formData.products[id]) {
                this.formData.products[id] = { quantity: 0, price: 0, cost_price: 0 };
            }
            this.formData.products[id].quantity = quantity;
        },
        init() {
            // 初始化所有商品的价格和成本，并预填数�?
            @foreach($products as $product)
                this.formData.products['{{ $product->id }}'] = {
                    quantity: {{ $returnRecord->returnDetails->where('product_id', $product->id)->first()?->quantity ?? 0 }},
                    price: {{ $product->price }},
                    cost_price: {{ $product->cost_price }}
                };
            @endforeach
        }
    }));
});
</script>
@endpush

<style>
.badge-warning {
    background: rgba(217, 119, 6, 0.1);
    color: #D97706;
}

.btn-warning {
    background: linear-gradient(135deg, #F59E0B, #D97706);
    transition: all 0.2s ease;
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}
</style>
@endsection 
