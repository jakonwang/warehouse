@extends('layouts.app')

@section('title', '编辑仓库')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">编辑仓库</h1>
                    <p class="mt-2 text-gray-600">修改仓库信息</p>
                </div>
                <a href="{{ route('stores.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    <i class="bi bi-arrow-left mr-2"></i>返回列表
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
            <form action="{{ route('stores.update', $store) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 仓库名称 -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">仓库名称</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $store->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="请输入仓库名称"
                               required>
                    </div>

                    <!-- 仓库编码 -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">仓库编码</label>
                        <input type="text" 
                               id="code" 
                               name="code" 
                               value="{{ old('code', $store->code) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="请输入仓库编码"
                               required>
                    </div>

                    <!-- 仓库状态 -->
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">仓库状态</label>
                        <select id="is_active" 
                                name="is_active"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="1" {{ old('is_active', $store->is_active) ? 'selected' : '' }}>启用</option>
                            <option value="0" {{ old('is_active', $store->is_active) ? '' : 'selected' }}>禁用</option>
                        </select>
                    </div>

                    <!-- 仓库描述 -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">仓库描述</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="请输入仓库描述">{{ old('description', $store->description) }}</textarea>
                    </div>
                </div>

                <!-- 提交按钮 -->
                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ route('stores.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        取消
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        保存修改
                    </button>
                </div>
            </form>
        </div>

        <!-- 仓库信息卡片 -->
        <div class="mt-8 bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">仓库信息</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="bi bi-building text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900">基本信息</h4>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div><span class="font-medium">名称：</span>{{ $store->name }}</div>
                        <div><span class="font-medium">编码：</span>{{ $store->code }}</div>
                        <div><span class="font-medium">状态：</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $store->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $store->is_active ? '启用' : '禁用' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="bi bi-box text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900">商品分配</h4>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div><span class="font-medium">已分配商品：</span>{{ $store->availableProducts->count() }} 个</div>
                        <div><span class="font-medium">标品数量：</span>{{ $store->availableProducts->where('type', 'standard')->count() }} 个</div>
                        <div><span class="font-medium">盲袋数量：</span>{{ $store->availableProducts->where('type', 'blind_bag')->count() }} 个</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="bi bi-people text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900">用户权限</h4>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div><span class="font-medium">授权用户：</span>{{ $store->users->count() }} 人</div>
                        <div><span class="font-medium">创建时间：</span>{{ $store->created_at->format('Y-m-d') }}</div>
                        <div><span class="font-medium">更新时间：</span>{{ $store->updated_at->format('Y-m-d') }}</div>
                    </div>
                </div>
            </div>

            @if($store->description)
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">仓库描述</h4>
                    <p class="text-gray-600">{{ $store->description }}</p>
                </div>
            @endif
        </div>

        <!-- 快捷操作 -->
        <div class="mt-8 bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">快捷操作</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('store-products.show', $store) }}" 
                   class="bg-blue-500 text-white px-4 py-3 rounded-lg text-center hover:bg-blue-600 transition-colors">
                    <i class="bi bi-box text-xl mb-2"></i>
                    <div class="font-medium">管理商品</div>
                    <div class="text-sm opacity-90">分配商品到仓库</div>
                </a>

                <a href="{{ route('stores.users', $store) }}" 
                   class="bg-green-500 text-white px-4 py-3 rounded-lg text-center hover:bg-green-600 transition-colors">
                    <i class="bi bi-people text-xl mb-2"></i>
                    <div class="font-medium">用户管理</div>
                    <div class="text-sm opacity-90">管理仓库用户权限</div>
                </a>

                <a href="{{ route('stock-ins.create') }}?store_id={{ $store->id }}" 
                   class="bg-purple-500 text-white px-4 py-3 rounded-lg text-center hover:bg-purple-600 transition-colors">
                    <i class="bi bi-arrow-down-circle text-xl mb-2"></i>
                    <div class="font-medium">入库管理</div>
                    <div class="text-sm opacity-90">为该仓库入库</div>
                </a>

                <a href="{{ route('sales.create') }}" 
                   class="bg-orange-500 text-white px-4 py-3 rounded-lg text-center hover:bg-orange-600 transition-colors">
                    <i class="bi bi-cart text-xl mb-2"></i>
                    <div class="font-medium">销售记录</div>
                    <div class="text-sm opacity-90">创建销售记录</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 