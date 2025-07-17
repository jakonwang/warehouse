@extends('layouts.app')

@section('title', '编辑销售记录')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">编辑销售记录</h2>
            <form action="{{ route('sales.update', $sale) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">客户姓名</label>
                        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $sale->customer_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('customer_name') border-red-500 @enderror">
                        @error('customer_name')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">客户电话</label>
                        <input type="text" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $sale->customer_phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('customer_phone') border-red-500 @enderror">
                        @error('customer_phone')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="mb-6">
                    <label for="remark" class="block text-sm font-medium text-gray-700 mb-2">备注</label>
                    <textarea id="remark" name="remark" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('remark') border-red-500 @enderror">{{ old('remark', $sale->remark) }}</textarea>
                    @error('remark')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-900 mb-4">价格系列</label>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">系列编号</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">数量</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">单价</th>
                                    @if(auth()->user()->canViewProfitAndCost())
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">成本</th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">小计</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/80 divide-y divide-gray-100">
                                @foreach($priceSeries as $series)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $series->code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number" min="0" name="price_series[{{ $loop->index }}][quantity]" value="{{ $sale->priceSeriesSaleDetails->where('series_code', $series->code)->first()?->quantity ?? 0 }}" class="w-24 px-2 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent quantity-input" data-series-code="{{ $series->code }}" data-series-price="{{ $series->price }}" data-series-cost="{{ $series->cost }}">
                                        <input type="hidden" name="price_series[{{ $loop->index }}][code]" value="{{ $series->code }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-green-600">¥{{ number_format($series->price, 2) }}</td>
                                    @if(auth()->user()->canViewProfitAndCost())
                                    <td class="px-6 py-4 whitespace-nowrap text-orange-600">¥{{ number_format($series->cost, 2) }}</td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-blue-600 font-bold subtotal">¥0.00</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="{{ auth()->user()->canViewProfitAndCost() ? '4' : '3' }}" class="text-right px-6 py-3 text-gray-700">总计：</th>
                                    <th id="total-amount" class="px-6 py-3 text-blue-600">¥0.00</th>
                                </tr>
                                @if(auth()->user()->canViewProfitAndCost())
                                <tr>
                                    <th colspan="4" class="text-right px-6 py-3 text-gray-700">总成本：</th>
                                    <th id="total-cost" class="px-6 py-3 text-orange-600">¥0.00</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-right px-6 py-3 text-gray-700">总利润：</th>
                                    <th id="total-profit" class="px-6 py-3 text-purple-600">¥0.00</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-right px-6 py-3 text-gray-700">利润率：</th>
                                    <th id="profit-rate" class="px-6 py-3 text-yellow-600">0.00%</th>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="mb-8">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">销售凭证</label>
                    @if($sale->image_path)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $sale->image_path) }}" alt="销售凭证" class="w-full max-h-60 object-contain rounded-lg">
                        </div>
                    @endif
                    <input type="file" id="image" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('image') border-red-500 @enderror">
                    @error('image')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('sales.index') }}" class="px-6 py-3 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">返回</a>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 border border-transparent rounded-lg font-medium text-white hover:from-blue-600 hover:to-purple-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function calculateTotals() {
        let totalAmount = 0;
        let totalCost = 0;
        $('.quantity-input').each(function() {
            const quantity = parseInt($(this).val()) || 0;
            const seriesPrice = parseFloat($(this).data('series-price'));
            const seriesCost = parseFloat($(this).data('series-cost'));
            const subtotal = quantity * seriesPrice;
            const cost = quantity * seriesCost;
            $(this).closest('tr').find('.subtotal').text('¥' + subtotal.toFixed(2));
            totalAmount += subtotal;
            totalCost += cost;
        });
        $('#total-amount').text('¥' + totalAmount.toFixed(2));
        
        @if(auth()->user()->canViewProfitAndCost())
        const totalProfit = totalAmount - totalCost;
        const profitRate = totalAmount > 0 ? (totalProfit / totalAmount) * 100 : 0;
        $('#total-cost').text('¥' + totalCost.toFixed(2));
        $('#total-profit').text('¥' + totalProfit.toFixed(2));
        $('#profit-rate').text(profitRate.toFixed(2) + '%');
        @endif
    }
    $('.quantity-input').on('input', calculateTotals);
    calculateTotals();
});
</script>
@endpush 