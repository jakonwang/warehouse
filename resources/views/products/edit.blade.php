@extends('layouts.app')

@section('title', '编辑商品')
@section('header', '编辑商品')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- 页面头部 -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">编辑商品</h1>
            <p class="text-gray-600 mt-1">修改商品信息和配置</p>
        </div>
        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
            <i class="bi bi-arrow-left mr-2"></i>
            返回列表
        </a>
    </div>

    <!-- 错误提示 -->
    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center mb-2">
                <i class="bi bi-exclamation-triangle text-red-600 mr-2"></i>
                <h3 class="text-red-800 font-medium">请修正以下错误：</h3>
            </div>
            <ul class="text-red-700 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 表单卡片 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- 左侧主要信息 -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- 基本信息 -->
                        <div class="bg-gray-50 rounded-lg p-5">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="bi bi-info-circle text-blue-600 mr-2"></i>
                                基本信息
                            </h2>
                            
                            <div class="space-y-4">
                                <!-- 商品名称 -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        商品名称 <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $product->name) }}" 
                                           required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-500 ring-2 ring-red-200 @enderror"
                                           placeholder="请输入商品名称">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- 商品编码（只读） -->
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                        商品编码
                                    </label>
                                    <input type="text" 
                                           id="code" 
                                           value="{{ $product->code }}" 
                                           disabled
                                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-500"
                                           placeholder="自动生成">
                                    <p class="mt-1 text-xs text-gray-500">商品编码创建后不可修改</p>
                                </div>

                                <!-- 商品类型 -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                        商品类型 <span class="text-red-500">*</span>
                                    </label>
                                    <select id="type" 
                                            name="type" 
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('type') border-red-500 ring-2 ring-red-200 @enderror">
                                        <option value="standard" {{ old('type', $product->type) == 'standard' ? 'selected' : '' }}>基础商品</option>
                                        <option value="blind_bag" {{ old('type', $product->type) == 'blind_bag' ? 'selected' : '' }}>盲袋商品</option>
                                    </select>
                                    @error('type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- 商品分类 -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                        商品分类
                                    </label>
                                    <select id="category" 
                                            name="category" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('category') border-red-500 ring-2 ring-red-200 @enderror">
                                        <option value="">请选择分类</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->name }}" {{ old('category', $product->category) == $category->name ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- 商品描述 -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                        商品描述
                                    </label>
                                    <textarea id="description" 
                                              name="description" 
                                              rows="3"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('description') border-red-500 ring-2 ring-red-200 @enderror"
                                              placeholder="请输入商品描述">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 价格信息 -->
                        <div class="bg-gray-50 rounded-lg p-5">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="bi bi-currency-dollar text-green-600 mr-2"></i>
                                价格信息
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- 售价 -->
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                        售价 <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">¥</span>
                                        <input type="number" 
                                               id="price" 
                                               name="price" 
                                               value="{{ old('price', $product->price) }}" 
                                               step="0.01" 
                                               min="0" 
                                               required
                                               class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('price') border-red-500 ring-2 ring-red-200 @enderror"
                                               placeholder="0.00">
                                    </div>
                                    @error('price')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- 成本价 -->
                                <div>
                                    <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-2">
                                        成本价
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">¥</span>
                                        <input type="number" 
                                               id="cost_price" 
                                               name="cost_price" 
                                               value="{{ old('cost_price', $product->cost_price) }}" 
                                               step="0.01" 
                                               min="0"
                                               class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('cost_price') border-red-500 ring-2 ring-red-200 @enderror"
                                               placeholder="0.00">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">基础商品必填，盲袋商品选填</p>
                                    @error('cost_price')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 库存信息 -->
                        <div class="bg-gray-50 rounded-lg p-5">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="bi bi-boxes text-purple-600 mr-2"></i>
                                库存信息
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- 当前库存（只读） -->
                                <div>
                                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                                        当前库存
                                    </label>
                                    <input type="number" 
                                           id="stock" 
                                           value="{{ $product->stock }}" 
                                           disabled
                                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-500"
                                           placeholder="0">
                                    <p class="mt-1 text-xs text-gray-500">库存数量通过入库/出库操作修改</p>
                                </div>

                                <!-- 库存警戒值 -->
                                <div>
                                    <label for="alert_stock" class="block text-sm font-medium text-gray-700 mb-2">
                                        库存警戒值 <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" 
                                           id="alert_stock" 
                                           name="alert_stock" 
                                           value="{{ old('alert_stock', $product->alert_stock) }}" 
                                           min="0" 
                                           required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('alert_stock') border-red-500 ring-2 ring-red-200 @enderror"
                                           placeholder="10">
                                    @error('alert_stock')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- 排序 -->
                                <div>
                                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                                        排序
                                    </label>
                                    <input type="number" 
                                           id="sort_order" 
                                           name="sort_order" 
                                           value="{{ old('sort_order', $product->sort_order) }}" 
                                           min="0"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('sort_order') border-red-500 ring-2 ring-red-200 @enderror"
                                           placeholder="0">
                                    @error('sort_order')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 右侧附加信息 -->
                    <div class="space-y-6">
                        <!-- 商品图片 -->
                        <div class="bg-gray-50 rounded-lg p-5">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="bi bi-image text-indigo-600 mr-2"></i>
                                商品图片
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                                        更新图片
                                    </label>
                                    <input type="file" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('image') border-red-500 ring-2 ring-red-200 @enderror">
                                    @error('image')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- 当前图片显示 -->
                                <div class="mt-4">
                                    @if($product->image && $product->image_url)
                                        <div id="currentImage">
                                            <p class="text-sm text-gray-600 mb-2">当前图片：</p>
                                            <img src="{{ $product->image_url }}" 
                                                 alt="当前图片" 
                                                 class="w-full h-48 object-cover rounded-lg border border-gray-200">
                                        </div>
                                    @else
                                        <div id="currentImage" class="w-full h-48 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <div class="text-center">
                                                <i class="bi bi-image text-gray-400 text-3xl"></i>
                                                <p class="text-gray-500 text-sm mt-2">暂无图片</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- 新图片预览 -->
                                    <div id="newImagePreview" class="mt-3 hidden">
                                        <p class="text-sm text-gray-600 mb-2">新图片预览：</p>
                                        <img id="imagePreview" 
                                             src="#" 
                                             alt="预览图" 
                                             class="w-full h-48 object-cover rounded-lg border border-gray-200">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 商品状态 -->
                        <div class="bg-gray-50 rounded-lg p-5">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="bi bi-toggles text-orange-600 mr-2"></i>
                                商品状态
                            </h2>
                            
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                       class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">
                                    上架商品
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">取消选中后商品将下架停售</p>
                        </div>

                        <!-- 创建信息 -->
                        <div class="bg-gray-50 rounded-lg p-5">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="bi bi-info-circle text-gray-600 mr-2"></i>
                                创建信息
                            </h2>
                            
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>创建时间：</span>
                                    <span>{{ $product->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>更新时间：</span>
                                    <span>{{ $product->updated_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>商品ID：</span>
                                    <span>#{{ $product->id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 表单操作区 -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('products.index') }}" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        取消修改
                    </a>
                    <a href="{{ route('products.show', $product) }}" class="px-4 py-2 text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                        查看详情
                    </a>
                </div>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-sm">
                    <i class="bi bi-save mr-2"></i>
                    保存修改
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const newImagePreview = document.getElementById('newImagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            newImagePreview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        newImagePreview.classList.add('hidden');
    }
});

// 根据商品类型控制成本价输入
document.getElementById('type').addEventListener('change', function(e) {
    const costPriceInput = document.getElementById('cost_price');
    const costPriceLabel = document.querySelector('label[for="cost_price"]');
    
    if (e.target.value === 'standard') {
        costPriceInput.required = true;
        costPriceLabel.innerHTML = '成本价 <span class="text-red-500">*</span>';
    } else {
        costPriceInput.required = false;
        costPriceLabel.innerHTML = '成本价';
    }
});

// 初始化成本价输入框状态
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const costPriceInput = document.getElementById('cost_price');
    const costPriceLabel = document.querySelector('label[for="cost_price"]');
    
    if (typeSelect.value === 'standard') {
        costPriceInput.required = true;
        costPriceLabel.innerHTML = '成本价 <span class="text-red-500">*</span>';
    } else {
        costPriceInput.required = false;
        costPriceLabel.innerHTML = '成本价';
    }
});
</script>
@endpush
@endsection 