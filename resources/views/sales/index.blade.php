@extends('layouts.app')

@section('title', __('messages.sale.title'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <!-- 顶部大标题与渐变背景 -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="relative rounded-3xl overflow-hidden shadow-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 p-8 flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-lg"><x-lang key="messages.sale.title"/></h1>
                <p class="mt-2 text-white/80 text-base md:text-lg"><x-lang key="messages.sale.subtitle"/></p>
            </div>
            <div class="mt-6 md:mt-0 flex items-center space-x-3">
                <a href="{{ route('sales.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold rounded-xl shadow-lg hover:from-green-600 hover:to-emerald-600 transition-all duration-200">
                    <i class="bi bi-plus-lg mr-2"></i> <x-lang key="messages.sale.add_sales"/>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto space-y-8">
        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg backdrop-blur-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-currency-dollar text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-blue-100 text-sm"><x-lang key="messages.sale.today_sales"/></p>
                        <p class="text-2xl font-bold">¥{{ number_format($sales->where('created_at', '>=', today())->sum('total_amount'), 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg backdrop-blur-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-graph-up text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-green-100 text-sm"><x-lang key="messages.sale.today_profit"/></p>
                        <p class="text-2xl font-bold">¥{{ number_format($sales->where('created_at', '>=', today())->sum('total_profit'), 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg backdrop-blur-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-receipt text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-purple-100 text-sm"><x-lang key="messages.sale.today_orders"/></p>
                        <p class="text-2xl font-bold">{{ $sales->where('created_at', '>=', today())->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg backdrop-blur-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i class="bi bi-percent text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-orange-100 text-sm"><x-lang key="messages.sale.avg_profit_rate"/></p>
                        <p class="text-2xl font-bold">{{ $sales->count() > 0 ? number_format($sales->avg('profit_rate'), 1) : 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 筛选栏 -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
            <form action="{{ route('sales.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sale.store_selection"/></label>
                    <select name="store_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value=""><x-lang key="messages.sale.all_stores"/></option>
                        @foreach($stores ?? [] as $store)
                            <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sale.salesperson"/></label>
                    <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value=""><x-lang key="messages.sale.all_salespeople"/></option>
                        @foreach($users ?? [] as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->real_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sale.time_range"/></label>
                    <select name="period" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value=""><x-lang key="messages.sale.all_time"/></option>
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}><x-lang key="messages.sale.today"/></option>
                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}><x-lang key="messages.sale.this_week"/></option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}><x-lang key="messages.sale.this_month"/></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.sale.amount_range"/></label>
                    <div class="flex space-x-2">
                        <input type="number" name="amount_min" placeholder="<x-lang key="messages.sale.min_amount"/>" class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" value="{{ request('amount_min') }}">
                        <input type="number" name="amount_max" placeholder="<x-lang key="messages.sale.max_amount"/>" class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" value="{{ request('amount_max') }}">
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:from-green-600 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                        <i class="bi bi-search mr-2"></i> <x-lang key="messages.sale.search"/>
                    </button>
                </div>
            </form>
        </div>

        <!-- 销售记录表格 -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900"><x-lang key="messages.sale.sales_records"/></h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">{{ __('messages.sale.total_records', ['count' => $sales->total()]) }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider"><x-lang key="messages.sale.order_no"/></th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider"><x-lang key="messages.sale.sales_mode"/></th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider"><x-lang key="messages.sale.salesperson_name"/></th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider"><x-lang key="messages.sale.store"/></th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider"><x-lang key="messages.sale.total_amount"/></th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider"><x-lang key="messages.sale.profit"/></th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider"><x-lang key="messages.sale.profit_rate"/></th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider"><x-lang key="messages.sale.sales_time"/></th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider"><x-lang key="messages.sale.actions"/></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/80 divide-y divide-gray-100">
                        @forelse($sales as $sale)
                        <tr class="hover:bg-gradient-to-r hover:from-indigo-50/60 hover:to-purple-50/60 transition-all duration-300">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">{{ $sale->order_no }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($sale->sale_type === 'standard')
                                        bg-blue-100 text-blue-800
                                    @elseif($sale->sale_type === 'blind_bag')
                                        bg-purple-100 text-purple-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $sale->sale_type_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $sale->user_name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $sale->store_name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold">¥{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold">¥{{ number_format($sale->total_profit, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600 font-bold">{{ $sale->profit_rate }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ date('Y-m-d H:i', strtotime($sale->created_at)) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center justify-center p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors duration-200" title="<x-lang key="messages.sale.view"/>">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('sales.edit', $sale) }}" class="inline-flex items-center justify-center p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200" title="<x-lang key="messages.sale.edit"/>">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="inline-block" onsubmit="return confirm('<x-lang key="messages.sale.confirm_delete"/>');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200" title="<x-lang key="messages.sale.delete"/>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                <i class="bi bi-receipt text-4xl mb-4"></i>
                                <div class="mt-2"><x-lang key="messages.sale.no_sales_records"/></div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- 分页 -->
            <div class="p-6 border-t border-gray-100 flex justify-center">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 