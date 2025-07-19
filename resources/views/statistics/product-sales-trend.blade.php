@extends('layouts.app')

@section('title', '产品销售趋势分析')

@section('content')
<div class="space-y-6" x-data="productSalesTrend({
    startDate: {{ json_encode($startDate ?? '') }},
    endDate: {{ json_encode($endDate ?? '') }},
    storeId: {{ json_encode($storeId ?? '') }},
    productId: {{ json_encode($productId ?? '') }},
    trendData: {{ json_encode($trendData ?? []) }},
    dailyTrend: {{ json_encode($dailyTrend ?? []) }},
    predictionData: {{ json_encode($predictionData ?? []) }}
})">
    <!-- 页面头部 -->
    <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-black bg-opacity-10"></div>
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white bg-opacity-10 rounded-full"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-white bg-opacity-10 rounded-full"></div>
        
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="bi bi-graph-up-arrow text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-2">产品销售趋势分析</h1>
                        <p class="text-purple-100 text-lg">深度分析标准商品销售表现，智能预测市场趋势</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportData()" class="bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg backdrop-blur-sm transition-all duration-200 flex items-center">
                        <i class="bi bi-download mr-2"></i>导出数据
                    </button>
                    <button @click="refreshData()" class="bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg backdrop-blur-sm transition-all duration-200 flex items-center">
                        <i class="bi bi-arrow-clockwise mr-2"></i>刷新
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选表单 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('statistics.product-sales-trend') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">开始日期</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">结束日期</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                @if($isSuperAdmin)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">仓库</label>
                    <select name="store_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">全部仓库</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">产品</label>
                    <select name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">全部标准商品</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">显示数量</label>
                    <select name="limit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="20" {{ $limit == 20 ? 'selected' : '' }}>前20名</option>
                        <option value="50" {{ $limit == 50 ? 'selected' : '' }}>前50名</option>
                        <option value="100" {{ $limit == 100 ? 'selected' : '' }}>前100名</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <div class="flex space-x-2">
                    <button type="button" @click="setQuickDate('today')" class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">今天</button>
                    <button type="button" @click="setQuickDate('week')" class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">最近7天</button>
                    <button type="button" @click="setQuickDate('month')" class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">最近30天</button>
                </div>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 flex items-center">
                    <i class="bi bi-search mr-2"></i>查询分析
                </button>
            </div>
        </form>
    </div>

    <!-- 概览统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">总销售量</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="totalQuantity"></p>
                    <p class="text-xs text-gray-500 mt-1">各产品销售总和</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="bi bi-boxes text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">总销售额</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="'¥' + totalAmount"></p>
                    <p class="text-xs text-gray-500 mt-1">各产品销售总额</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="bi bi-currency-dollar text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">平均日销量</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="avgDailySales"></p>
                    <p class="text-xs text-gray-500 mt-1">日均销售数量</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="bi bi-graph-up text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">热销产品</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="hotProducts"></p>
                    <p class="text-xs text-gray-500 mt-1">有销售记录的产品</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="bi bi-star text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 图表展示 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 每日趋势图 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">每日销售趋势</h3>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <i class="bi bi-calendar3"></i>
                    <span>{{ $startDate }} 至 {{ $endDate }}</span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="dailyTrendChart"></canvas>
            </div>
        </div>

        <!-- 预测图表 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">销量预测</h3>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>未来7天预测</span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="predictionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 产品销售排行 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">产品销售排行（仅标准商品）</h3>
            <p class="text-sm text-gray-500 mt-1">按销量、金额等维度查看产品表现</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">排名</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">产品信息</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">销售量</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">销售额</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">平均单价</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日均销量</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($trendData as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($index < 3)
                                    <div class="w-6 h-6 rounded-full {{ $index == 0 ? 'bg-yellow-100 text-yellow-800' : ($index == 1 ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800') }} flex items-center justify-center text-sm font-bold">
                                        {{ $index + 1 }}
                                    </div>
                                @else
                                    <span class="text-gray-500 font-medium">{{ $index + 1 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($item->product_image)
                                    @php
                                        $imagePath = $item->product_image;
                                        
                                        // 强力清理路径，移除所有可能的重复和错误前缀
                                        $cleanImagePath = $imagePath;
                                        
                                        // 移除开头的斜杠
                                        $cleanImagePath = ltrim($cleanImagePath, '/');
                                        
                                        // 移除重复的storage/前缀
                                        $cleanImagePath = preg_replace('/^(storage\/)+/', '', $cleanImagePath);
                                        
                                        // 移除重复的products/前缀
                                        $cleanImagePath = preg_replace('/^(products\/)+/', 'products/', $cleanImagePath);
                                        
                                        // 确保路径以products/开头（如果不是完整URL的话）
                                        if (!filter_var($cleanImagePath, FILTER_VALIDATE_URL) && !Str::startsWith($cleanImagePath, 'products/')) {
                                            $cleanImagePath = 'products/' . $cleanImagePath;
                                        }
                                        
                                        // 生成最终URL
                                        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                            $fullUrl = $imagePath;
                                        } else {
                                            $fullUrl = asset('storage/' . $cleanImagePath);
                                        }
                                    @endphp
                                    <img src="{{ $fullUrl }}" alt="{{ $item->product_name }}" 
                                         class="w-10 h-10 rounded-lg object-cover mr-3"
                                         title="调试: 原始=[{{ $item->product_image }}] | 清理后=[{{ $cleanImagePath }}] | 最终=[{{ $fullUrl }}]">
                                @else
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-image text-gray-400"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->product_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ number_format($item->total_quantity) }}</div>
                            <div class="text-sm text-gray-500">{{ $item->order_count }} 笔订单</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">¥{{ number_format($item->total_amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">¥{{ number_format($item->avg_price, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $item->avg_daily_sales }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button @click="viewProductDetail({{ $item->product_id }})" class="text-purple-600 hover:text-purple-900 mr-3">
                                <i class="bi bi-eye"></i> 详情
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="bi bi-inbox text-4xl mb-4"></i>
                                <p class="text-lg">暂无销售数据</p>
                                <p class="text-sm">请尝试调整查询条件或日期范围</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 产品详情模态框 -->
    <div x-show="showProductModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showProductModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">产品销售详情</h3>
                        <button @click="showProductModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="h-64">
                        <canvas id="productDetailChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function productSalesTrend(config) {
    return {
        startDate: config.startDate || '',
        endDate: config.endDate || '',
        storeId: config.storeId || '',
        productId: config.productId || '',
        trendData: config.trendData || [],
        dailyTrend: config.dailyTrend || [],
        predictionData: config.predictionData || [],
        chartType: 'quantity',
        sortBy: 'quantity',
        showProductModal: false,
        dailyChart: null,
        predictionChart: null,
        productDetailChart: null,
        totalQuantity: '0',
        totalAmount: '0.00',
        avgDailySales: '0',
        hotProducts: 0,

        init() {
            console.log('Alpine component initialized', {
                trendData: this.trendData.length,
                dailyTrend: this.dailyTrend.length
            });
            this.$nextTick(() => {
                this.calculateStats();
                this.initCharts();
            });
        },

        exportData() {
            const params = new URLSearchParams();
            if (this.startDate) params.append('start_date', this.startDate);
            if (this.endDate) params.append('end_date', this.endDate);
            if (this.storeId) params.append('store_id', this.storeId);
            if (this.productId) params.append('product_id', this.productId);
            
            const url = `{{ route('statistics.product-sales-trend.export') }}?${params.toString()}`;
            window.open(url);
        },

        refreshData() {
            window.location.reload();
        },

        setQuickDate(period) {
            const today = new Date();
            let startDate, endDate;

            switch(period) {
                case 'today':
                    startDate = endDate = today.toISOString().split('T')[0];
                    break;
                case 'week':
                    startDate = new Date(today.getTime() - 6 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                    endDate = today.toISOString().split('T')[0];
                    break;
                case 'month':
                    startDate = new Date(today.getTime() - 29 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                    endDate = today.toISOString().split('T')[0];
                    break;
            }

            const startInput = document.querySelector('input[name="start_date"]');
            const endInput = document.querySelector('input[name="end_date"]');
            
            if (startInput) startInput.value = startDate;
            if (endInput) endInput.value = endDate;
            
            this.startDate = startDate;
            this.endDate = endDate;
        },

        calculateStats() {
            if (!this.trendData || this.trendData.length === 0) {
                this.totalQuantity = '0';
                this.totalAmount = '0.00';
                this.avgDailySales = '0';
                this.hotProducts = 0;
                return;
            }
            
            try {
                this.totalQuantity = this.trendData.reduce((sum, item) => sum + parseInt(item.total_quantity || 0), 0).toLocaleString();
                this.totalAmount = this.trendData.reduce((sum, item) => sum + parseFloat(item.total_amount || 0), 0).toFixed(2);
                
                if (this.dailyTrend && this.dailyTrend.length > 0) {
                    const avgQuantity = this.dailyTrend.reduce((sum, item) => sum + parseInt(item.quantity || 0), 0) / this.dailyTrend.length;
                    this.avgDailySales = Math.round(avgQuantity).toLocaleString();
                }
                
                if (this.trendData.length > 0) {
                    const avgQuantity = this.trendData.reduce((sum, item) => sum + parseInt(item.total_quantity || 0), 0) / this.trendData.length;
                    this.hotProducts = this.trendData.filter(item => parseInt(item.total_quantity || 0) > avgQuantity).length;
                }
            } catch (error) {
                console.error('Error calculating stats:', error);
            }
        },

        initCharts() {
            this.initDailyTrendChart();
            this.initPredictionChart();
        },

        initDailyTrendChart() {
            if (!this.dailyTrend || this.dailyTrend.length === 0) {
                return;
            }

            const ctx = document.getElementById('dailyTrendChart');
            if (!ctx) return;

            try {
                this.dailyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.dailyTrend.map(item => item.formatted_date || item.sale_date),
                        datasets: [{
                            label: '每日销量',
                            data: this.dailyTrend.map(item => item.quantity),
                            borderColor: 'rgb(147, 51, 234)',
                            backgroundColor: 'rgba(147, 51, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            } catch (error) {
                console.error('Error creating daily trend chart:', error);
            }
        },

        initPredictionChart() {
            if (!this.predictionData || this.predictionData.length === 0) {
                return;
            }

            const ctx = document.getElementById('predictionChart');
            if (!ctx) return;

            try {
                this.predictionChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.predictionData.map(item => item.date),
                        datasets: [{
                            label: '预测销量',
                            data: this.predictionData.map(item => item.predicted_quantity),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderDash: [5, 5],
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            } catch (error) {
                console.error('Error creating prediction chart:', error);
            }
        },

        viewProductDetail(productId) {
            this.showProductModal = true;
            this.$nextTick(() => {
                this.loadProductDetailChart(productId);
            });
        },

        loadProductDetailChart(productId) {
            if (this.productDetailChart) {
                this.productDetailChart.destroy();
            }

            const ctx = document.getElementById('productDetailChart');
            if (!ctx) return;

            try {
                this.productDetailChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: '日销量',
                            data: [],
                            borderColor: 'rgb(147, 51, 234)',
                            backgroundColor: 'rgba(147, 51, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });

                const detailUrl = '{{ route("statistics.product-sales-trend.detail") }}';
                fetch(`${detailUrl}?product_id=${productId}&days=30`)
                    .then(response => response.json())
                    .then(data => {
                        this.productDetailChart.data.labels = data.map(item => item.sale_date);
                        this.productDetailChart.data.datasets[0].data = data.map(item => item.quantity);
                        this.productDetailChart.update();
                    })
                    .catch(error => {
                        console.error('Error loading product detail:', error);
                    });
            } catch (error) {
                console.error('Error creating product detail chart:', error);
            }
        }
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }
</style>
@endpush 