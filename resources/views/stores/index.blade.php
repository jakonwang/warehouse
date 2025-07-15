@extends('layouts.app')

@section('title', __('messages.stores.title'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900"><x-lang key="messages.stores.title"/></h1>
                    <p class="mt-2 text-gray-600"><x-lang key="messages.stores.subtitle"/></p>
                </div>
                <a href="{{ route('stores.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="bi bi-plus-circle mr-2"></i><x-lang key="messages.stores.add"/>
                </a>
            </div>
        </div>

        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-building text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600"><x-lang key="messages.stores.total_stores"/></p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stores->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-toggle-on text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600"><x-lang key="messages.stores.active_stores"/></p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stores->where('is_active', true)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-toggle-off text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600"><x-lang key="messages.stores.inactive_stores"/></p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stores->where('is_active', false)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-box text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600"><x-lang key="messages.stores.stores_with_products"/></p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stores->filter(function($store) { return $store->availableProducts->count() > 0; })->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 搜索和筛选 -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6 mb-8">
            <form method="GET" action="{{ route('stores.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stores.search_stores"/></label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="<x-lang key="messages.stores.search_placeholder"/>">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stores.status_filter"/></label>
                    <select id="status" 
                            name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value=""><x-lang key="messages.stores.all_status"/></option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}><x-lang key="messages.stores.active"/></option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}><x-lang key="messages.stores.inactive"/></option>
                    </select>
                </div>

                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stores.sort_by"/></label>
                    <select id="sort" 
                            name="sort"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}><x-lang key="messages.stores.latest_created"/></option>
                        <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}><x-lang key="messages.stores.earliest_created"/></option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}><x-lang key="messages.stores.name_asc"/></option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}><x-lang key="messages.stores.name_desc"/></option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="bi bi-search mr-2"></i><x-lang key="messages.stores.search"/>
                    </button>
                </div>
            </form>
        </div>

        <!-- 仓库列表 -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.stores.store_info"/></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.stores.status"/></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.stores.product_stats"/></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.stores.created_time"/></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-lang key="messages.stores.actions"/></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($stores as $store)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-building text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $store->name }}</div>
                                        <div class="text-sm text-gray-500"><x-lang key="messages.stores.code"/>：{{ $store->code }}</div>
                                        @if($store->description)
                                            <div class="text-xs text-gray-400 mt-1">{{ Str::limit($store->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $store->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $store->is_active ? __('messages.stores.active') : __('messages.stores.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div><x-lang key="messages.stores.total_products"/>：{{ $store->availableProducts->count() }}</div>
                                    <div class="text-xs text-gray-500">
                                        <x-lang key="messages.stores.standard"/>：{{ $store->availableProducts->where('type', 'standard')->count() }} | 
                                        <x-lang key="messages.stores.blind_bag"/>：{{ $store->availableProducts->where('type', 'blind_bag')->count() }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $store->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('stores.show', $store) }}" 
                                       class="text-blue-600 hover:text-blue-900 transition-colors"
                                       title="<x-lang key="messages.stores.view_details"/>">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('stores.edit', $store) }}" 
                                       class="text-green-600 hover:text-green-900 transition-colors"
                                       title="<x-lang key="messages.stores.edit"/>">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('store-products.show', $store) }}" 
                                       class="text-purple-600 hover:text-purple-900 transition-colors"
                                       title="<x-lang key="messages.stores.manage_products"/>">
                                        <i class="bi bi-box"></i>
                                    </a>
                                    <form action="{{ route('stores.destroy', $store) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('<x-lang key="messages.stores.confirm_delete"/>')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 transition-colors"
                                                title="<x-lang key="messages.stores.delete"/>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="bi bi-building text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">暂无仓库数据</p>
                                    <p class="text-sm">点击上方"新增仓库"按钮创建第一个仓库</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            @if($stores->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $stores->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 