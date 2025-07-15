@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- 欢迎区域 -->
    <div class="card p-6 floating">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold gradient-text"><x-lang key="messages.mobile.welcome_back"/></h1>
                <p class="text-gray-600 mt-1"><x-lang key="messages.mobile.today_is"/> {{ now()->format('Y年m月d日') }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="bi bi-person text-white text-xl"></i>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ml-2 px-3 py-1 rounded-lg text-xs text-gray-500 border border-gray-200 hover:bg-gray-100 hover:text-red-500 transition-all">
                        <i class="bi bi-box-arrow-right mr-1"></i><x-lang key="messages.mobile.logout"/>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 快捷操作 -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold mb-4 gradient-text"><x-lang key="messages.mobile.quick_actions"/></h2>
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('mobile.sales.create') }}" class="modern-btn text-center py-4 px-4 rounded-xl">
                <i class="bi bi-cart-plus text-2xl mb-2"></i>
                <div class="text-sm font-medium"><x-lang key="messages.mobile.add_sale"/></div>
            </a>
            <a href="{{ route('mobile.stock-in.index') }}" class="bg-gradient-to-br from-green-500 to-emerald-600 text-white text-center py-4 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <i class="bi bi-box-arrow-in-down text-2xl mb-2"></i>
                <div class="text-sm font-medium"><x-lang key="messages.mobile.stock_in.title"/></div>
            </a>
            <a href="{{ route('mobile.returns.create') }}" class="bg-gradient-to-br from-orange-500 to-red-500 text-white text-center py-4 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <i class="bi bi-arrow-return-left text-2xl mb-2"></i>
                <div class="text-sm font-medium"><x-lang key="messages.mobile.return_process"/></div>
            </a>
            <a href="{{ route('mobile.inventory.index') }}" class="bg-gradient-to-br from-blue-500 to-cyan-500 text-white text-center py-4 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <i class="bi bi-archive text-2xl mb-2"></i>
                <div class="text-sm font-medium"><x-lang key="messages.mobile.inventory_query"/></div>
            </a>
        </div>
    </div>

    <!-- 数据概览 -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold mb-4 gradient-text"><x-lang key="messages.mobile.today_data"/></h2>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-4 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold">{{ $todaySales }}</div>
                        <div class="text-sm opacity-90"><x-lang key="messages.mobile.today_sales"/></div>
                    </div>
                    <i class="bi bi-cart3 text-3xl opacity-80"></i>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white p-4 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold">{{ $todayRevenue }}</div>
                        <div class="text-sm opacity-90"><x-lang key="messages.mobile.today_revenue"/></div>
                    </div>
                    <i class="bi bi-currency-dollar text-3xl opacity-80"></i>
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-500 to-cyan-500 text-white p-4 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold">{{ $totalProducts }}</div>
                        <div class="text-sm opacity-90"><x-lang key="messages.mobile.total_products"/></div>
                    </div>
                    <i class="bi bi-box text-3xl opacity-80"></i>
                </div>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-red-500 text-white p-4 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold">{{ $lowStockCount }}</div>
                        <div class="text-sm opacity-90"><x-lang key="messages.mobile.low_stock"/></div>
                    </div>
                    <i class="bi bi-exclamation-triangle text-3xl opacity-80"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 最近活动 -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold mb-4 gradient-text"><x-lang key="messages.mobile.recent_activities"/></h2>
        <div class="space-y-3">
            @forelse($recentActivities as $activity)
            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                    <i class="bi bi-{{ $activity->icon }} text-indigo-600"></i>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-900">{{ $activity->description }}</div>
                    <div class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <i class="bi bi-inbox text-4xl mb-2"></i>
                <div><x-lang key="messages.mobile.no_activities"/></div>
            </div>
            @endforelse
        </div>
    </div>
    <div class="h-24"></div>
</div>
@endsection 