@extends('layouts.app')

@section('title', $store->name . ' - 商品分配管理')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">{{ $store->name }}</h1>
                    <p class="mt-2 text-gray-600">商品分配管理 - {{ $store->code }}</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('store-products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <i class="bi bi-arrow-left mr-2"></i>返回列表
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- 当前分配的商品 -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">当前分配的商品</h3>
                    <p class="text-sm text-gray-600">该仓库可销售的商品列表</p>
                </div>

                @if($store->availableProducts->count() > 0)
                    <div class="space-y-4">
                        @foreach($store->availableProducts as $product)
                            <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover">
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                                                <span class="text-white font-bold text-sm">{{ substr($product->code, -2) }}</span>
                                            </div>
                                        @endif
                                        <div class="ml-3">
                                            <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                                            <p class="text-sm text-gray-500">
                                                ¥{{ number_format($product->price, 2) }}
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                    {{ $product->type === 'standard' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                    {{ $product->type === 'standard' ? '标品' : '盲袋' }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="removeProduct({{ $product->id }})" 
                                                class="text-red-600 hover:text-red-800 transition-colors">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-4">
                            <i class="bi bi-box text-4xl"></i>
                        </div>
                        <p class="text-gray-500">暂无分配的商品</p>
                    </div>
                @endif
            </div>

            <!-- 可分配的商品 -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">可分配的商品</h3>
                    <p class="text-sm text-gray-600">选择要分配给该仓库的商品</p>
                </div>

                <form action="{{ route('store-products.assign', $store) }}" method="POST" id="assignForm">
                    @csrf
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @foreach($allProducts as $product)
                            @php
                                $isAssigned = $store->availableProducts->contains($product->id);
                            @endphp
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 {{ $isAssigned ? 'opacity-50' : '' }}">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="product_ids[]" 
                                           value="{{ $product->id }}" 
                                           id="product_{{ $product->id }}"
                                           {{ $isAssigned ? 'checked disabled' : '' }}
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                    <label for="product_{{ $product->id }}" class="ml-3 flex-1 cursor-pointer">
                                        <div class="flex items-center">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover">
                                            @else
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                                    <span class="text-white font-bold text-sm">{{ substr($product->code, -2) }}</span>
                                                </div>
                                            @endif
                                            <div class="ml-3">
                                                <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                                                <p class="text-sm text-gray-500">
                                                    ¥{{ number_format($product->price, 2) }}
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                        {{ $product->type === 'standard' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                        {{ $product->type === 'standard' ? '标品' : '盲袋' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            确认分配
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 统计信息 -->
        <div class="mt-8 bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">分配统计</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $store->availableProducts->count() }}</div>
                    <div class="text-sm text-gray-600">已分配商品</div>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $store->availableProducts->where('type', 'standard')->count() }}</div>
                    <div class="text-sm text-gray-600">标品数量</div>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $store->availableProducts->where('type', 'blind_bag')->count() }}</div>
                    <div class="text-sm text-gray-600">盲袋数量</div>
                </div>
                
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $allProducts->count() }}</div>
                    <div class="text-sm text-gray-600">可选商品总数</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function removeProduct(productId) {
    if (confirm('确定要移除这个商品吗？')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("store-products.remove", $store) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const productInput = document.createElement('input');
        productInput.type = 'hidden';
        productInput.name = 'product_ids[]';
        productInput.value = productId;
        
        form.appendChild(csrfToken);
        form.appendChild(productInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection 