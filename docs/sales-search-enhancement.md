# 销售页面搜索功能增强

## 功能概述

为销售管理页面 (`/sales`) 和移动端销售页面 (`/mobile/sales`) 增加了搜索功能和销售凭证图片显示功能。

## 主要功能

### 1. 搜索功能
- **商品搜索**: 支持按销售的商品名称或编码搜索
- **客户名称搜索**: 支持模糊搜索客户名称
- **时间范围搜索**: 支持按时间范围筛选销售记录
- **销售员筛选**: 支持按销售员筛选
- **仓库筛选**: 支持按仓库筛选
- **金额范围筛选**: 支持按销售金额范围筛选

### 2. 销售凭证图片显示
- **图片显示**: 在销售列表中显示销售凭证图片
- **点击放大**: 点击图片可以在模态框中放大查看
- **响应式设计**: 适配桌面端和移动端显示

## 技术实现

### 后端实现

#### 控制器修改 (`app/Http/Controllers/SaleController.php`)

**桌面端销售页面 (`index` 方法)**:
```php
// 商品搜索
if (request('product_search')) {
    $productSearch = request('product_search');
    $query->where(function($q) use ($productSearch) {
        // 搜索标品销售明细中的商品
        $q->whereHas('saleDetails.product', function($productQuery) use ($productSearch) {
            $productQuery->where('name', 'like', '%' . $productSearch . '%')
                        ->orWhere('code', 'like', '%' . $productSearch . '%');
        })
        // 搜索盲袋销售中的商品
        ->orWhereHas('blindBagSales.product', function($productQuery) use ($productSearch) {
            $productQuery->where('name', 'like', '%' . $productSearch . '%')
                        ->orWhere('code', 'like', '%' . $productSearch . '%');
        })
        // 搜索盲袋发货明细中的商品
        ->orWhereHas('blindBagDeliveries.deliveryProduct', function($productQuery) use ($productSearch) {
            $productQuery->where('name', 'like', '%' . $productSearch . '%')
                        ->orWhere('code', 'like', '%' . $productSearch . '%');
        });
    });
}
```

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

#### 桌面端销售页面 (`resources/views/sales/index.blade.php`)

**搜索表单**:
```html
<form method="GET" action="{{ route('sales.index') }}" class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 商品搜索 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="messages.sale.product_search"/>
            </label>
            <input type="text" name="product_search" value="{{ request('product_search') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="<x-lang key="messages.sale.product_search_placeholder"/>">
        </div>
        
        <!-- 客户名称搜索 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="messages.sale.customer_name_search"/>
            </label>
            <input type="text" name="customer_name" value="{{ request('customer_name') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="<x-lang key="messages.sale.customer_name_placeholder"/>">
        </div>
        
        <!-- 时间范围 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="messages.sale.start_date"/>
            </label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <x-lang key="messages.sale.end_date"/>
            </label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>
    
    <div class="mt-4 flex justify-end space-x-3">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
            <x-lang key="messages.sale.search"/>
        </button>
        <a href="{{ route('sales.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
            <x-lang key="messages.sale.reset"/>
        </a>
    </div>
</form>
```

**销售凭证图片显示**:
```html
<td class="px-6 py-4 whitespace-nowrap">
    <div class="product-images-container">
        @if($sale->image_path)
            <div class="product-image-wrapper">
                <img src="{{ Storage::url($sale->image_path) }}" 
                     alt="销售凭证" 
                     class="product-image" 
                     onclick="openImageModal('{{ Storage::url($sale->image_path) }}', '销售凭证')"
                     title="销售凭证">
            </div>
        @else
            <span class="text-gray-400 text-xs"><x-lang key="messages.sale.no_sale_proof"/></span>
        @endif
    </div>
</td>
```

**图片模态框**:
```html
<!-- 图片放大模态框 -->
<div id="imageModal" class="image-modal">
    <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
    <img id="modalImage" class="image-modal-content">
</div>
```

**JavaScript 函数**:
```javascript
function openImageModal(imageUrl, imageName) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('modalImage').alt = imageName;
    document.getElementById('imageModal').style.display = 'block';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

// 点击模态框外部关闭
window.onclick = function(event) {
    const modal = document.getElementById('imageModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
```

#### 移动端销售页面 (`resources/views/mobile/sales/index.blade.php`)

**搜索筛选区域**:
```html
<!-- 搜索筛选 -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
    <form method="GET" action="{{ route('mobile.sales.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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

### CSS 样式

**桌面端样式**:
```css
.product-images-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.product-image-wrapper {
    position: relative;
    display: inline-block;
}

.product-image {
    width: 3rem;
    height: 3rem;
    object-fit: cover;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    transition: transform 0.2s;
}

.product-image:hover {
    transform: scale(1.05);
}

.image-count {
    position: absolute;
    top: -0.25rem;
    right: -0.25rem;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 0.75rem;
    border-radius: 50%;
    width: 1.25rem;
    height: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
}

.image-modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}

.image-modal-close {
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    position: absolute;
    top: 15px;
    right: 35px;
    cursor: pointer;
}

.image-modal-close:hover,
.image-modal-close:focus {
    color: #bbb;
    text-decoration: none;
}
```

## 多语言支持

### 中文翻译 (`resources/lang/zh_CN/messages.php`)
```php
'sale' => [
    'product_search' => '商品搜索',
    'product_search_placeholder' => '输入商品名称或编码',
    'customer_name_search' => '客户名称',
    'customer_name_placeholder' => '输入客户名称',
    'start_date' => '开始日期',
    'end_date' => '结束日期',
    'sale_proof' => '销售凭证',
    'no_sale_proof' => '无销售凭证',
    'view_image' => '查看图片',
    // ... 其他翻译
],
```

### 移动端多语言支持

**中文 (`resources/lang/zh_CN/mobile.php`)**:
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

**英文 (`resources/lang/en/mobile.php`)**:
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

**越南语 (`resources/lang/vi/mobile.php`)**:
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

## 性能优化

### 1. 数据库查询优化
- 使用 `with()` 预加载关联数据，减少 N+1 查询问题
- 移动端使用 `DB::table` 和 `whereExists` 子查询优化复杂搜索
- 添加适当的数据库索引

### 2. 前端优化
- 图片懒加载
- 响应式图片显示
- 模态框优化，避免重复创建 DOM 元素

### 3. 缓存策略
- 视图缓存
- 查询结果缓存
- 静态资源缓存

## 兼容性

### 浏览器兼容性
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

### 设备兼容性
- 桌面端：支持所有现代浏览器
- 移动端：支持 iOS Safari 12+ 和 Android Chrome 60+

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
- 实现销售搜索功能
- 实现销售凭证图片显示
- 支持多语言（中文、英文、越南语）
- 优化移动端体验 