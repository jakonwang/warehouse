@extends('layouts.app')

@section('title', '报表统计')
@section('header', '报表统计')

@section('content')
<div class="space-y-6" x-data="{ 
    selectedPeriod: 'month',
    selectedStore: 'all',
    reportType: 'sales'
}">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">报表统计</h2>
                <p class="mt-1 text-sm text-gray-600">全面的数据分析报表，支持多维度数据对比分析</p>
            </div>
            <div class="mt-4 lg:mt-0 flex items-center space-x-3">
                <select x-model="reportType" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="sales">销售报表</option>
                    <option value="inventory">库存报表</option>
                    <option value="financial">财务报表</option>
                    <option value="comparison">对比分析</option>
                </select>
                <button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-medium text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="bi bi-download mr-2"></i>
                    导出报表
                </button>
            </div>
        </div>
    </div>

    <!-- 快速筛选 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">时间范围</label>
                <select x-model="selectedPeriod" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="today">今天</option>
                    <option value="week">本周</option>
                    <option value="month">本月</option>
                    <option value="quarter">本季度</option>
                    <option value="year">本年</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">仓库选择</label>
                <select x-model="selectedStore" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">{{ __('messages.stores.all_stores') }}</option>
                    <option value="store1">李佳琦直播间</option>
                    <option value="store2">薇娅直播间</option>
                    <option value="store3">罗永浩直播间</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">自定义日期</label>
                <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    <i class="bi bi-search mr-2"></i>
                    生成报表
                </button>
            </div>
        </div>
    </div>

    <!-- 核心指标概览 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-currency-dollar text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-blue-100 text-sm">总销售额</p>
                    <p class="text-2xl font-bold">¥2,580,000</p>
                    <p class="text-xs text-blue-200 mt-1">较上月 +15.8%</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-graph-up text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-green-100 text-sm">总利润</p>
                    <p class="text-2xl font-bold">¥698,000</p>
                    <p class="text-xs text-green-200 mt-1">利润率 27.1%</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-box text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-purple-100 text-sm">库存周转</p>
                    <p class="text-2xl font-bold">2.4次</p>
                    <p class="text-xs text-purple-200 mt-1">较上月 +0.3</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-people text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-orange-100 text-sm">活跃客户</p>
                    <p class="text-2xl font-bold">8,456</p>
                    <p class="text-xs text-orange-200 mt-1">新增 +128</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 报表内容区域 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 销售趋势图表 -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">销售趋势分析</h3>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-lg">销售额</button>
                    <button class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">利润</button>
                    <button class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">订单数</button>
                </div>
            </div>
            
            <div class="h-80 bg-gray-50 rounded-lg">
                <canvas id="storeComparisonChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- 仓库表现排行 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">仓库排行榜</h3>
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">查看详情</a>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg border-l-4 border-yellow-400">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">1</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">李佳琦直播间</p>
                            <p class="text-xs text-gray-500">销售冠军</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-900">¥850K</p>
                        <p class="text-sm text-green-600">+25%</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border-l-4 border-gray-400">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">2</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">薇娅直播间</p>
                            <p class="text-xs text-gray-500">表现优秀</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-900">¥620K</p>
                        <p class="text-sm text-green-600">+18%</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg border-l-4 border-orange-400">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">3</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">罗永浩直播间</p>
                            <p class="text-xs text-gray-500">稳步提升</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-900">¥420K</p>
                        <p class="text-sm text-green-600">+12%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 详细数据表格 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">详细数据</h3>
                <div class="flex items-center space-x-2">
                    <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="bi bi-download"></i>
                    </button>
                    <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="bi bi-printer"></i>
                    </button>
                    <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="bi bi-share"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            仓库名称
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            销售额
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            利润
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            利润率
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            订单数
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            增长率
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="bi bi-shop text-blue-600"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-900">李佳琦直播间</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥850,000</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥238,000</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                28.0%
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2,156</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+25.8%</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="bi bi-shop text-purple-600"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-900">薇娅直播间</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥620,000</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥167,400</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                27.0%
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1,642</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+18.3%</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="bi bi-shop text-orange-600"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-900">罗永浩直播间</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥420,000</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥113,400</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                27.0%
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1,248</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+12.7%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 