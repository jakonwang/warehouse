@extends('layouts.app')

@section('title', __('messages.returns.title'))
@section('header', __('messages.returns.title'))

@section('content')
<div class="space-y-6">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><x-lang key="messages.returns.title"/></h2>
                <p class="mt-1 text-sm text-gray-600"><x-lang key="messages.returns.subtitle"/></p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('returns.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                    <i class="bi bi-plus-circle mr-2"></i>
                    <x-lang key="messages.returns.add"/>
                </a>
            </div>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-arrow-return-left text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-red-100 text-sm"><x-lang key="messages.returns.today_returns"/></p>
                    <p class="text-2xl font-bold">{{ $todayCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-currency-dollar text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-orange-100 text-sm"><x-lang key="messages.returns.return_amount"/></p>
                    <p class="text-2xl font-bold">¥{{ number_format($totalAmount, 0) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-exclamation-triangle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-yellow-100 text-sm"><x-lang key="messages.returns.pending"/></p>
                    <p class="text-2xl font-bold">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-graph-up text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-purple-100 text-sm"><x-lang key="messages.returns.return_rate"/></p>
                    <p class="text-2xl font-bold">{{ $returnRate }}%</p>
                    <p class="text-purple-200 text-xs">本月数据</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选和搜索 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.returns.time_range"/></label>
                <select name="date_range" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <option value=""><x-lang key="messages.returns.all_time"/></option>
                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}><x-lang key="messages.returns.today"/></option>
                    <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}><x-lang key="messages.returns.this_week"/></option>
                    <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}><x-lang key="messages.returns.this_month"/></option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.returns.search"/></label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="<x-lang key="messages.returns.search_placeholder"/>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <i class="bi bi-search mr-2"></i>
                    <x-lang key="messages.returns.search"/>
                </button>
            </div>
        </form>
    </div>

    <!-- 退货记录列表 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900"><x-lang key="messages.returns.return_records"/></h3>
        </div>
        
        @if($records->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <x-lang key="messages.returns.return_info"/>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <x-lang key="messages.returns.store_customer"/>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <x-lang key="messages.returns.amount_cost"/>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <x-lang key="messages.returns.operator"/>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <x-lang key="messages.returns.created_time"/>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <x-lang key="messages.returns.actions"/>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($records as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-arrow-return-left text-white"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900"><x-lang key="messages.returns.return_record"/> #{{ $record->id }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $record->returnDetails->count() }} <x-lang key="messages.returns.product_types"/>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium">{{ $record->store->name }}</div>
                                    <div class="text-gray-500">{{ $record->customer ?? __('messages.returns.no_customer_info') }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <span class="font-medium text-red-600">¥{{ number_format($record->total_amount, 2) }}</span>
                                        <span class="text-gray-400 mx-1">/</span>
                                        <span class="text-gray-500"><x-lang key="messages.returns.cost"/>¥{{ number_format($record->total_cost, 2) }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <x-lang key="messages.returns.quantity"/>: {{ $record->returnDetails ? $record->returnDetails->sum('quantity') : 0 }}<x-lang key="messages.returns.pieces"/>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium">{{ $record->user->real_name ?? $record->user->username }}</div>
                                    <div class="text-gray-500">{{ $record->user->email }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ date('Y-m-d H:i', strtotime($record->created_at)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('returns.show', $record->id) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($record->canDelete())
                                        <form action="{{ route('returns.destroy', $record->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('<x-lang key="messages.returns.confirm_delete"/>')" class="text-red-600 hover:text-red-900">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                {{ $records->links() }}
            </div>
        @else
            <!-- 空状态 -->
            <div class="p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-inbox text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">暂无退货记录</h3>
                <p class="text-gray-500">还没有创建任何退货记录</p>
                <div class="mt-6">
                    <a href="{{ route('returns.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                        <i class="bi bi-plus-circle mr-2"></i>
                        创建退货记录
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection 