@extends('layouts.app')

@section('title', '退货详情')
@section('header', '退货详情')

@section('content')
<div class="space-y-6">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">退货详情</h2>
                <p class="mt-1 text-sm text-gray-600">退货记录 #{{ $returnRecord->id }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('returns.edit', $returnRecord->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="bi bi-pencil mr-2"></i>
                    编辑
                </a>
                <a href="{{ route('returns.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <i class="bi bi-arrow-left mr-2"></i>
                    返回列表
                </a>
            </div>
        </div>
    </div>

    <!-- 基本信息 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">基本信息</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">仓库</label>
                    <p class="text-base text-gray-900 font-medium">{{ $returnRecord->store->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">客户信息</label>
                    <p class="text-base text-gray-900">{{ $returnRecord->customer ?? '无客户信息' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">操作人</label>
                    <p class="text-base text-gray-900">{{ $returnRecord->user->real_name ?? $returnRecord->user->username }}</p>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">退货金额</label>
                    <p class="text-base text-gray-900 font-semibold text-red-600">¥{{ number_format($returnRecord->total_amount, 2) }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">退货成本</label>
                    <p class="text-base text-gray-900">¥{{ number_format($returnRecord->total_cost, 2) }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">创建时间</label>
                    <p class="text-base text-gray-900">{{ date('Y-m-d H:i:s', strtotime($returnRecord->created_at)) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 退货凭证 -->
    @if($returnRecord->image_path)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">退货凭证</h3>
        <div class="flex items-center space-x-4">
            <img src="{{ asset('uploads/' . $returnRecord->image_path) }}" alt="退货凭证" class="w-32 h-32 object-cover rounded-lg border border-gray-200">
            <div>
                <p class="text-sm text-gray-600">上传时间：{{ date('Y-m-d H:i:s', strtotime($returnRecord->created_at)) }}</p>
                <a href="{{ asset('uploads/' . $returnRecord->image_path) }}" target="_blank" class="inline-flex items-center mt-2 text-sm text-blue-600 hover:text-blue-800">
                    <i class="bi bi-eye mr-1"></i>
                    查看原图
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- 退货明细 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">退货明细</h3>
        </div>
        
        @if($returnRecord->returnDetails && $returnRecord->returnDetails->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                商品名称
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                数量
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                单价
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                总金额
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                总成本
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($returnRecord->returnDetails as $detail)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $detail->product->name ?? '未知商品' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $detail->quantity }} 件
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ¥{{ number_format($detail->unit_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                ¥{{ number_format($detail->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ¥{{ number_format($detail->total_cost, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- 汇总信息 -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700">
                        总计：{{ $returnRecord->returnDetails->sum('quantity') }} 件商品
                    </span>
                    <div class="flex items-center space-x-6">
                        <span class="text-sm text-gray-600">
                            退货金额：<span class="font-semibold text-red-600">¥{{ number_format($returnRecord->total_amount, 2) }}</span>
                        </span>
                        <span class="text-sm text-gray-600">
                            退货成本：<span class="font-semibold text-gray-900">¥{{ number_format($returnRecord->total_cost, 2) }}</span>
                        </span>
                    </div>
                </div>
            </div>
        @else
            <!-- 空状态 -->
            <div class="p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-inbox text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">暂无退货明细</h3>
                <p class="text-gray-500">该退货记录暂无明细数据</p>
            </div>
        @endif
    </div>
</div>
@endsection 