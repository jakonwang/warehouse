# 移动端销售页面搜索功能增强

## 功能概述

为移动端销售管理页面增加全面的搜索和筛选功能，同时增加销售凭证图片显示和放大查看功能，提升移动端用户体验和数据查找效率。

## 主要功能

### 1. 搜索筛选功能
- **商品搜索**: 支持按商品名称或商品编码搜索销售记录
- **客户名称搜索**: 支持按客户姓名搜索销售记录
- **时间搜索**: 支持按时间范围筛选销售记录
- **销售员筛选**: 支持按销售员筛选
- **仓库筛选**: 支持按仓库筛选
- **金额范围筛选**: 支持按销售金额范围筛选

### 2. 销售凭证图片显示功能
- **图片展示**: 在销售记录卡片中显示销售凭证图片
- **点击放大**: 点击图片可以在模态框中放大查看
- **响应式设计**: 适配移动端显示

## 技术实现

### 后端实现

#### 控制器修改 (`app/Http/Controllers/SaleController.php`)

**移动端销售页面 (`mobileIndex` 方法)**:
```php
// 商品搜索使用 whereExists 子查询
if (request('product_search')) {
    $productSearch = request('product_search');
    $query->where(function($q) use ($productSearch) {
        // 搜索标品销售明细中的商品
        $q->whereExists(function($subQuery) use ($productSearch) {
            $subQuery->select(DB::raw(1))
                ->from('sale_details')
                ->join('products', 'sale_details.product_id', '=', 'products.id')
                ->whereRaw('sale_details.sale_id = sales.id')
                ->where(function($productQuery) use ($productSearch) {
                    $productQuery->where('products.name', 'like', '%' . $productSearch . '%')
                                ->orWhere('products.code', 'like', '%' . $productSearch . '%');
                });
        })
        // 搜索盲袋销售中的商品
        ->orWhereExists(function($subQuery) use ($productSearch) {
            $subQuery->select(DB::raw(1))
                ->from('blind_bag_sales')
                ->join('products', 'blind_bag_sales.product_id', '=', 'products.id')
                ->whereRaw('blind_bag_sales.sale_id = sales.id')
                ->where(function($productQuery) use ($productSearch) {
                    $productQuery->where('products.name', 'like', '%' . $productSearch . '%')
                                ->orWhere('products.code', 'like', '%' . $productSearch . '%');
                });
        })
        // 搜索盲袋发货明细中的商品
        ->orWhereExists(function($subQuery) use ($productSearch) {
            $subQuery->select(DB::raw(1))
                ->from('blind_bag_deliveries')
                ->join('products', 'blind_bag_deliveries.delivery_product_id', '=', 'products.id')
                ->whereRaw('blind_bag_deliveries.sale_id = sales.id')
                ->where(function($productQuery) use ($productSearch) {
                    $productQuery->where('products.name', 'like', '%' . $productSearch . '%')
                                ->orWhere('products.code', 'like', '%' . $productSearch . '%');
                });
        });
    });
}
```

### 前端实现

#### 移动端销售页面 (`resources/views/mobile/sales/index.blade.php`)

**搜索筛选区域**:
```html
<!-- 搜索筛选 -->
<div class="card p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">🔍 <x-lang key="mobile.sales.search_filter"/></h2>
    
    <form action="{{ route('mobile.sales.index') }}" method="GET" class="space-y-4">
        <!-- 商品搜索 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="mobile.sales.product_search"/>
            </label>
            <input type="text" name="product_search" value="{{ request('product_search') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="<x-lang key="mobile.sales.product_search_placeholder"/>">
        </div>
        
        <!-- 客户名称搜索 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="mobile.sales.customer_name_search"/>
            </label>
            <input type="text" name="customer_name" value="{{ request('customer_name') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="<x-lang key="mobile.sales.customer_name_placeholder"/>">
        </div>
        
        <!-- 仓库选择 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="mobile.sales.store_selection"/>
            </label>
            <select name="store_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value=""><x-lang key="mobile.sales.all_stores"/></option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                        {{ $store->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- 销售员选择 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="mobile.sales.salesperson"/>
            </label>
            <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value=""><x-lang key="mobile.sales.all_salespeople"/></option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->real_name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- 时间范围 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="mobile.sales.time_range"/>
            </label>
            <select name="period" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value=""><x-lang key="mobile.sales.all_time"/></option>
                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}><x-lang key="mobile.sales.today"/></option>
                <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}><x-lang key="mobile.sales.this_week"/></option>
                <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}><x-lang key="mobile.sales.this_month"/></option>
            </select>
        </div>
        
        <!-- 自定义时间范围 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="mobile.sales.start_date"/>
            </label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="mobile.sales.end_date"/>
            </label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <!-- 金额范围 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="mobile.sales.min_amount"/>
            </label>
            <input type="number" step="0.01" name="amount_min" value="{{ request('amount_min') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="<x-lang key="mobile.sales.min_amount_placeholder"/>">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="mobile.sales.max_amount"/>
            </label>
            <input type="number" step="0.01" name="amount_max" value="{{ request('amount_max') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="<x-lang key="mobile.sales.max_amount_placeholder"/>">
        </div>
        
        <div class="mt-4 flex justify-end space-x-3">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                <x-lang key="mobile.sales.search"/>
            </button>
            <a href="{{ route('mobile.sales.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                <x-lang key="mobile.sales.reset"/>
            </a>
        </div>
    </form>
</div>
```

**销售凭证图片显示**:
```html
<!-- 销售凭证图片 -->
@if($sale->image_path)
    <div class="mb-3">
        <div class="text-xs text-gray-600 mb-2"><x-lang key="mobile.sales.sale_proof"/>:</div>
        <div class="flex flex-wrap gap-2">
            <div class="relative">
                <img src="{{ Storage::url($sale->image_path) }}" 
                     alt="销售凭证" 
                     class="w-12 h-12 object-cover rounded-lg border border-gray-200 cursor-pointer" 
                     onclick="openMobileImageModal('{{ Storage::url($sale->image_path) }}', '销售凭证')"
                     title="销售凭证">
            </div>
        </div>
    </div>
@endif
```

**移动端图片模态框**:
```html
<!-- 移动端图片放大模态框 -->
<div id="mobileImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="relative max-w-full max-h-full p-4">
        <span class="absolute top-4 right-4 text-white text-2xl cursor-pointer z-10" onclick="closeMobileImageModal()">&times;</span>
        <img id="mobileModalImage" class="max-w-full max-h-full object-contain">
    </div>
</div>
```

**移动端 JavaScript 函数**:
```javascript
function openMobileImageModal(imageUrl, imageName) {
    document.getElementById('mobileModalImage').src = imageUrl;
    document.getElementById('mobileModalImage').alt = imageName;
    document.getElementById('mobileImageModal').classList.remove('hidden');
}

function closeMobileImageModal() {
    document.getElementById('mobileImageModal').classList.add('hidden');
}

// 点击模态框外部关闭
document.getElementById('mobileImageModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeMobileImageModal();
    }
});
```

## 多语言支持

### 中文翻译 (`resources/lang/zh_CN/mobile.php`)
```php
'sales' => [
    'search_filter' => '搜索筛选',
    'product_search' => '商品搜索',
    'product_search_placeholder' => '输入商品名称或编码',
    'customer_name_search' => '客户名称',
    'customer_name_placeholder' => '输入客户名称',
    'store_selection' => '仓库选择',
    'all_stores' => '所有仓库',
    'salesperson' => '销售员',
    'all_salespeople' => '所有销售员',
    'time_range' => '时间范围',
    'all_time' => '全部时间',
    'today' => '今天',
    'this_week' => '本周',
    'this_month' => '本月',
    'start_date' => '开始日期',
    'end_date' => '结束日期',
    'min_amount' => '最小金额',
    'max_amount' => '最大金额',
    'min_amount_placeholder' => '输入最小金额',
    'max_amount_placeholder' => '输入最大金额',
    'search' => '搜索',
    'reset' => '重置',
    'sale_proof' => '销售凭证',
    // ... 其他翻译
],
```

### 英文翻译 (`resources/lang/en/mobile.php`)
```php
'sales' => [
    'search_filter' => 'Search Filter',
    'product_search' => 'Product Search',
    'product_search_placeholder' => 'Enter product name or code',
    'customer_name_search' => 'Customer Name',
    'customer_name_placeholder' => 'Enter customer name',
    'store_selection' => 'Store Selection',
    'all_stores' => 'All Stores',
    'salesperson' => 'Salesperson',
    'all_salespeople' => 'All Salespeople',
    'time_range' => 'Time Range',
    'all_time' => 'All Time',
    'today' => 'Today',
    'this_week' => 'This Week',
    'this_month' => 'This Month',
    'start_date' => 'Start Date',
    'end_date' => 'End Date',
    'min_amount' => 'Min Amount',
    'max_amount' => 'Max Amount',
    'min_amount_placeholder' => 'Enter minimum amount',
    'max_amount_placeholder' => 'Enter maximum amount',
    'search' => 'Search',
    'reset' => 'Reset',
    'sale_proof' => 'Sale Proof',
    // ... 其他翻译
],
```

### 越南语翻译 (`resources/lang/vi/mobile.php`)
```php
'sales' => [
    'search_filter' => 'Bộ lọc tìm kiếm',
    'product_search' => 'Tìm kiếm sản phẩm',
    'product_search_placeholder' => 'Nhập tên hoặc mã sản phẩm',
    'customer_name_search' => 'Tên khách hàng',
    'customer_name_placeholder' => 'Nhập tên khách hàng',
    'store_selection' => 'Chọn kho',
    'all_stores' => 'Tất cả kho',
    'salesperson' => 'Nhân viên bán hàng',
    'all_salespeople' => 'Tất cả nhân viên',
    'time_range' => 'Khoảng thời gian',
    'all_time' => 'Tất cả thời gian',
    'today' => 'Hôm nay',
    'this_week' => 'Tuần này',
    'this_month' => 'Tháng này',
    'start_date' => 'Ngày bắt đầu',
    'end_date' => 'Ngày kết thúc',
    'min_amount' => 'Số tiền tối thiểu',
    'max_amount' => 'Số tiền tối đa',
    'min_amount_placeholder' => 'Nhập số tiền tối thiểu',
    'max_amount_placeholder' => 'Nhập số tiền tối đa',
    'search' => 'Tìm kiếm',
    'reset' => 'Đặt lại',
    'sale_proof' => 'Chứng minh bán hàng',
    // ... 其他翻译
],
```

## 分页翻译修复

### 问题描述
移动端销售页面的分页组件中，"上一页"和"下一页"按钮没有正确显示翻译文本。

### 解决方案

#### 1. 创建分页语言文件

**中文分页翻译 (`resources/lang/zh_CN/pagination.php`)**:
```php
<?php

return [
    'previous' => '&laquo; 上一页',
    'next' => '下一页 &raquo;',
    'showing' => '显示',
    'to' => '到',
    'of' => '共',
    'results' => '条记录',
    'go_to_page' => '跳转到第 :page 页',
];
```

**英文分页翻译 (`resources/lang/en/pagination.php`)**:
```php
<?php

return [
    'previous' => '&laquo; Previous',
    'next' => 'Next &raquo;',
    'showing' => 'Showing',
    'to' => 'to',
    'of' => 'of',
    'results' => 'results',
    'go_to_page' => 'Go to page :page',
];
```

**越南语分页翻译 (`resources/lang/vi/pagination.php`)**:
```php
<?php

return [
    'previous' => '&laquo; Trước',
    'next' => 'Tiếp &raquo;',
    'showing' => 'Hiển thị',
    'to' => 'đến',
    'of' => 'trong tổng số',
    'results' => 'kết quả',
    'go_to_page' => 'Đi đến trang :page',
];
```

#### 2. 修改分页模板

更新 `resources/views/vendor/pagination/tailwind.blade.php` 文件，确保所有翻译键都使用 `pagination.` 前缀：

```php
// 修改前
{!! __('Showing') !!}
{!! __('to') !!}
{!! __('of') !!}
{!! __('results') !!}
{{ __('Go to page :page', ['page' => $page]) }}

// 修改后
{!! __('pagination.showing') !!}
{!! __('pagination.to') !!}
{!! __('pagination.of') !!}
{!! __('pagination.results') !!}
{{ __('pagination.go_to_page', ['page' => $page]) }}
```

### 技术细节

1. **语言文件结构**: 分页翻译文件遵循 Laravel 的标准语言文件结构
2. **翻译键命名**: 使用 `pagination.` 前缀避免与其他翻译键冲突
3. **参数支持**: 支持动态参数，如 `:page` 占位符
4. **HTML 实体**: 使用 `&laquo;` 和 `&raquo;` 显示左右箭头符号

### 测试验证

1. **中文环境**: 分页显示"« 上一页"和"下一页 »"
2. **英文环境**: 分页显示"« Previous"和"Next »"
3. **越南语环境**: 分页显示"« Trước"和"Tiếp »"
4. **分页信息**: 显示"显示 1 到 10 共 100 条记录"等格式

## 性能优化

### 1. 数据库查询优化
- 使用 `whereExists` 子查询，避免复杂的JOIN操作
- 合理使用索引，提高搜索性能
- 分页查询，避免大量数据加载
- 预加载关联数据，减少查询次数

### 2. 移动端优化
- 图片懒加载（浏览器原生支持）
- CSS动画使用 `transform` 属性
- JavaScript事件委托，减少内存占用
- 响应式设计，适配不同屏幕尺寸

## 兼容性

### 浏览器兼容性
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

### 设备兼容性
- iOS Safari 12+
- Android Chrome 60+
- 支持触摸操作
- 响应式布局

## 部署说明

### 1. 文件上传配置
确保 `config/filesystems.php` 中配置了正确的存储驱动：
```php
'default' => env('FILESYSTEM_DISK', 'local'),
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

### 2. 存储链接
确保创建了存储链接：
```bash
php artisan storage:link
```

### 3. 权限设置
确保存储目录有正确的读写权限：
```bash
chmod -R 755 storage/
chmod -R 755 public/storage/
```

## 测试建议

### 1. 功能测试
- 测试各种搜索条件组合
- 测试图片上传和显示
- 测试模态框功能
- 测试响应式布局
- 测试分页功能和多语言显示

### 2. 性能测试
- 测试大量数据下的搜索性能
- 测试图片加载性能
- 测试移动端性能

### 3. 兼容性测试
- 测试不同浏览器
- 测试不同设备尺寸
- 测试不同网络环境

## 更新日志

### v1.0.0 (2025-01-XX)
- 初始版本发布
- 实现移动端销售搜索功能
- 实现销售凭证图片显示
- 支持多语言（中文、英文、越南语）
- 修复分页翻译问题
- 优化移动端体验 