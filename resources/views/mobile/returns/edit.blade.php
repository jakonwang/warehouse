@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4" x-data="returnForm" x-init="init()">
    <!-- 标题 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">✏️ <x-lang key="mobile.returns.edit_title"/></h1>
        <p class="text-gray-600"><x-lang key="mobile.returns.edit_subtitle"/></p>
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
    <form action="{{ route('mobile.returns.update', $returnRecord->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 基本信息 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🏪 <x-lang key="mobile.returns.basic_info"/></h2>
            <div class="space-y-4">
                <!-- 仓库选择 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.select_store"/></label>
                    <select name="store_id" class="form-input w-full px-3 py-2 rounded-lg border" required>
                        <option value=""><x-lang key="mobile.returns.please_select_store"/></option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $returnRecord->store_id == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 客户信息 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.customer_info"/>（<x-lang key="mobile.returns.optional"/>）</label>
                    <input type="text" name="customer" value="{{ $returnRecord->customer }}" 
                        class="form-input w-full px-3 py-2 rounded-lg border" placeholder="<x-lang key="mobile.returns.customer_placeholder"/>">
                </div>

                <!-- 退货照片 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2">📷 <x-lang key="mobile.returns.return_photo"/>（<x-lang key="mobile.returns.optional"/>）</label>
                    
                    <!-- 隐藏的文件输入框 -->
                    <input type="file" name="image" accept="image/*" class="hidden" id="returnEditImageInput">
                    
                    <!-- 上传选项按钮 -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <button type="button" onclick="selectFromGalleryReturnEdit()" class="flex flex-col items-center justify-center py-4 bg-blue-50 text-blue-700 rounded-lg border border-blue-200 hover:bg-blue-100 transition-all duration-200 transform hover:scale-105">
                            <i class="bi bi-image text-2xl mb-1"></i>
                            <span class="text-sm font-medium"><x-lang key="mobile.returns.select_gallery"/></span>
                        </button>
                        <button type="button" onclick="takePhotoReturnEdit()" class="flex flex-col items-center justify-center py-4 bg-green-50 text-green-700 rounded-lg border border-green-200 hover:bg-green-100 transition-all duration-200 transform hover:scale-105">
                            <i class="bi bi-camera text-2xl mb-1"></i>
                            <span class="text-sm font-medium"><x-lang key="mobile.returns.take_photo"/></span>
                        </button>
                    </div>
                    
                    <!-- 当前图片显示 -->
                    @if($returnRecord->image_path)
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 mb-2">🖼️ <x-lang key="mobile.returns.current_image"/>：</p>
                            <img src="{{ asset('storage/' . $returnRecord->image_path) }}" alt="<x-lang key="mobile.returns.current_return_photo"/>" class="w-full max-h-48 object-contain rounded-lg border border-gray-200 shadow-md">
                        </div>
                    @endif
                    
                    <!-- 图片预览 -->
                    <div id="return-edit-image-preview" class="mt-2 hidden">
                        <div class="relative inline-block">
                            <img src="" alt="<x-lang key="mobile.returns.preview_image"/>" class="max-w-full h-48 rounded-lg border border-gray-200 object-cover shadow-md">
                            <button type="button" onclick="removeReturnEditImage()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600 transition-colors duration-200 shadow-lg">
                                ×
                            </button>
                        </div>
                        <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded p-2" id="return-edit-image-info"></div>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-1">💡 <x-lang key="mobile.returns.photo_desc"/></p>
                </div>

                <!-- 备注 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><x-lang key="mobile.returns.return_reason"/>（<x-lang key="mobile.returns.optional"/>）</label>
                    <textarea name="remark" rows="2" class="form-input w-full px-3 py-2 rounded-lg border" 
                        placeholder="<x-lang key="mobile.returns.reason_placeholder"/>">{{ $returnRecord->remark }}</textarea>
                </div>
            </div>
        </div>

        <!-- 商品选择 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 <x-lang key="mobile.returns.return_products"/></h2>
            <div class="grid grid-cols-2 gap-4">
                @foreach($products as $product)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                                <p class="text-sm text-gray-500"><x-lang key="mobile.returns.price"/>: ¥{{ number_format($product->price, 2) }}</p>
                            </div>
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
                        <p class="text-xs text-gray-500 mt-1 text-center"><x-lang key="mobile.returns.return_quantity"/></p>
                    </div>
                @endforeach
            </div>
            <!-- 退货统计 -->
            <div class="bg-red-50 rounded-lg p-4 mt-4">
                <h4 class="text-md font-semibold text-red-900 mb-3"><x-lang key="mobile.returns.return_stats"/></h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="mobile.returns.return_quantity"/></p>
                        <p class="text-lg font-bold text-red-700" x-text="totalQuantity + ' 件'"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="mobile.returns.return_amount"/></p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalAmount.toFixed(2)"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><x-lang key="mobile.returns.cost_loss"/></p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalCost.toFixed(2)"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 提交按钮 -->
        <div class="card p-6 space-y-3">
            <button type="submit" class="btn-warning w-full py-4 text-white font-semibold rounded-lg shadow-lg">
                <i class="bi bi-save mr-2"></i>
                <x-lang key="mobile.returns.save_changes"/>
            </button>
            
            @if($returnRecord->canDelete())
                <form action="{{ route('mobile.returns.destroy', $returnRecord->id) }}" method="POST" 
                      onsubmit="return confirm('<x-lang key="mobile.returns.confirm_delete"/>')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-4 bg-red-500 text-white font-semibold rounded-lg shadow-lg hover:bg-red-600 transition-colors">
                        <i class="bi bi-trash mr-2"></i>
                        <x-lang key="mobile.returns.delete_record"/>
                    </button>
                </form>
            @endif
        </div>
    </form>
    <div class="h-24"></div>
</div>

@push('scripts')
<script>
function selectFromGalleryReturnEdit() {
    const input = document.getElementById('returnEditImageInput');
    input.removeAttribute('capture');
    input.click();
}

function takePhotoReturnEdit() {
    const input = document.getElementById('returnEditImageInput');
    input.setAttribute('capture', 'environment');
    input.click();
}

function removeReturnEditImage() {
    const input = document.getElementById('returnEditImageInput');
    const preview = document.getElementById('return-edit-image-preview');
    const info = document.getElementById('return-edit-image-info');
    
    input.value = '';
    preview.classList.add('hidden');
    info.innerHTML = '';
}

document.getElementById('returnEditImageInput').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById('return-edit-image-preview');
            const info = document.getElementById('return-edit-image-info');
            
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('hidden');
            
            // 显示文件信息
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            info.innerHTML = `文件名: ${file.name}<br>大小: ${fileSize} MB<br>类型: ${file.type}`;
        }
        
        reader.readAsDataURL(file);
    }
});

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
