@extends('layouts.app')

@section('title', '仓库详情')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">仓库详情</h1>
                    <p class="mt-2 text-gray-600">{{ $store->name }} - {{ $store->code }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('stores.edit', $store) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="bi bi-pencil mr-2"></i>编辑仓库
                    </a>
                    <a href="{{ route('stores.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <i class="bi bi-arrow-left mr-2"></i>返回列表
                    </a>
                </div>
            </div>
        </div>

        <!-- 仓库基本信息 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- 左侧：基本信息 -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 基本信息卡片 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mr-4">
                            <i class="bi bi-building text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $store->name }}</h3>
                            <p class="text-gray-600">编码：{{ $store->code }}</p>
                        </div>
                        <div class="ml-auto">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $store->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $store->is_active ? '启用' : '禁用' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">仓库名称</label>
                            <p class="text-gray-900">{{ $store->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">仓库编码</label>
                            <p class="text-gray-900">{{ $store->code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">创建时间</label>
                            <p class="text-gray-900">{{ $store->created_at ? $store->created_at->format('Y-m-d H:i:s') : '未知' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">更新时间</label>
                            <p class="text-gray-900">{{ $store->updated_at ? $store->updated_at->format('Y-m-d H:i:s') : '未知' }}</p>
                        </div>
                        @if($store->description)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">仓库描述</label>
                            <p class="text-gray-900">{{ $store->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 商品统计 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mr-4">
                            <i class="bi bi-box text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">商品统计</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="bi bi-box text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">总商品数</h4>
                            </div>
                            <div class="text-2xl font-bold text-blue-600">{{ $store->availableProducts->count() }}</div>
                            <p class="text-sm text-gray-600">已分配商品</p>
                        </div>

                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="bi bi-tag text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">标品数量</h4>
                            </div>
                            <div class="text-2xl font-bold text-green-600">{{ $store->availableProducts->where('type', 'standard')->count() }}</div>
                            <p class="text-sm text-gray-600">标准商品</p>
                        </div>

                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="bi bi-gift text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">盲袋数量</h4>
                            </div>
                            <div class="text-2xl font-bold text-purple-600">{{ $store->availableProducts->where('type', 'blind_bag')->count() }}</div>
                            <p class="text-sm text-gray-600">盲袋商品</p>
                        </div>
                    </div>

                    @if($store->availableProducts->count() > 0)
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-900 mb-3">最近分配的商品</h4>
                        <div class="space-y-2">
                            @foreach($store->availableProducts->take(5) as $product)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-box text-gray-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $product->type == 'standard' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $product->type == 'standard' ? '标品' : '盲袋' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- 用户权限 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center mr-4">
                            <i class="bi bi-people text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">用户权限</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">授权用户</h4>
                            </div>
                            <div class="text-2xl font-bold text-purple-600">{{ $store->users->count() }}</div>
                            <p class="text-sm text-gray-600">有权限的用户</p>
                        </div>

                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="bi bi-shield text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">权限级别</h4>
                            </div>
                            <div class="text-2xl font-bold text-orange-600">仓库级</div>
                            <p class="text-sm text-gray-600">独立权限管理</p>
                        </div>
                    </div>

                    @if($store->users->count() > 0)
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-900 mb-3">授权用户列表</h4>
                        <div class="space-y-2">
                            @foreach($store->users->take(5) as $user)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-person text-gray-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $user->real_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">{{ $user->pivot->created_at ? $user->pivot->created_at->format('Y-m-d') : '未知' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 右侧：快捷操作 -->
            <div class="space-y-6">
                <!-- 快捷操作 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">快捷操作</h3>
                    <div class="space-y-3">
                        <a href="{{ route('store-products.show', $store) }}" 
                           class="w-full flex items-center p-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="bi bi-box text-xl mr-3"></i>
                            <div>
                                <div class="font-medium">管理商品</div>
                                <div class="text-sm opacity-90">分配商品到仓库</div>
                            </div>
                        </a>

                        <a href="{{ route('stores.users', $store) }}" 
                           class="w-full flex items-center p-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                            <i class="bi bi-people text-xl mr-3"></i>
                            <div>
                                <div class="font-medium">用户管理</div>
                                <div class="text-sm opacity-90">管理仓库用户权限</div>
                            </div>
                        </a>

                        <a href="{{ route('stock-ins.create') }}?store_id={{ $store->id }}" 
                           class="w-full flex items-center p-3 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                            <i class="bi bi-arrow-down-circle text-xl mr-3"></i>
                            <div>
                                <div class="font-medium">入库管理</div>
                                <div class="text-sm opacity-90">为该仓库入库</div>
                            </div>
                        </a>

                        <a href="{{ route('sales.create') }}?store_id={{ $store->id }}" 
                           class="w-full flex items-center p-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="bi bi-cart text-xl mr-3"></i>
                            <div>
                                <div class="font-medium">销售记录</div>
                                <div class="text-sm opacity-90">创建销售记录</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- 仓库状态 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">仓库状态</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">仓库状态</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $store->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $store->is_active ? '启用' : '禁用' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">商品数量</span>
                            <span class="font-medium text-gray-900">{{ $store->availableProducts->count() }} 个</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">授权用户</span>
                            <span class="font-medium text-gray-900">{{ $store->users->count() }} 人</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">创建时间</span>
                            <span class="font-medium text-gray-900">{{ $store->created_at ? $store->created_at->format('Y-m-d') : '未知' }}</span>
                        </div>
                    </div>
                </div>

                <!-- 最近活动 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">最近活动</h3>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">仓库创建</div>
                                <div class="text-xs text-gray-500">{{ $store->created_at ? $store->created_at->format('Y-m-d H:i') : '未知' }}</div>
                            </div>
                        </div>
                        @if($store->availableProducts->count() > 0)
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">商品分配</div>
                                <div class="text-xs text-gray-500">分配了 {{ $store->availableProducts->count() }} 个商品</div>
                            </div>
                        </div>
                        @endif
                        @if($store->users->count() > 0)
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">用户授权</div>
                                <div class="text-xs text-gray-500">授权了 {{ $store->users->count() }} 个用户</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 