@extends('layouts.app')

@section('title', '编辑商品分类')

@section('content')
<div class="space-y-8" x-data="{ 
    isSubmitting: false,
    selectedIcon: 'bi-tag',
    icons: [
        'bi-tag', 'bi-box', 'bi-gift', 'bi-star', 'bi-heart', 'bi-diamond',
        'bi-lightning', 'bi-flower1', 'bi-cup-hot', 'bi-cart', 'bi-bag',
        'bi-archive', 'bi-briefcase', 'bi-umbrella', 'bi-sunglasses'
    ],
    submitForm() {
        this.isSubmitting = true;
        document.getElementById('categoryForm').submit();
    }
}">
    <!-- 高级页面头部 -->
    <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-purple-700 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-black bg-opacity-10"></div>
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white bg-opacity-10 rounded-full"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-white bg-opacity-10 rounded-full"></div>
        
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="bi bi-pencil-square text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-2">编辑商品分类</h1>
                        <p class="text-purple-100 text-lg">修改分类信息，支持层级结构管理</p>
                        <div class="flex items-center mt-3 space-x-4 text-sm">
                            <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full backdrop-blur-sm">
                                <i class="bi bi-layers mr-1"></i>层级管理
                            </span>
                            <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full backdrop-blur-sm">
                                <i class="bi bi-gear mr-1"></i>智能排序
                            </span>
                            <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full backdrop-blur-sm">
                                <i class="bi bi-shield-check mr-1"></i>权限控制
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('categories.index') }}" class="inline-flex items-center px-6 py-3 bg-white bg-opacity-20 backdrop-blur-sm border border-white border-opacity-30 rounded-xl font-medium text-white hover:bg-white hover:bg-opacity-30 transition-all duration-200">
                        <i class="bi bi-arrow-left mr-2"></i>
                        返回列表
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-800 px-6 py-4 rounded-2xl shadow-sm">
            <div class="flex items-center">
                <i class="bi bi-check-circle text-green-600 mr-3"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 text-red-800 px-6 py-4 rounded-2xl shadow-sm">
            <div class="flex items-start">
                <i class="bi bi-exclamation-triangle text-red-600 mr-3 mt-0.5"></i>
                <div>
                    <p class="font-medium mb-2">请修正以下错误：</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- 主要内容区域 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- 表单卡片 -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-xl font-semibold text-gray-900">分类信息</h3>
                    <p class="text-gray-600 mt-1">修改分类的基本信息和属性</p>
                </div>
                
                <form id="categoryForm" action="{{ route('categories.update', $category) }}" method="POST" class="p-8 space-y-8">
                    @csrf
                    @method('PUT')
                    
                    <!-- 分类名称 -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-3">
                            分类名称 <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $category->name) }}" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('name') border-red-300 ring-red-200 @enderror"
                                   placeholder="例如：盲袋商品、标准商品、限量版等">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i class="bi bi-tag text-gray-400"></i>
                            </div>
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="bi bi-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- 分类描述 -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-3">
                            分类描述
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('description') border-red-300 ring-red-200 @enderror"
                                  placeholder="详细描述这个分类的特点、用途和适用范围...">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="bi bi-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- 父级分类 -->
                    <div>
                        <label for="parent_id" class="block text-sm font-semibold text-gray-700 mb-3">
                            父级分类
                        </label>
                        <div class="relative">
                            <select id="parent_id" 
                                    name="parent_id"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('parent_id') border-red-300 ring-red-200 @enderror">
                                <option value="">无（顶级分类）</option>
                                @foreach($categories as $parent)
                                    @if($parent->id != $category->id)
                                        <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i class="bi bi-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                        @error('parent_id')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="bi bi-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- 排序权重 -->
                    <div>
                        <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-3">
                            排序权重
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', $category->sort_order) }}"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('sort_order') border-red-300 ring-red-200 @enderror"
                                   placeholder="数字越小排序越靠前">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">权重</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">数字越小，在列表中排序越靠前</p>
                        @error('sort_order')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="bi bi-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- 启用状态 -->
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                        <div>
                            <h4 class="font-semibold text-gray-900">启用此分类</h4>
                            <p class="text-sm text-gray-600 mt-1">启用后分类将显示在商品管理界面中</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- 提交按钮 -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-100">
                        <a href="{{ route('categories.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-all duration-200">
                            取消
                        </a>
                        <button type="submit" 
                                @click="submitForm()"
                                :disabled="isSubmitting"
                                class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="bi bi-check-circle mr-2"></i>
                            <span x-text="isSubmitting ? '更新中...' : '更新分类'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 侧边栏 -->
        <div class="space-y-6">
            <!-- 分类图标选择 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">分类图标</h4>
                <div class="grid grid-cols-4 gap-3">
                    <template x-for="icon in icons" :key="icon">
                        <button @click="selectedIcon = icon" 
                                :class="selectedIcon === icon ? 'bg-purple-100 border-purple-500' : 'bg-gray-50 border-gray-200 hover:bg-gray-100'"
                                class="p-3 border-2 rounded-xl transition-all duration-200">
                            <i :class="icon" class="text-xl"></i>
                        </button>
                    </template>
                </div>
                <p class="text-sm text-gray-500 mt-3">选择分类的显示图标</p>
            </div>

            <!-- 分类信息 -->
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-100">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">分类信息</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">创建时间</span>
                        <span class="text-sm font-medium text-gray-900">{{ $category->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">最后更新</span>
                        <span class="text-sm font-medium text-gray-900">{{ $category->updated_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">排序权重</span>
                        <span class="text-sm font-medium text-purple-600">{{ $category->sort_order }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">状态</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $category->is_active ? '启用' : '禁用' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 操作历史 -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">操作历史</h4>
                <div class="space-y-3 text-sm text-gray-600">
                    <div class="flex items-start">
                        <i class="bi bi-clock-history text-blue-500 mr-2 mt-0.5"></i>
                        <p>创建于 {{ $category->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-start">
                        <i class="bi bi-pencil text-green-500 mr-2 mt-0.5"></i>
                        <p>最后编辑于 {{ $category->updated_at->diffForHumans() }}</p>
                    </div>
                    @if($category->parent)
                        <div class="flex items-start">
                            <i class="bi bi-diagram-3 text-purple-500 mr-2 mt-0.5"></i>
                            <p>父级分类：{{ $category->parent->name }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 编辑提示 -->
            <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl p-6 border border-yellow-100">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">编辑提示</h4>
                <div class="space-y-3 text-sm text-gray-600">
                    <div class="flex items-start">
                        <i class="bi bi-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                        <p>修改分类名称可能影响已关联的商品</p>
                    </div>
                    <div class="flex items-start">
                        <i class="bi bi-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                        <p>调整排序权重会立即影响显示顺序</p>
                    </div>
                    <div class="flex items-start">
                        <i class="bi bi-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                        <p>禁用分类会隐藏相关商品</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 