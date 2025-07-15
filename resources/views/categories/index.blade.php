@extends('layouts.app')

@section('title', __('messages.categories.title'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <!-- 高级页面头部 -->
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 shadow-2xl">
        <!-- 装饰背景 -->
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-400/20 rounded-full translate-x-1/3 translate-y-1/3 blur-3xl"></div>
        
        <div class="relative px-6 py-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-tags text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2"><x-lang key="messages.categories.title"/></h1>
                            <p class="text-indigo-100 text-lg"><x-lang key="messages.categories.subtitle"/></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                            <div class="text-2xl font-bold text-white">{{ $totalCategories ?? 0 }}</div>
                            <div class="text-indigo-100 text-sm"><x-lang key="messages.categories.total_categories"/></div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                            <div class="text-2xl font-bold text-white">{{ $activeCategories ?? 0 }}</div>
                            <div class="text-indigo-100 text-sm"><x-lang key="messages.categories.active_categories"/></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 主要内容区域 -->
    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- 操作栏 -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 mb-8 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" placeholder="<x-lang key="messages.categories.search_placeholder"/>" class="pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent w-80">
                    </div>
                    <select class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value=""><x-lang key="messages.categories.all_status"/></option>
                        <option value="1"><x-lang key="messages.categories.active"/></option>
                        <option value="0"><x-lang key="messages.categories.inactive"/></option>
                    </select>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="openCategoryModal()" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center space-x-2">
                        <i class="bi bi-plus-lg"></i>
                        <span><x-lang key="messages.categories.add_category"/></span>
                    </button>
                    <button class="px-6 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-300 flex items-center space-x-2">
                        <i class="bi bi-download"></i>
                        <span><x-lang key="messages.categories.export"/></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 分类列表 -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700"><x-lang key="messages.categories.category_name"/></th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700"><x-lang key="messages.categories.description"/></th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700"><x-lang key="messages.categories.product_count"/></th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700"><x-lang key="messages.categories.status"/></th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700"><x-lang key="messages.categories.sort_order"/></th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700"><x-lang key="messages.categories.actions"/></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($categories as $category)
                        <tr class="hover:bg-gradient-to-r hover:from-indigo-50/50 hover:to-purple-50/50 transition-all duration-300">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-xl flex items-center justify-center">
                                        <i class="bi bi-tag text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                                        @if($category->parent_name)
                                            <div class="text-sm text-gray-500"><x-lang key="messages.categories.parent_category"/>: {{ $category->parent_name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 max-w-xs truncate">
                                    {{ $category->description ?: __('messages.categories.no_description') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">0</span>
                                    <span class="text-xs text-gray-500"><x-lang key="messages.categories.products"/></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($category->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="bi bi-check-circle mr-1"></i>
                                        <x-lang key="messages.categories.active"/>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="bi bi-pause-circle mr-1"></i>
                                        <x-lang key="messages.categories.inactive"/>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600">{{ $category->sort_order ?? 0 }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors duration-200" title="<x-lang key="messages.categories.edit"/>" data-category='@json($category)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200" title="<x-lang key="messages.categories.view_products"/>" data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200" title="<x-lang key="messages.categories.delete"/>" data-category-id="{{ $category->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                        <i class="bi bi-tags text-gray-400 text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2"><x-lang key="messages.categories.no_categories"/></h3>
                                        <p class="text-gray-500"><x-lang key="messages.categories.no_categories_description"/></p>
                                    </div>
                                    <button onclick="openCategoryModal()" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-300">
                                        <x-lang key="messages.categories.create_category"/>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 分页 -->
        @if($categories->hasPages())
        <div class="mt-8 flex justify-center">
            <nav class="flex items-center space-x-2 bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-2">
                @if($categories->onFirstPage())
                    <span class="px-3 py-2 text-gray-400 cursor-not-allowed">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $categories->previousPageUrl() }}" class="px-3 py-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors duration-200">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                @endif

                @foreach($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                    @if($page == $categories->currentPage())
                        <span class="px-3 py-2 bg-indigo-500 text-white rounded-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors duration-200">{{ $page }}</a>
                    @endif
                @endforeach

                @if($categories->hasMorePages())
                    <a href="{{ $categories->nextPageUrl() }}" class="px-3 py-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors duration-200">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @else
                    <span class="px-3 py-2 text-gray-400 cursor-not-allowed">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                @endif
            </nav>
        </div>
        @endif
    </div>
</div>

<!-- 新增分类模态框 -->
<div id="createCategoryModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">新增分类</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors duration-200" onclick="closeCategoryModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form id="categoryForm" action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">分类名称 <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="请输入分类名称" required>
                        @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">父分类</label>
                        <select name="parent_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">无父分类</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">描述</label>
                        <textarea name="description" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent" rows="2" placeholder="请输入描述"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">排序</label>
                        <input type="number" name="sort_order" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent" value="0">
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">启用</span>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-6">
                    <button type="button" class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors duration-200" onclick="closeCategoryModal()">取消</button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-300">创建分类</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 编辑分类模态框 -->
<div id="editCategoryModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">编辑分类</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors duration-200" onclick="closeEditCategoryModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="category_id" id="edit_category_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">分类名称 <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">父分类</label>
                        <select name="parent_id" id="edit_parent_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">无父分类</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">描述</label>
                        <textarea name="description" id="edit_description" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">排序</label>
                        <input type="number" name="sort_order" id="edit_sort_order" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">启用</span>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-6">
                    <button type="button" class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors duration-200" onclick="closeEditCategoryModal()">取消</button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-300">保存修改</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 删除分类确认模态框 -->
<div id="deleteCategoryModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">删除分类</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors duration-200" onclick="closeDeleteCategoryModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="mb-6 text-gray-700">确定要删除该分类吗？删除后不可恢复。</div>
            <form id="deleteCategoryForm" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="category_id" id="delete_category_id">
                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors duration-200" onclick="closeDeleteCategoryModal()">取消</button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl hover:from-red-600 hover:to-pink-700 transition-all duration-300">确认删除</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 查看商品模态框 -->
<div id="viewProductsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">分类商品列表</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors duration-200" onclick="closeViewProductsModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div id="productsList" class="max-h-96 overflow-y-auto">
                <!-- 商品列表将通过AJAX加载 -->
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                    <p class="text-gray-500 mt-2">加载中...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// 打开/关闭新增分类模态框
function openCategoryModal() {
    document.getElementById('createCategoryModal').classList.remove('hidden');
}
function closeCategoryModal() {
    document.getElementById('createCategoryModal').classList.add('hidden');
}
// 打开/关闭编辑分类模态框
function openEditCategoryModal(category) {
    document.getElementById('editCategoryModal').classList.remove('hidden');
    document.getElementById('edit_category_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description || '';
    document.getElementById('edit_sort_order').value = category.sort_order || 0;
    document.getElementById('edit_is_active').checked = !!category.is_active;
    document.getElementById('edit_parent_id').value = category.parent_id || '';
    // 设置表单action
    document.getElementById('editCategoryForm').action = '/categories/' + category.id;
}
function closeEditCategoryModal() {
    document.getElementById('editCategoryModal').classList.add('hidden');
}
// 打开/关闭删除分类模态框
function openDeleteCategoryModal(categoryId) {
    document.getElementById('deleteCategoryModal').classList.remove('hidden');
    document.getElementById('delete_category_id').value = categoryId;
    document.getElementById('deleteCategoryForm').action = '/categories/' + categoryId;
}
function closeDeleteCategoryModal() {
    document.getElementById('deleteCategoryModal').classList.add('hidden');
}
// 打开/关闭查看商品模态框
function openViewProductsModal(categoryId, categoryName) {
    document.getElementById('viewProductsModal').classList.remove('hidden');
    document.querySelector('#viewProductsModal h3').textContent = categoryName + ' - 商品列表';
    
    // 加载商品列表
    loadCategoryProducts(categoryId);
}
function closeViewProductsModal() {
    document.getElementById('viewProductsModal').classList.add('hidden');
}
function loadCategoryProducts(categoryId) {
    const productsList = document.getElementById('productsList');
    productsList.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div><p class="text-gray-500 mt-2">加载中...</p></div>';
    
    console.log('Loading products for category:', categoryId);
    
    fetch(`/categories/${categoryId}/products`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success && data.products && data.products.length > 0) {
                let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
                data.products.forEach(product => {
                    html += `
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-box text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">${product.name}</h4>
                                    <p class="text-sm text-gray-500">编码: ${product.code}</p>
                                    <p class="text-sm text-gray-600">价格: ¥${product.price}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                productsList.innerHTML = html;
            } else {
                productsList.innerHTML = '<div class="text-center py-8"><i class="bi bi-box text-gray-400 text-4xl mb-4"></i><p class="text-gray-500">该分类下暂无商品</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            productsList.innerHTML = '<div class="text-center py-8"><i class="bi bi-exclamation-triangle text-red-400 text-4xl mb-4"></i><p class="text-red-500">加载失败：' + error.message + '</p></div>';
        });
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('button').forEach(btn => {
        if (btn.textContent.includes('新增分类')) {
            btn.addEventListener('click', openCategoryModal);
        }
    });
    // 编辑按钮事件
    document.querySelectorAll('button[title="编辑"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const category = JSON.parse(this.dataset.category);
            openEditCategoryModal(category);
        });
    });
    // 删除按钮事件
    document.querySelectorAll('button[title="删除"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            openDeleteCategoryModal(categoryId);
        });
    });
    // 查看商品按钮事件
    document.querySelectorAll('button[title="查看商品"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const categoryName = this.dataset.categoryName;
            openViewProductsModal(categoryId, categoryName);
        });
    });
});
</script>
@endpush
@endsection 