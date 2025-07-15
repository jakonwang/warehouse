@extends('layouts.app')

@section('title', '仓库商品分配管理')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">仓库商品分配管理</h1>
                    <p class="mt-2 text-gray-600">管理各仓库可销售的商品分配</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('store-products.batch-assign') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                        <i class="bi bi-plus-circle mr-2"></i>批量分配
                    </a>
                    <a href="{{ route('stores.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <i class="bi bi-arrow-left mr-2"></i>返回仓库管理
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
            <!-- 仓库列表 -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">仓库列表</h3>
                    <p class="text-sm text-gray-600">点击仓库查看和管理商品分配</p>
                </div>

                <div class="space-y-4">
                    @foreach($stores as $store)
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-building text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $store->name }}</h4>
                                        <p class="text-sm text-gray-500">{{ $store->code }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">{{ $store->available_products_count }}</span> 个商品
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        已分配 {{ $store->store_products_count }} 个
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('store-products.show', $store) }}" 
                                   class="flex-1 bg-blue-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors text-center">
                                    管理商品
                                </a>
                                <a href="{{ route('stores.show', $store) }}" 
                                   class="bg-gray-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-600 transition-colors">
                                    查看详情
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 商品列表 -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">商品列表</h3>
                    <p class="text-sm text-gray-600">查看商品在各仓库的分配情况</p>
                </div>

                <div class="space-y-4">
                    @foreach($products as $product)
                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-box text-white"></i>
                                    </div>
                                    <div>
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
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">
                                        已分配到 <span class="font-medium">{{ $product->available_stores_count }}</span> 个仓库
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 统计信息 -->
        <div class="mt-8 bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">统计信息</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stores->count() }}</div>
                    <div class="text-sm text-gray-600">总仓库数</div>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $products->count() }}</div>
                    <div class="text-sm text-gray-600">总商品数</div>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $products->where('type', 'standard')->count() }}</div>
                    <div class="text-sm text-gray-600">标品数量</div>
                </div>
                
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $products->where('type', 'blind_bag')->count() }}</div>
                    <div class="text-sm text-gray-600">盲袋数量</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 