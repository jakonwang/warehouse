@extends('layouts.app')

@section('title', '编辑库存设置')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-900">库存设置 - {{ $inventory->product->name ?? '未知商品' }}</h5>
        </div>

        <div class="p-6">
            <form action="{{ route('inventory.update', $inventory) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">商品名称</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" 
                               id="product_name" value="{{ $inventory->product->name ?? '未知商品' }}" disabled>
                    </div>

                    <div>
                        <label for="product_code" class="block text-sm font-medium text-gray-700 mb-2">商品编码</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" 
                               id="product_code" value="{{ $inventory->product->code ?? 'N/A' }}" disabled>
                    </div>

                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">当前库存</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" 
                               id="quantity" value="{{ $inventory->quantity }}" disabled>
                    </div>

                    <div>
                        <label for="store_name" class="block text-sm font-medium text-gray-700 mb-2">所属仓库</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" 
                               id="store_name" value="{{ $inventory->store->name ?? '未知仓库' }}" disabled>
                    </div>

                    <div>
                        <label for="min_quantity" class="block text-sm font-medium text-gray-700 mb-2">最小库存 <span class="text-red-500">*</span></label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('min_quantity') border-red-500 @enderror" 
                               id="min_quantity" name="min_quantity" value="{{ old('min_quantity', $inventory->min_quantity) }}" required min="0">
                        @error('min_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_quantity" class="block text-sm font-medium text-gray-700 mb-2">最大库存 <span class="text-red-500">*</span></label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('max_quantity') border-red-500 @enderror" 
                               id="max_quantity" name="max_quantity" value="{{ old('max_quantity', $inventory->max_quantity) }}" required min="0">
                        @error('max_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="remark" class="block text-sm font-medium text-gray-700 mb-2">备注</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('remark') border-red-500 @enderror" 
                              id="remark" name="remark" rows="3" placeholder="请输入备注信息">{{ old('remark', $inventory->remark) }}</textarea>
                    @error('remark')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between items-center mt-8">
                    <a href="{{ route('inventory.index') }}" class="btn-secondary">返回列表</a>
                    <button type="submit" class="btn-primary">保存设置</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 