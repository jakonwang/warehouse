@extends('layouts.app')

@section('title', '入库详情')

@section('content')
<div class="flex justify-center items-center min-h-[70vh] py-8">
    <div class="w-full max-w-4xl">
        <div class="backdrop-blur-xl bg-white/30 border border-white/40 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-8 pt-8 pb-6 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                    <i class="bi bi-box-arrow-in-down text-white text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">入库详情</h1>
                <p class="text-gray-500 text-sm">单号：#{{ $stockInRecord->id }}</p>
            </div>
            <div class="px-8 pb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white/80 rounded-xl shadow p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center"><i class="bi bi-list-ul mr-2 text-blue-500"></i>入库明细</h3>
                        @if($stockInRecord->stockInDetails->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500">商品名称</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500">数量</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500">单价</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500">小计</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($stockInRecord->stockInDetails as $detail)
                                        <tr>
                                            <td class="px-4 py-2">{{ $detail->product->name }}</td>
                                            <td class="px-4 py-2">{{ $detail->quantity }}</td>
                                            <td class="px-4 py-2">¥{{ number_format($detail->unit_price, 2) }}</td>
                                            <td class="px-4 py-2">¥{{ number_format($detail->total_amount, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-400">暂无入库明细</p>
                        @endif
                    </div>
                    <div class="bg-white/80 rounded-xl shadow p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center"><i class="bi bi-info-circle mr-2 text-purple-500"></i>其他信息</h3>
                        <div class="space-y-3">
                            <div><span class="text-gray-500 text-sm">仓库：</span><span class="ml-2 text-gray-900 font-medium">{{ $stockInRecord->store->name }}</span></div>
                            <div><span class="text-gray-500 text-sm">总金额：</span><span class="ml-2 text-blue-600 font-bold">¥{{ number_format($stockInRecord->total_amount, 2) }}</span></div>
                            <div><span class="text-gray-500 text-sm">总成本：</span><span class="ml-2 text-purple-600 font-bold">¥{{ number_format($stockInRecord->total_cost, 2) }}</span></div>
                            <div><span class="text-gray-500 text-sm">供应商：</span><span class="ml-2 text-gray-900 font-medium">{{ $stockInRecord->supplier ?: '无' }}</span></div>
                            <div><span class="text-gray-500 text-sm">备注：</span><span class="ml-2 text-gray-900 font-medium">{{ $stockInRecord->remark ?: '无' }}</span></div>
                            <div><span class="text-gray-500 text-sm">操作人：</span><span class="ml-2 text-gray-900 font-medium">{{ $stockInRecord->user->real_name }}</span></div>
                            <div><span class="text-gray-500 text-sm">入库时间：</span><span class="ml-2 text-gray-900 font-medium">{{ date('Y-m-d H:i:s', strtotime($stockInRecord->created_at)) }}</span></div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-8">
                    <a href="{{ route('stock-ins.index') }}" class="inline-flex items-center px-6 py-2 bg-gray-200 border border-transparent rounded-lg font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all duration-200">
                        <i class="bi bi-arrow-left mr-2"></i> 返回列表
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 