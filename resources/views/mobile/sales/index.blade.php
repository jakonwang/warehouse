@extends('layouts.mobile')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-6 pb-4">
    <!-- ๆ ้ข -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">๐ฐ <x-lang key="mobile.sales.title"/></h1>
        <p class="text-gray-600"><x-lang key="mobile.sales.subtitle"/></p>
    </div>

    @if (session('success'))
        <div class="card p-4 border-l-4 border-green-500 bg-green-50">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="card p-4 border-l-4 border-red-500 bg-red-50">
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <!-- ๅฟซๆทๆไฝ -->
    <div class="card p-6">
        <div class="space-y-3">
            <a href="{{ route('mobile.sales.create') }}" 
               class="btn-primary w-full flex items-center justify-center py-4 text-white font-semibold rounded-lg shadow-lg">
                <i class="bi bi-plus-circle mr-2"></i>
                <x-lang key="mobile.sales.create_new"/>
            </a>
        </div>
        
        <div class="mt-6 text-sm text-gray-600 text-center">
            <div class="border-t pt-4">
                <div class="mb-2">
                    <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        ๐ก <x-lang key="mobile.sales.dual_mode_support"/>
                    </span>
                </div>
                <div class="space-y-1 text-sm">
                    <div class="flex items-center justify-center space-x-2">
                        <span class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">
                            ๐ฒ <x-lang key="mobile.sales.standard"/>
                        </span>
                        <span class="text-gray-500"><x-lang key="mobile.sales.standard_desc"/></span>
                    </div>
                    <div class="flex items-center justify-center space-x-2">
                        <span class="inline-flex items-center px-2 py-1 bg-purple-50 text-purple-700 rounded text-xs">
                            ๐ <x-lang key="mobile.sales.blind_bag"/>
                        </span>
                        <span class="text-gray-500"><x-lang key="mobile.sales.blind_bag_desc"/></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ้ๅฎ่ฎฐๅฝๅ่ก?-->
    @if($sales->count() > 0)
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">๐ <x-lang key="mobile.sales.record_list"/></h2>
            <div class="space-y-4">
                @foreach($sales as $sale)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ $sale->created_at instanceof \Carbon\Carbon ? $sale->created_at->format('Y-m-d H:i') : \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d H:i') }}
                                    </span>
                                    @if($sale->sale_type === 'blind_bag')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            ๐ <x-lang key="mobile.sales.blind_bag"/>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            ๐๏ธ?<x-lang key="mobile.sales.standard"/>
                                        </span>
                                    @endif
                                </div>
                                
                                @if($sale->store_name)
                                    <div class="text-xs text-gray-500 mb-1">
                                        <x-lang key="mobile.sales.store"/>: {{ $sale->store_name }}
                                    </div>
                                @endif
                                
                                @if($sale->customer_name)
                                    <div class="text-xs text-gray-500 mb-1">
                                        <x-lang key="mobile.sales.customer"/>: {{ $sale->customer_name }}
                                        @if($sale->customer_phone)
                                            ({{ $sale->customer_phone }})
                                        @endif
                                    </div>
                                @endif
                                
                                <div class="text-xs text-gray-500">
                                    <x-lang key="mobile.sales.operator"/>: {{ $sale->user_name }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-600">
                                    ยฅ{{ number_format($sale->total_amount, 2) }}
                                </div>
                                @if(auth()->user()->canViewProfitAndCost() && $sale->total_profit)
                                    <div class="text-xs text-green-500">
                                        <x-lang key="mobile.sales.profit"/>: ยฅ{{ number_format($sale->total_profit, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <x-lang key="mobile.sales.profit_rate"/>: {{ number_format($sale->profit_rate, 1) }}%
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- ้ๅฎๆ็ป?-->
                        @if($sale->sale_details && count($sale->sale_details) > 0)
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach($sale->sale_details as $detail)
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded border">
                                        {{ $detail->product_name }} ร {{ $detail->quantity }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- ็ฒ่ขๅ่ดงๆ็ป -->
                        @if($sale->blind_bag_deliveries && count($sale->blind_bag_deliveries) > 0)
                            <div class="mb-3">
                                <div class="text-xs text-gray-600 mb-1"><x-lang key="mobile.sales.delivery_content"/>:</div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($sale->blind_bag_deliveries as $delivery)
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded border">
                                            {{ $delivery->delivery_product_name }} ร {{ $delivery->quantity }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- ๆไฝๆ้ฎ -->
                        <div class="flex space-x-2">
                            <div class="flex-1"></div>
                            
                            <a href="{{ route('mobile.sales.show', $sale->id) }}" 
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                <x-lang key="mobile.sales.view_details"/>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- ๅ้กต -->
            @if($sales->hasPages())
                <div class="mt-6">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- ็ฉบ็ถๆ?-->
        <div class="card p-8 text-center">
            <div class="text-6xl mb-4">๐</div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2"><x-lang key="mobile.sales.no_records"/></h3>
            <p class="text-gray-600 mb-6"><x-lang key="mobile.sales.start_first_sale"/></p>
            <a href="{{ route('mobile.sales.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                <i class="bi bi-plus-circle mr-2"></i>
                <x-lang key="mobile.sales.create_record"/>
            </a>
        </div>
    @endif

    <!-- ็ป่ฎกๆ่ฆ -->
    @if($sales->count() > 0)
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">๐ <x-lang key="mobile.sales.today_stats"/></h2>
            @php
                $todaySales = collect($sales->items())->filter(function($sale) {
                    return \Carbon\Carbon::parse($sale->created_at)->isToday();
                });
                $todayTotal = $todaySales->sum('total_amount');
                $todayCount = $todaySales->count();
                $todayProfit = $todaySales->sum('total_profit');
                $todayBlindBagCount = $todaySales->where('sale_type', 'blind_bag')->count();
                $todayStandardCount = $todaySales->where('sale_type', 'standard')->count();
            @endphp
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="text-lg font-bold text-blue-600">{{ $todayCount }}</div>
                    <p class="text-xs text-gray-500"><x-lang key="mobile.sales.today_orders"/></p>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="text-lg font-bold text-green-600">ยฅ{{ number_format($todayTotal, 0) }}</div>
                    <p class="text-xs text-gray-500"><x-lang key="mobile.sales.today_sales"/></p>
                </div>
                @if(auth()->user()->canViewProfitAndCost())
                <div class="text-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="text-lg font-bold text-purple-600">ยฅ{{ number_format($todayProfit, 0) }}</div>
                    <p class="text-xs text-gray-500"><x-lang key="mobile.sales.today_profit"/></p>
                </div>
                @endif
                <div class="text-center p-3 bg-orange-50 rounded-lg border border-orange-200">
                    <div class="text-lg font-bold text-orange-600">{{ $todayBlindBagCount }}/{{ $todayStandardCount }}</div>
                    <p class="text-xs text-gray-500"><x-lang key="mobile.sales.blind_bag_standard"/></p>
                </div>
            </div>
        </div>
    @endif
    <div class="h-24"></div>
</div>
@endsection 
