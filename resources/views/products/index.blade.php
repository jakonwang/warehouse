@extends('layouts.app')

@section('title', __('messages.products.title'))

@section('content')
<div class="min-h-screen bg-gray-50 py-8" x-data="productManager()" @open-add-modal="showAddModal = true">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 页面标题 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><x-lang key="messages.products.title"/></h1>
            <p class="mt-2 text-gray-600"><x-lang key="messages.products.subtitle"/></p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                <div class="flex items-center">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-box text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-blue-100 text-sm"><x-lang key="messages.products.total_products"/></p>
                        <p class="text-2xl font-bold">{{ $products->total() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                <div class="flex items-center">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-tag text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-green-100 text-sm"><x-lang key="messages.products.standard_products"/></p>
                        <p class="text-2xl font-bold">{{ $products->where('type', 'standard')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                <div class="flex items-center">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-gift text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-purple-100 text-sm"><x-lang key="messages.products.blind_bag_products"/></p>
                        <p class="text-2xl font-bold">{{ $products->where('type', 'blind_bag')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                <div class="flex items-center">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-currency-dollar text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-orange-100 text-sm"><x-lang key="messages.products.average_price"/></p>
                        <p class="text-2xl font-bold">¥{{ number_format($products->avg('price') ?? 0, 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 搜索和筛选工具栏 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col lg:flex-row gap-4 items-end">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- 搜索框 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><x-lang key="messages.products.search_products"/></label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="<x-lang key="messages.products.search_placeholder"/>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- 类型筛选 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><x-lang key="messages.products.product_type"/></label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value=""><x-lang key="messages.products.all_types"/></option>
                            <option value="standard" {{ request('type') === 'standard' ? 'selected' : '' }}><x-lang key="messages.products.standard_products"/></option>
                            <option value="blind_bag" {{ request('type') === 'blind_bag' ? 'selected' : '' }}><x-lang key="messages.products.blind_bag_products"/></option>
                        </select>
                    </div>

                    <!-- 状态筛选 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><x-lang key="messages.products.product_status"/></label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value=""><x-lang key="messages.products.all_status"/></option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}><x-lang key="messages.products.active"/></option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}><x-lang key="messages.products.inactive"/></option>
                        </select>
                    </div>

                    <!-- 排序 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><x-lang key="messages.products.sort_by"/></label>
                        <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="created_desc" {{ request('sort') === 'created_desc' ? 'selected' : '' }}><x-lang key="messages.products.latest_created"/></option>
                            <option value="created_asc" {{ request('sort') === 'created_asc' ? 'selected' : '' }}><x-lang key="messages.products.earliest_created"/></option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}><x-lang key="messages.products.name_asc"/></option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}><x-lang key="messages.products.name_desc"/></option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}><x-lang key="messages.products.price_asc"/></option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}><x-lang key="messages.products.price_desc"/></option>
                        </select>
                    </div>
                </div>

                <!-- 操作按钮 -->
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="bi bi-search mr-2"></i><x-lang key="messages.products.search"/>
                    </button>
                    <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <i class="bi bi-arrow-clockwise mr-2"></i><x-lang key="messages.products.reset"/>
                    </a>
                </div>
            </form>
        </div>

        <!-- 工具栏 -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <!-- 视图切换 -->
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button @click="viewMode = 'card'" 
                            :class="viewMode === 'card' ? 'bg-white shadow-sm' : ''"
                            class="px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        <i class="bi bi-grid mr-2"></i><x-lang key="messages.products.card_view"/>
                    </button>
                    <button @click="viewMode = 'table'" 
                            :class="viewMode === 'table' ? 'bg-white shadow-sm' : ''"
                            class="px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        <i class="bi bi-table mr-2"></i><x-lang key="messages.products.table_view"/>
                    </button>
                </div>
            </div>

            <!-- 添加商品按钮 -->
            @if(auth()->user()->canManageProducts())
                <button @click="showAddModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="bi bi-plus-circle mr-2"></i><x-lang key="messages.products.add_product"/>
                </button>
            @endif
        </div>

        <!-- 商品列表 -->
        @if($products->count() > 0)
            <!-- 卡片视图 -->
            <div x-show="viewMode === 'card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($products as $product)
                    <div class="group relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300">
                        <!-- 商品图片 -->
                        <div class="aspect-w-16 aspect-h-9 bg-gray-100">
                            @if($product->image)
                                <img src="{{ str_contains($product->image, 'uploads/') ? asset($product->image) : asset('storage/' . $product->image) }}" alt="{{ $product->name }}" 
                                     class="w-full h-48 object-cover" 
                                     onerror="this.parentElement.innerHTML='<div class=\'w-full h-48 bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center\'><i class=\'bi bi-image text-white text-4xl opacity-50\'></i></div>'">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br 
                                    @if($product->type === 'standard')
                                        from-blue-500 to-blue-600
                                    @else
                                        from-purple-500 to-purple-600
                                    @endif
                                    flex items-center justify-center">
                                    <i class="bi bi-image text-white text-4xl opacity-50"></i>
                                </div>
                            @endif
                            
                            <!-- 悬停操作按钮 -->
                            @if(auth()->user()->canManageProducts())
                                <div class="absolute top-3 right-3 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                    <button @click="editProduct({{ json_encode($product) }})" 
                                            class="p-2 bg-white/90 hover:bg-white rounded-lg shadow-sm transition-colors border border-gray-200">
                                        <i class="bi bi-pencil text-blue-600"></i>
                                    </button>
                                    <button onclick="deleteProduct({{ $product->id }})" 
                                            class="p-2 bg-white/90 hover:bg-white rounded-lg shadow-sm transition-colors border border-gray-200">
                                        <i class="bi bi-trash text-red-600"></i>
                                    </button>
                                </div>
                            @endif

                            <!-- 状态和类型标签 -->
                            <div class="absolute top-3 left-3 flex flex-col space-y-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($product->type === 'standard')
                                        bg-blue-100 text-blue-800
                                    @else
                                        bg-purple-100 text-purple-800
                                    @endif">
                                    {{ $product->type === 'standard' ? '标准商品' : '盲袋商品' }}
                                </span>
                                @if(!$product->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        已禁用
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- 商品信息 -->
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 line-clamp-1">{{ $product->name }}</h3>
                                    <p class="text-sm text-gray-500">编码: {{ $product->code }}</p>
                                </div>
                            </div>

                            <!-- 价格信息 -->
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">销售价格</span>
                                    <span class="text-lg font-bold text-gray-900">¥{{ number_format($product->price, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">成本价格</span>
                                    <span class="text-sm font-medium text-gray-700">¥{{ number_format($product->cost_price, 2) }}</span>
                                </div>
                                @if($product->type === 'standard')
                                    @if(auth()->user()->canViewProfitAndCost())
                                    @php
                                        $profitRate = $product->price > 0 ? (($product->price - $product->cost_price) / $product->price) * 100 : 0;
                                    @endphp
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">利润率</span>
                                        <span class="text-sm font-medium {{ $profitRate >= 30 ? 'text-green-600' : 'text-orange-600' }}">
                                            {{ number_format($profitRate, 1) }}%
                                        </span>
                                    </div>
                                    @endif
                                @endif
                            </div>

                            @if($product->category)
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        <i class="bi bi-tag mr-1"></i>
                                        {{ $product->category }}
                                    </span>
                                </div>
                            @endif
                            
                            @if($product->description)
                                <p class="text-sm text-gray-600 line-clamp-2">{{ $product->description }}</p>
                            @endif
                            
                            <!-- 底部操作按钮 -->
                            @if(auth()->user()->canManageProducts())
                                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end space-x-2">
                                    <button @click="editProduct({{ json_encode($product) }})" 
                                            class="px-3 py-1 text-sm bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-md transition-colors">
                                        <i class="bi bi-pencil mr-1"></i>编辑
                                    </button>
                                    <button onclick="deleteProduct({{ $product->id }})" 
                                            class="px-3 py-1 text-sm bg-red-50 hover:bg-red-100 text-red-600 rounded-md transition-colors">
                                        <i class="bi bi-trash mr-1"></i>删除
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- 表格视图 -->
            <div x-show="viewMode === 'table'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品信息</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">分类</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">类型</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">价格</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">成本</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">利润率</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($product->image)
                                                <img src="{{ str_contains($product->image, 'uploads/') ? asset($product->image) : asset('storage/' . $product->image) }}" alt="{{ $product->name }}" 
                                                     class="w-12 h-12 rounded-lg object-cover"
                                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDgiIGhlaWdodD0iNDgiIGZpbGw9IiNkMWQ1ZGIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTkgM0g1Yy0xLjEgMC0yIC45LTIgMnYxNGMwIDEuMS45IDIgMiAyaDE0YzEuMSAwIDItLjkgMi0yVjVjMC0xLjEtLjktMi0yLTJ6bTAgMTZINVY1aDE0djE0em0tNS04LjVjMC0uODMtLjY3LTEuNS0xLjUtMS41UzExIDcuNjcgMTEgOC41czY3IDEuNSAxLjUgMS41IDEuNS0uNjcgMS41LTEuNXpNOSAxMWw0LjUgNiA0LjUtNi05eiIvPjwvc3ZnPg=='">
                                            @else
                                                <div class="w-12 h-12 bg-gradient-to-br 
                                                    @if($product->type === 'standard')
                                                        from-blue-500 to-blue-600
                                                    @else
                                                        from-purple-500 to-purple-600
                                                    @endif
                                                    rounded-lg flex items-center justify-center">
                                                    <i class="bi bi-image text-white text-lg opacity-50"></i>
                                                </div>
                                            @endif
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $product->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($product->category)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                <i class="bi bi-tag mr-1"></i>
                                                {{ $product->category }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">未分类</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($product->type === 'standard')
                                                bg-blue-100 text-blue-800
                                            @else
                                                bg-purple-100 text-purple-800
                                            @endif">
                                            {{ $product->type === 'standard' ? '标准商品' : '盲袋商品' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ¥{{ number_format($product->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ¥{{ number_format($product->cost_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($product->type === 'standard')
                                            @if(auth()->user()->canViewProfitAndCost())
                                            @php
                                                $profitRate = $product->price > 0 ? (($product->price - $product->cost_price) / $product->price) * 100 : 0;
                                            @endphp
                                            <span class="font-medium {{ $profitRate >= 30 ? 'text-green-600' : 'text-orange-600' }}">
                                                {{ number_format($profitRate, 1) }}%
                                            </span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($product->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                启用
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                禁用
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(auth()->user()->canManageProducts())
                                            <div class="flex space-x-2">
                                                <button @click="editProduct({{ json_encode($product) }})" 
                                                        class="text-blue-600 hover:text-blue-900">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button onclick="deleteProduct({{ $product->id }})" 
                                                        class="text-red-600 hover:text-red-900">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 分页 -->
            <div class="flex justify-center">
                {{ $products->appends(request()->query())->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 text-center py-12">
                <i class="bi bi-box text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">暂无商品</h3>
                <p class="text-gray-600 mb-4">开始添加您的第一个商品</p>
                @if(auth()->user()->canManageProducts())
                    <button @click="showAddModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        <i class="bi bi-plus-circle mr-2"></i>添加商品
                    </button>
                @endif
            </div>
        @endif

        <!-- 添加商品模态框 -->
        <div x-show="showAddModal" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeAddModal()"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">添加新商品</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品名称 <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" x-model="newProduct.name" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="例如：29元盲袋" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品编码</label>
                                    <input type="text" name="code" x-model="newProduct.code" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="留空将自动生成">
                                    <p class="mt-1 text-xs text-gray-500">留空将根据商品名称自动生成</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品类型 <span class="text-red-500">*</span></label>
                                    <select name="type" x-model="newProduct.type" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                        <option value="standard">标准商品</option>
                                        <option value="blind_bag">盲袋商品</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">销售价格 <span class="text-red-500">*</span></label>
                                        <input type="number" name="price" x-model="newProduct.price" step="0.01" min="0" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               placeholder="0.00" required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">成本价格</label>
                                        <input type="number" name="cost_price" x-model="newProduct.cost_price" step="0.01" min="0" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               placeholder="0.00">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">库存数量</label>
                                    <input type="number" name="stock" value="0" min="0" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="0">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">预警库存</label>
                                    <input type="number" name="alert_stock" value="10" min="0" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="10">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品分类</label>
                                    <select name="category" x-model="newProduct.category" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">请选择分类</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品图片</label>
                                    <input type="file" name="image" accept="image/*" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="mt-1 text-xs text-gray-500">支持JPG、PNG格式，文件大小不超过2MB</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品描述</label>
                                    <textarea name="description" x-model="newProduct.description" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                              placeholder="商品详细描述"></textarea>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" checked id="add_is_active" 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="add_is_active" class="ml-2 block text-sm text-gray-900">启用商品</label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                添加商品
                            </button>
                            <button type="button" @click="closeAddModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                取消
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 编辑商品模态框 -->
        <div x-show="editingItem" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeEditModal()"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form x-bind:action="`{{ route('products.update', '') }}/${editingItem?.id}`" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">编辑商品</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品名称 <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" x-model="editingItem?.name" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品类型</label>
                                    <select name="type" x-model="editingItem?.type" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                        <option value="standard">标准商品</option>
                                        <option value="blind_bag">盲袋商品</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">销售价格 <span class="text-red-500">*</span></label>
                                        <input type="number" name="price" x-model="editingItem?.price" step="0.01" min="0" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">成本价格</label>
                                        <input type="number" name="cost_price" x-model="editingItem?.cost_price" step="0.01" min="0" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">预警库存</label>
                                    <input type="number" name="alert_stock" x-model="editingItem?.alert_stock" min="0" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品分类</label>
                                    <select name="category" x-model="editingItem?.category" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">请选择分类</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品图片</label>
                                    <input type="file" name="image" accept="image/*" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="mt-1 text-xs text-gray-500">支持JPG、PNG格式，文件大小不超过2MB</p>
                                    <div x-show="editingItem?.image" class="mt-2">
                                        <img x-bind:src="editingItem?.image" alt="当前图片" class="w-20 h-20 object-cover rounded-lg">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品描述</label>
                                    <textarea name="description" x-model="editingItem?.description" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="is_active" :checked="editingItem?.is_active" value="1" id="edit_is_active" 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="edit_is_active" class="ml-2 block text-sm text-gray-900">启用商品</label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                更新商品
                            </button>
                            <button type="button" @click="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                取消
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productManager() {
    return {
        viewMode: 'card',
        showAddModal: false,
        editingItem: null,
        newProduct: { 
            name: '', 
            code: '', 
            type: 'standard', 
            price: '', 
            cost_price: '', 
            category: '', 
            description: '' 
        },
        
        resetForm() {
            this.newProduct = { 
                name: '', 
                code: '', 
                type: 'standard', 
                price: '', 
                cost_price: '', 
                category: '', 
                description: '' 
            };
        },
        
        closeAddModal() {
            this.showAddModal = false;
            this.resetForm();
        },
        
        editProduct(product) {
            this.editingItem = { 
                ...product,
                is_active: Boolean(product.is_active),
                image: product.image ? (product.image.includes('uploads/') ? '{{ asset('') }}' + product.image : '{{ asset('storage') }}/' + product.image) : null
            };
        },
        
        closeEditModal() {
            this.editingItem = null;
        }
    }
}

function deleteProduct(productId) {
    if (confirm('确定要删除这个商品吗？删除后无法恢复。')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/products/${productId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// 处理旧数据
@if(old())
document.addEventListener('DOMContentLoaded', function() {
    const productManager = window.Alpine.stores.productManager || {};
    if (productManager.newProduct) {
        productManager.newProduct.name = '{{ old('name') }}';
        productManager.newProduct.code = '{{ old('code') }}';
        productManager.newProduct.type = '{{ old('type') }}';
        productManager.newProduct.price = '{{ old('price') }}';
        productManager.newProduct.cost_price = '{{ old('cost_price') }}';
        productManager.newProduct.category = '{{ old('category') }}';
        productManager.newProduct.description = '{{ old('description') }}';
    }
});
@endif
</script>
@endpush
@endsection 