@extends('layouts.app')

@section('title', __('messages.sale.view_details'))

@section('styles')
<style>
    .card {
        background: white;
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0 2rem 0 rgba(136, 152, 170, 0.15);
        margin-bottom: 1.5rem;
    }
    .card-header {
        background: none;
        border-bottom: 1px solid #e9ecef;
        padding: 1.5rem;
    }
    .card-body {
        padding: 1.5rem;
    }
    .info-label {
        color: #8898aa;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    .info-value {
        color: #344767;
        font-weight: 500;
        margin-bottom: 1rem;
    }
    .result-card {
        background: linear-gradient(45deg, #5e72e4, #825ee4);
        color: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .result-card h6 {
        margin-bottom: 0.5rem;
        opacity: 0.8;
    }
    .result-card h3 {
        margin-bottom: 0;
        font-weight: 600;
    }
    .image-preview {
        width: 100%;
        max-height: 400px;
        border-radius: 0.5rem;
        object-fit: contain;
    }
    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900"><x-lang key="messages.sale.view_details"/></h1>
                    <p class="mt-2 text-gray-600"><x-lang key="messages.sale.view_details_description"/></p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('sales.edit', $sale) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-200">
                        <i class="bi bi-pencil mr-2"></i> <x-lang key="messages.sale.edit"/>
                    </a>
                    <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <i class="bi bi-arrow-left mr-2"></i><x-lang key="messages.sale.back_to_list"/>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- 主要内容区域 -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 基本信息 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">基本信息</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">订单信息</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">订单号：</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $sale->order_no }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">销售模式：</span>
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
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">销售时间：</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $sale->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">操作员：</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $sale->user->real_name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">客户信息</h4>
                            <div class="space-y-2">
                                @if($sale->customer_name)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">客户姓名：</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $sale->customer_name }}</span>
                                    </div>
                                    @if($sale->customer_phone)
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">客户电话：</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $sale->customer_phone }}</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-500">未填写客户信息</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 标品销售明细 -->
                @if($sale->saleDetails->count() > 0)
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">标品销售明细</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">商品名称</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">数量</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">单价</th>
                                    @if(auth()->user()->canViewProfitAndCost())
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">成本</th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">小计</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/80 divide-y divide-gray-100">
                                @foreach($sale->saleDetails as $detail)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $detail->product->name ?? '未知商品' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $detail->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">¥{{ number_format($detail->price, 2) }}</td>
                                    @if(auth()->user()->canViewProfitAndCost())
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">¥{{ number_format($detail->cost, 2) }}</td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold">¥{{ number_format($detail->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- 盲袋销售明细 -->
                @if($sale->blindBagDeliveries->count() > 0)
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">盲袋销售明细</h3>
                        <p class="text-sm text-gray-600">显示盲袋销售和实际发货内容</p>
                    </div>

                    <!-- 盲袋销售信息 -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">盲袋销售信息</h4>
                        @foreach($sale->saleDetails as $detail)
                            @if($detail->product && $detail->product->type === 'blind_bag')
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ $detail->product->name }}</span>
                                <span class="text-sm font-medium text-gray-900">{{ $detail->quantity }}个 × ¥{{ number_format($detail->price, 2) }} = ¥{{ number_format($detail->total, 2) }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- 实际发货明细 -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-purple-50 to-purple-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">发货商品</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">数量</th>
                                    @if(auth()->user()->canViewProfitAndCost())
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">成本单价</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">成本小计</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white/80 divide-y divide-gray-100">
                                @foreach($sale->blindBagDeliveries as $delivery)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $delivery->deliveryProduct->name ?? '未知商品' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $delivery->quantity }}</td>
                                    @if(auth()->user()->canViewProfitAndCost())
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">¥{{ number_format($delivery->unit_cost, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold">¥{{ number_format($delivery->total_cost, 2) }}</td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- 备注信息 -->
                @if($sale->remark)
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">备注信息</h3>
                    </div>
                    <p class="text-sm text-gray-700">{{ $sale->remark }}</p>
                </div>
                @endif

                <!-- 销售凭证 -->
                @if($sale->image_path)
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">销售凭证</h3>
                    </div>
                    <img src="{{ asset('storage/' . $sale->image_path) }}" alt="销售凭证" class="w-full max-h-96 object-contain rounded-lg">
                </div>
                @endif
            </div>

            <!-- 侧边栏统计 -->
            <div class="space-y-6">
                <!-- 销售统计 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">销售统计</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">销售金额</span>
                            <span class="text-lg font-bold text-green-600">¥{{ number_format($sale->total_amount, 2) }}</span>
                        </div>
                        @if(auth()->user()->canViewProfitAndCost())
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">销售成本</span>
                            <span class="text-lg font-bold text-orange-600">¥{{ number_format($sale->total_cost, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">销售利润</span>
                            <span class="text-lg font-bold text-purple-600">¥{{ number_format($sale->total_profit, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">利润率</span>
                            <span class="text-lg font-bold text-yellow-600">{{ number_format($sale->profit_rate, 1) }}%</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 销售模式说明 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">销售模式说明</h3>
                    <div class="space-y-3">
                        @if($sale->sale_type === 'standard')
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600">标品销售：直接销售固定价格商品</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600">价格透明，成本固定</p>
                            </div>
                        @elseif($sale->sale_type === 'blind_bag')
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600">盲袋销售：销售盲袋商品</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600">主播决定实际发货内容</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                                <p class="text-sm text-gray-600">利润 = 销售收入 - 实际发货成本</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 