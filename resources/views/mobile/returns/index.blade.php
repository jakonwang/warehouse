@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4" x-data="returnForm" x-init="init()">
    <!-- 标题 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">↩️ <x-lang key="messages.mobile.returns.title"/></h1>
        <p class="text-gray-600"><x-lang key="messages.mobile.returns.subtitle"/></p>
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
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 退货表单 -->
    <form action="{{ route('mobile.returns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 基本信息 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="messages.mobile.returns.basic_info"/></h2>
            <div class="space-y-4">
                <!-- 当前仓库显示 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="messages.mobile.returns.current_store"/></label>
                    <div class="form-input w-full px-3 py-2 rounded-lg border bg-gray-50 text-gray-700">
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
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="messages.mobile.returns.customer_info"/></label>
                    <input type="text" name="customer" value="{{ old('customer') }}" 
                        class="form-input w-full px-3 py-2 rounded-lg border" placeholder="<x-lang key="messages.mobile.returns.customer_placeholder"/>">
                </div>

                <!-- 退货照片 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="messages.mobile.returns.return_photo"/></label>
                    <input type="file" name="image" accept="image/*" 
                        class="form-input w-full px-3 py-2 rounded-lg border">
                    <p class="text-xs text-gray-500 mt-1"><x-lang key="messages.mobile.returns.photo_desc"/></p>
                </div>

                <!-- 备注 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="messages.mobile.returns.return_reason"/></label>
                    <textarea name="remark" rows="2" class="form-input w-full px-3 py-2 rounded-lg border" 
                        placeholder="<x-lang key="messages.mobile.returns.reason_placeholder"/>">{{ old('remark') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 商品选择 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 <x-lang key="messages.mobile.returns.return_products"/></h2>
            <div class="grid grid-cols-2 gap-4">
                @foreach($products as $product)
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ $product->name }}</span>
                            <span class="badge-warning text-xs px-2 py-1 rounded-full"><x-lang key="messages.mobile.returns.price"/>: ¥{{ number_format($product->price, 2) }}</span>
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
                        <p class="text-xs text-gray-500 mt-1 text-center"><x-lang key="messages.mobile.returns.return_quantity"/></p>
                    </div>
                @endforeach
            </div>
            <!-- 退货统计 -->
            <div class="bg-red-50 rounded-lg p-4 mt-4">
                <h4 class="text-md font-semibold text-red-900 mb-3"><x-lang key="messages.mobile.returns.return_stats"/></h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="messages.mobile.returns.return_quantity"/></p>
                        <p class="text-lg font-bold text-red-700" x-text="totalQuantity + ' <x-lang key="messages.mobile.returns.pieces"/>'"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="messages.mobile.returns.return_amount"/></p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalAmount.toFixed(2)"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="messages.mobile.returns.cost_loss"/></p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalCost.toFixed(2)"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 提交按钮 -->
        <div class="card p-6">
            <button type="submit" class="btn-warning w-full py-4 text-white font-semibold rounded-lg shadow-lg">
                <i class="bi bi-arrow-return-left mr-2"></i>
                <x-lang key="messages.mobile.returns.confirm_return"/>
            </button>
        </div>
    </form>

    <!-- 最近退货记录 -->
    @if(isset($recentRecords) && $recentRecords->count() > 0)
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 <x-lang key="messages.mobile.returns.recent_records"/></h2>
            <div class="space-y-3">
                @foreach($recentRecords as $record)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-sm font-medium text-gray-700">
                                {{ date('m-d H:i', strtotime($record->created_at)) }}
                            </span>
                            <span class="badge-warning text-xs px-2 py-1 rounded-full">
                                ¥{{ number_format($record->total_amount, 2) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @foreach($record->returnDetails as $detail)
                                <span class="text-xs bg-white px-2 py-1 rounded border">
                                    {{ $detail->product->name ?? __('messages.mobile.returns.unknown_product') }} × {{ $detail->quantity }}
                                </span>
                            @endforeach
                        </div>
                        @if($record->customer)
                            <p class="text-xs text-gray-500 mt-1"><x-lang key="messages.mobile.returns.customer"/>: {{ $record->customer }}</p>
                        @endif
                        @if($record->remark)
                            <p class="text-xs text-gray-500 mt-1"><x-lang key="messages.mobile.returns.reason"/>: {{ $record->remark }}</p>
                        @endif
                        <div class="mt-2 flex justify-end space-x-2">
                            <a href="{{ route('mobile.returns.edit', $record->id) }}" class="inline-flex items-center px-3 py-1 bg-yellow-400 text-white text-xs font-semibold rounded shadow hover:bg-yellow-500">
                                <i class="bi bi-pencil mr-1"></i> <x-lang key="messages.mobile.returns.edit"/>
                            </a>
                            @if($record->canDelete())
                                <form action="{{ route('mobile.returns.destroy', $record->id) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('<x-lang key="messages.mobile.returns.confirm_delete"/>')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded shadow hover:bg-red-600">
                                        <i class="bi bi-trash mr-1"></i> <x-lang key="messages.mobile.returns.delete"/>
                                    </button>
                                </form>
                            @endif
                        </div>
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