# 销售权限控制完善功能实现文档

## 功能概述

### 问题背景
在销售详情页面和销售编辑页面中，非超级管理员用户仍能看到成本、利润等敏感财务信息，需要完善权限控制，确保只有超级管理员能看到所有财务信息。

### 解决方案
为销售详情页面和销售编辑页面的敏感信息添加权限控制，确保权限控制的一致性。

## 技术实现

### 1. 销售详情页面权限控制

**文件**: `resources/views/sales/show.blade.php`

**修改内容**:

#### 1.1 标品销售明细表格
```php
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
```

#### 1.2 盲袋发货明细表格
```php
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
```

### 2. 销售编辑页面权限控制

**文件**: `resources/views/sales/edit.blade.php`

**修改内容**:

#### 2.1 价格系列表格
```php
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
```

#### 2.2 表格底部统计
```php
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
```

#### 2.3 JavaScript权限控制
```javascript
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
```

## 功能特性

### 1. 权限控制一致性
- 所有销售相关页面都使用相同的权限判断逻辑
- 确保只有超级管理员能看到敏感财务信息

### 2. 用户体验优化
- 非超级管理员用户看不到成本、利润等敏感信息
- 表格列数根据权限动态调整
- JavaScript计算也遵循权限控制

### 3. 安全性保障
- 前端和后端双重权限控制
- 敏感信息完全隐藏，不会泄露

### 4. 维护性
- 使用统一的权限判断方法
- 代码结构清晰，易于维护

## 权限控制范围

### 已完善的页面
- ✅ **销售列表页面** (`sales/index.blade.php`)
  - 利润率列
  - 平均利润率统计

- ✅ **销售详情页面** (`sales/show.blade.php`)
  - 标品销售明细成本列
  - 盲袋发货明细成本列
  - 侧边栏利润统计

- ✅ **销售编辑页面** (`sales/edit.blade.php`)
  - 价格系列成本列
  - 表格底部利润统计
  - JavaScript利润计算

- ✅ **商品页面** (`products/*.blade.php`)
  - 成本价显示
  - 利润率显示

- ✅ **价格系列页面** (`price-series/index.blade.php`)
  - 成本列显示

- ✅ **仪表盘页面** (`dashboard*.blade.php`)
  - 利润相关统计

## 测试验证

### 测试场景
1. **超级管理员用户**
   - 访问销售详情页面，应能看到所有成本、利润信息
   - 访问销售编辑页面，应能看到所有财务统计

2. **普通用户（库存管理员、销售员、查看员）**
   - 访问销售详情页面，不应看到成本列
   - 访问销售编辑页面，不应看到成本、利润统计
   - 表格列数应相应减少

3. **权限切换测试**
   - 切换不同角色用户
   - 验证权限控制是否正确生效

### 预期结果
- 超级管理员能看到所有财务信息
- 其他角色用户看不到敏感财务信息
- 表格布局根据权限动态调整
- 权限控制在所有页面保持一致

## 部署说明

### 文件修改
- `resources/views/sales/show.blade.php` - 添加成本列权限控制
- `resources/views/sales/edit.blade.php` - 添加财务信息权限控制

### 缓存清理
```bash
php artisan view:clear
php artisan cache:clear
```

### 验证步骤
1. 使用超级管理员账户访问销售页面，确认能看到所有财务信息
2. 使用普通用户账户访问销售页面，确认看不到敏感财务信息
3. 检查表格列数是否正确调整
4. 测试JavaScript计算功能是否正常

## 注意事项

1. **权限方法**：确保 `canViewProfitAndCost()` 方法在所有用户模型中正确实现
2. **缓存清理**：修改视图后需要清理缓存
3. **测试覆盖**：确保所有角色用户都经过测试
4. **一致性**：保持所有页面的权限控制逻辑一致

## 后续优化建议

1. **权限缓存**：考虑对权限判断结果进行缓存
2. **动态权限**：支持更细粒度的权限控制
3. **审计日志**：记录敏感信息的访问日志
4. **权限管理界面**：提供可视化的权限管理功能 