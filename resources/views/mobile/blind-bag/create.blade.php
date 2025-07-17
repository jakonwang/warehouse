@extends('layouts.mobile')

@section('title', __('messages.mobile.blind_bag.title'))

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-40">
    <!-- {{ __('messages.mobile.blind_bag.top_navigation') }} -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('mobile.sales.index') }}" class="text-gray-600">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <h1 class="text-xl font-semibold text-gray-900">🎲 <x-lang key="messages.mobile.sales.blind_bag_details.title"/></h1>
            <div class="w-6"></div>
        </div>
        <p class="text-gray-600"><x-lang key="messages.mobile.sales.blind_bag_details.subtitle"/></p>
    </div>

    @if ($errors->any())
        <div class="card p-4 border-l-4 border-red-500 bg-red-50">
            <ul class="text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="card p-4 border-l-4 border-red-500 bg-red-50">
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <form action="{{ route('mobile.blind-bag.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- {{ __('messages.mobile.blind_bag.step1_title') }} -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🛍️ <x-lang key="messages.mobile.sales.blind_bag_details.step1_title"/></h2>
            @if($blindBagProducts->count() > 0)
                <div class="grid grid-cols-2 gap-4">
                    @foreach($blindBagProducts as $product)
                    <label class="relative">
                        <input type="radio" name="blind_bag_product_id" value="{{ $product->id }}" 
                               class="blind-bag-radio hidden" data-price="{{ $product->price }}">
                        <div class="blind-bag-card border-2 border-gray-200 rounded-lg p-4 cursor-pointer transition-all hover:border-blue-300">
                            <div class="text-center">
                                <div class="text-2xl mb-2">🎁</div>
                                <div class="text-lg font-bold text-gray-900">{{ $product->name }}</div>
                                <div class="text-xl font-bold text-green-600">¥{{ $product->price }}</div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-4xl mb-4">📦</div>
                    <p class="text-gray-600"><x-lang key="messages.mobile.sales.blind_bag_details.no_products"/></p>
                    <p class="text-sm text-gray-500 mt-2"><x-lang key="messages.mobile.sales.blind_bag_details.create_in_backend"/></p>
                </div>
            @endif
        </div>

        <!-- {{ __('messages.mobile.blind_bag.step2_title') }} -->
        <div id="delivery-content" class="card p-6" style="display: none;">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📦 <x-lang key="messages.mobile.sales.blind_bag_details.step2_title"/></h2>
            <p class="text-sm text-gray-600 mb-4"><x-lang key="messages.mobile.sales.blind_bag_details.step2_desc"/></p>
            
            @if($availableProducts->count() > 0)
                <div class="space-y-4">
                    @foreach($availableProducts as $product)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <span class="text-lg font-bold text-gray-900">{{ $product->name }}</span>
                                <p class="text-sm text-gray-500"><x-lang key="messages.mobile.sales.blind_bag_details.cost"/>: ¥{{ $product->cost_price }} | <x-lang key="messages.mobile.sales.blind_bag_details.stock"/>: <span class="stock-display" data-product="{{ $product->id }}">{{ $product->stock }}</span></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-center space-x-3">
                            <button type="button" 
                                    class="delivery-btn minus w-10 h-10 flex items-center justify-center bg-red-100 rounded-full text-red-600 hover:bg-red-200 transition-colors"
                                    data-target="delivery_{{ $product->id }}">
                                <i class="bi bi-dash text-lg"></i>
                            </button>
                            
                            <div class="text-center">
                                <input type="number" 
                                       name="delivery_content[{{ $product->id }}]" 
                                       id="delivery_{{ $product->id }}"
                                       value="0"
                                       class="delivery-input w-16 text-center text-xl font-bold bg-white border-2 border-gray-300 rounded-lg py-2 focus:border-blue-500 focus:outline-none" 
                                       min="0" step="1"
                                       max="{{ $product->stock }}"
                                       data-cost="{{ $product->cost_price }}"
                                       data-name="{{ $product->name }}"
                                       data-product="{{ $product->id }}">
                                <p class="text-xs text-gray-500 mt-1"><x-lang key="messages.mobile.sales.blind_bag_details.delivery_quantity"/></p>
                            </div>
                            
                            <button type="button" 
                                    class="delivery-btn plus w-10 h-10 flex items-center justify-center bg-green-100 rounded-full text-green-600 hover:bg-green-200 transition-colors"
                                    data-target="delivery_{{ $product->id }}">
                                <i class="bi bi-plus text-lg"></i>
                            </button>
                        </div>
                        
                        <!-- {{ __('messages.mobile.blind_bag.subtotal_display') }} -->
                        <div class="mt-3 pt-3 border-t border-gray-200 text-center">
                            <span class="text-sm text-gray-600"><x-lang key="messages.mobile.sales.blind_bag_details.cost_subtotal"/>: </span>
                            <span id="cost_{{ $product->id }}" class="text-sm font-bold text-red-600">¥0.00</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-4xl mb-4">📭</div>
                    <p class="text-gray-600"><x-lang key="messages.mobile.sales.blind_bag_details.no_delivery_products"/></p>
                    <p class="text-sm text-gray-500 mt-2"><x-lang key="messages.mobile.sales.blind_bag_details.all_out_of_stock"/></p>
                </div>
            @endif
        </div>

        <!-- {{ __('messages.mobile.blind_bag.step3_title') }} -->
        <div id="profit-calculation" class="card p-6" style="display: none;">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 <x-lang key="messages.mobile.sales.blind_bag_details.step3_title"/></h2>
            
            <!-- {{ __('messages.mobile.blind_bag.delivery_summary') }} -->
            <div class="bg-blue-50 rounded-lg p-4 mb-4 border border-blue-200">
                <h3 class="text-sm font-semibold text-blue-900 mb-2"><x-lang key="messages.mobile.sales.blind_bag_details.delivery_content"/>：</h3>
                <div id="delivery-summary" class="text-sm text-blue-800">
                    <x-lang key="messages.mobile.sales.blind_bag_details.please_select_delivery"/>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-3 bg-green-50 rounded-lg border border-green-200">
                    <div id="sale_amount" class="text-lg font-bold text-green-600">¥0.00</div>
                    <p class="text-xs text-gray-500"><x-lang key="messages.mobile.sales.blind_bag_details.sale_revenue"/></p>
                </div>
                <div class="text-center p-3 bg-red-50 rounded-lg border border-red-200">
                    <div id="total_delivery_cost" class="text-lg font-bold text-red-600">¥0.00</div>
                    <p class="text-xs text-gray-500"><x-lang key="messages.mobile.sales.blind_bag_details.delivery_cost"/></p>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                    <div id="net_profit" class="text-lg font-bold text-purple-600">¥0.00</div>
                    <p class="text-xs text-gray-500"><x-lang key="messages.mobile.sales.blind_bag_details.net_profit"/></p>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <span class="text-sm text-gray-600"><x-lang key="messages.mobile.sales.blind_bag_details.profit_margin"/>: </span>
                <span id="profit_margin" class="text-lg font-bold text-orange-600">0%</span>
            </div>
        </div>

        <!-- {{ __('messages.mobile.blind_bag.customer_info') }} -->
        <div id="customer-info" class="card p-6" style="display: none;">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">👤 <x-lang key="messages.mobile.sales.blind_bag_details.customer_info"/></h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2"><x-lang key="messages.mobile.sales.blind_bag_details.customer_name"/></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" 
                           class="form-input w-full px-3 py-2 rounded-lg border" 
                           placeholder="<x-lang key="messages.mobile.sales.blind_bag_details.customer_name_placeholder"/>">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"><x-lang key="messages.mobile.blind_bag.customer_phone"/></label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" 
                           class="form-input w-full px-3 py-2 rounded-lg border" 
                           placeholder="<x-lang key="messages.mobile.blind_bag.customer_phone_placeholder"/>">
                </div>
            </div>
        </div>

        <!-- {{ __('messages.mobile.blind_bag.sales_photo') }} -->
        <div id="photo-section" class="card p-6" style="display: none;">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📷 <x-lang key="messages.mobile.blind_bag.sale_photo"/></h2>
            <div class="space-y-4">
                <input type="file" name="photo" id="photo" accept="image/*" capture="environment" class="hidden">
                <div id="photo-preview" class="hidden relative">
                    <img src="" alt="{{ __('messages.mobile.blind_bag.preview_image') }}" class="w-full h-48 object-cover rounded-lg">
                    <button type="button" id="remove-photo" class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center bg-red-500 rounded-full text-white hover:bg-red-600">
                        <i class="bi bi-x text-lg"></i>
                    </button>
                </div>
                <label for="photo" id="photo-upload-area" class="block w-full h-32 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-gray-400 transition-colors">
                    <i class="bi bi-camera text-3xl text-gray-400 mb-2"></i>
                    <span class="text-sm text-gray-600"><x-lang key="messages.mobile.blind_bag.click_to_photo"/></span>
                </label>
            </div>
        </div>

        <!-- {{ __('messages.mobile.blind_bag.submit_button') }} -->
        <div id="submit-section" class="card p-6" style="display: none;">
            <button type="submit" id="submit-btn" disabled class="btn-primary w-full py-4 text-white font-semibold rounded-lg shadow-lg opacity-50 cursor-not-allowed">
                <i class="bi bi-box-seam mr-2"></i>
                <x-lang key="messages.mobile.blind_bag.submit_button"/>
            </button>
                            <p class="text-xs text-gray-500 text-center mt-2"><x-lang key="messages.mobile.blind_bag.submit_hint"/></p>
        </div>

        <!-- {{ __('messages.mobile.blind_bag.hidden_fields') }} -->
        <input type="hidden" name="sale_amount" id="hidden_sale_amount" value="0">
        <input type="hidden" name="total_cost" id="hidden_total_cost" value="0">
        <input type="hidden" name="profit" id="hidden_profit" value="0">
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedBlindBagPrice = 0;
    let canSubmit = false;

    // {{ __('messages.mobile.blind_bag.select_blind_bag') }}
    document.querySelectorAll('.blind-bag-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            selectedBlindBagPrice = parseFloat(this.dataset.price);
            
            // {{ __('messages.mobile.blind_bag.update_ui_status') }}
            document.querySelectorAll('.blind-bag-card').forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50');
                card.classList.add('border-gray-200');
            });
            
            this.nextElementSibling.classList.remove('border-gray-200');
            this.nextElementSibling.classList.add('border-blue-500', 'bg-blue-50');
            
            // {{ __('messages.mobile.blind_bag.show_next_steps') }}
            document.getElementById('delivery-content').style.display = 'block';
            document.getElementById('profit-calculation').style.display = 'block';
            document.getElementById('customer-info').style.display = 'block';
            document.getElementById('photo-section').style.display = 'block';
            document.getElementById('submit-section').style.display = 'block';
            
            updateCalculations();
        });
    });

    // {{ __('messages.mobile.blind_bag.delivery_quantity_buttons') }}
    document.querySelectorAll('.delivery-btn').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const currentValue = parseInt(input.value) || 0;
            const maxValue = parseInt(input.getAttribute('max')) || 999;
            
            if (this.classList.contains('minus')) {
                input.value = Math.max(0, currentValue - 1);
            } else {
                input.value = Math.min(maxValue, currentValue + 1);
            }
            updateCalculations();
        });
    });

    // 发货数量输入框变化
    document.querySelectorAll('.delivery-input').forEach(input => {
        input.addEventListener('input', function() {
            const maxValue = parseInt(this.getAttribute('max')) || 999;
            const currentValue = parseInt(this.value) || 0;
            
            if (currentValue > maxValue) {
                this.value = maxValue;
            }
            
            updateCalculations();
        });
    });

    // 照片上传处理
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    const photoUploadArea = document.getElementById('photo-upload-area');
    const removePhotoBtn = document.getElementById('remove-photo');

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.querySelector('img').src = e.target.result;
                photoPreview.style.display = 'block';
                photoUploadArea.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    removePhotoBtn.addEventListener('click', function() {
        photoInput.value = '';
        photoPreview.style.display = 'none';
        photoUploadArea.style.display = 'block';
    });

    // 更新计算
    function updateCalculations() {
        if (selectedBlindBagPrice <= 0) return;

        let totalCost = 0;
        let deliverySummary = [];
        let hasDeliveryItems = false;

        document.querySelectorAll('.delivery-input').forEach(input => {
            const quantity = parseInt(input.value) || 0;
            if (quantity > 0) {
                hasDeliveryItems = true;
                const cost = parseFloat(input.dataset.cost) || 0;
                const name = input.dataset.name || '';
                const productId = input.dataset.product || '';
                
                const itemCost = quantity * cost;
                totalCost += itemCost;
                deliverySummary.push(`${name} x${quantity}`);
                
                // 更新单项成本显示
                document.getElementById(`cost_${productId}`).textContent = `¥${itemCost.toFixed(2)}`;
            } else {
                // 清空单项成本显示
                const productId = input.dataset.product || '';
                document.getElementById(`cost_${productId}`).textContent = '¥0.00';
            }
        });

        const profit = selectedBlindBagPrice - totalCost;
        const profitRate = selectedBlindBagPrice > 0 ? (profit / selectedBlindBagPrice) * 100 : 0;

        // 更新显示
        document.getElementById('sale_amount').textContent = `¥${selectedBlindBagPrice.toFixed(2)}`;
        document.getElementById('total_delivery_cost').textContent = `¥${totalCost.toFixed(2)}`;
        document.getElementById('net_profit').textContent = `¥${profit.toFixed(2)}`;
        document.getElementById('profit_margin').textContent = `${profitRate.toFixed(1)}%`;
        
        document.getElementById('delivery-summary').textContent = 
            deliverySummary.length > 0 ? deliverySummary.join(', ') : '请先选择发货内容';

        // 更新隐藏字段
        document.getElementById('hidden_sale_amount').value = selectedBlindBagPrice.toFixed(2);
        document.getElementById('hidden_total_cost').value = totalCost.toFixed(2);
        document.getElementById('hidden_profit').value = profit.toFixed(2);

        // 更新提交按钮状态
        canSubmit = hasDeliveryItems && selectedBlindBagPrice > 0;
        const submitBtn = document.getElementById('submit-btn');
        
        if (canSubmit) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.classList.add('hover:bg-blue-700');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitBtn.classList.remove('hover:bg-blue-700');
        }
    }
});
</script>
@endsection 