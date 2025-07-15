@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- 标题 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">📦 <x-lang key="messages.mobile.inventory.title"/></h1>
        <p class="text-gray-600"><x-lang key="messages.mobile.inventory.subtitle"/></p>
    </div>

    @if($inventory->count() > 0)
        <!-- 库存概览 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🛍️ <x-lang key="messages.mobile.inventory.product_inventory"/></h2>
            <div class="space-y-4">
                @foreach($inventory as $item)
                    @php
                        $isLowStock = $item->quantity <= $item->min_quantity;
                        $isOutOfStock = $item->quantity == 0;
                    @endphp
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-4 border {{ $isOutOfStock ? 'border-red-300 bg-red-50' : ($isLowStock ? 'border-orange-300 bg-orange-50' : 'border-blue-200') }}">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-sm font-medium text-gray-700">{{ $item->product->name }}</span>
                                @if($item->store)
                                    <div class="text-xs text-gray-500">{{ $item->store->name }}</div>
                                @endif
                            </div>
                            @if($isOutOfStock)
                                <span class="badge-error text-xs px-2 py-1 rounded-full">
                                    <i class="bi bi-exclamation-triangle mr-1"></i><x-lang key="messages.mobile.inventory.out_of_stock"/>
                                </span>
                            @elseif($isLowStock)
                                <span class="badge-warning text-xs px-2 py-1 rounded-full">
                                    <i class="bi bi-exclamation-triangle mr-1"></i><x-lang key="messages.mobile.inventory.low_stock"/>
                                </span>
                            @else
                                <span class="badge-success text-xs px-2 py-1 rounded-full">
                                    <i class="bi bi-check-circle mr-1"></i><x-lang key="messages.mobile.inventory.normal"/>
                                </span>
                            @endif
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold {{ $isOutOfStock ? 'text-red-600' : ($isLowStock ? 'text-orange-600' : 'text-indigo-600') }} mb-1">
                                {{ $item->quantity }}
                            </div>
                            <p class="text-xs text-gray-500"><x-lang key="messages.mobile.inventory.current_stock"/></p>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex justify-between text-xs text-gray-500">
                                <span><x-lang key="messages.mobile.inventory.cost"/>: ¥{{ number_format($item->product->cost_price, 2) }}</span>
                                <span><x-lang key="messages.mobile.inventory.price"/>: ¥{{ number_format($item->product->price, 2) }}</span>
                            </div>
                            @if($item->min_quantity > 0)
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span><x-lang key="messages.mobile.inventory.warning_value"/>: {{ $item->min_quantity }}</span>
                                <span><x-lang key="messages.mobile.inventory.max_value"/>: {{ $item->max_quantity }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 库存统计 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📊 <x-lang key="messages.mobile.inventory.inventory_stats"/></h2>
            <div class="grid grid-cols-2 gap-4">
                @php
                    $totalQuantity = $inventory->sum('quantity');
                    $totalValue = $inventory->sum(function($item) {
                        return $item->quantity * $item->product->price;
                    });
                    $totalCost = $inventory->sum(function($item) {
                        return $item->quantity * $item->product->cost_price;
                    });
                    $lowStockCount = $inventory->filter(function($item) {
                        return $item->quantity <= $item->min_quantity && $item->quantity > 0;
                    })->count();
                    $outOfStockCount = $inventory->filter(function($item) {
                        return $item->quantity == 0;
                    })->count();
                @endphp

                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                    <div class="text-center">
                        <div class="text-xl font-bold text-green-600 mb-1">{{ $totalQuantity }}</div>
                        <p class="text-xs text-gray-600"><x-lang key="messages.mobile.inventory.total_quantity"/></p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200">
                    <div class="text-center">
                        <div class="text-xl font-bold text-blue-600 mb-1">¥{{ number_format($totalValue, 0) }}</div>
                        <p class="text-xs text-gray-600"><x-lang key="messages.mobile.inventory.total_value"/></p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-lg p-4 border border-purple-200">
                    <div class="text-center">
                        <div class="text-xl font-bold text-purple-600 mb-1">¥{{ number_format($totalCost, 0) }}</div>
                        <p class="text-xs text-gray-600"><x-lang key="messages.mobile.inventory.total_cost"/></p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-lg p-4 border border-orange-200">
                    <div class="text-center">
                        <div class="text-xl font-bold text-orange-600 mb-1">{{ $lowStockCount }}/{{ $outOfStockCount }}</div>
                        <p class="text-xs text-gray-600"><x-lang key="messages.mobile.inventory.warning_out_of_stock"/></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 快捷操作 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">⚡ <x-lang key="messages.mobile.inventory.quick_actions"/></h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('mobile.stock-in.index') }}" 
                   class="flex items-center justify-center p-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="bi bi-box-arrow-in-down text-xl mr-2"></i>
                    <span class="font-semibold"><x-lang key="messages.mobile.inventory.stock_in"/></span>
                </a>

                <a href="{{ route('mobile.sales.create') }}" 
                   class="flex items-center justify-center p-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="bi bi-cart-plus text-xl mr-2"></i>
                    <span class="font-semibold"><x-lang key="messages.mobile.inventory.sales_record"/></span>
                </a>

                <a href="{{ route('mobile.returns.index') }}" 
                   class="flex items-center justify-center p-4 bg-gradient-to-r from-orange-500 to-amber-600 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="bi bi-arrow-return-left text-xl mr-2"></i>
                    <span class="font-semibold"><x-lang key="messages.mobile.inventory.process_return"/></span>
                </a>

                <button onclick="refreshInventory()" 
                        class="flex items-center justify-center p-4 bg-gradient-to-r from-purple-500 to-violet-600 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="bi bi-arrow-clockwise text-xl mr-2"></i>
                    <span class="font-semibold"><x-lang key="messages.mobile.inventory.refresh_data"/></span>
                </button>
            </div>
        </div>
    @else
        <!-- 空状态 -->
        <div class="card p-8 text-center">
            <div class="text-6xl mb-4">📭</div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2"><x-lang key="messages.mobile.inventory.no_data"/></h3>
            <p class="text-gray-600 mb-6"><x-lang key="messages.mobile.inventory.please_stock_in"/></p>
            <a href="{{ route('mobile.stock-in.index') }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                <i class="bi bi-box-arrow-in-down mr-2"></i>
                <x-lang key="messages.mobile.inventory.stock_in_now"/>
            </a>
        </div>
    @endif
    <div class="h-24"></div>
</div>

<style>
.badge-success {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
}

.badge-warning {
    background: rgba(245, 158, 11, 0.1);
    color: #F59E0B;
}

.badge-error {
    background: rgba(220, 38, 38, 0.1);
    color: #DC2626;
}
</style>

<script>
function refreshInventory() {
    // 显示加载提示
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-arrow-clockwise text-xl mr-2 animate-spin"></i><span class="font-semibold"><x-lang key="messages.mobile.inventory.refreshing"/></span>';
    button.disabled = true;
    
    // 刷新页面
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}
</script>
@endsection 