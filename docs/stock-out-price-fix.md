# 出库创建页面商品单价显示修复文档

## 问题描述

在 `/stock-outs/create` 页面中，选择了商品后单价不显示，用户需要手动输入单价，影响出库操作的效率和准确性。

## 问题分析

### 主要问题
1. **字段名称不一致**：表单中使用的是 `price_series[0][unit_price]`，但控制器期望的是 `products[0][unit_price]`
2. **缺少商品选择后的单价自动填充功能**：选择商品后没有自动填充对应的销售价格
3. **JavaScript动态添加商品时字段名称错误**：新添加的商品项使用错误的字段名称
4. **单价字段可编辑**：单价应该是只读的，基于商品价格自动填充

### 技术问题
1. **表单字段映射错误**：前端表单字段与后端验证规则不匹配
2. **缺少价格更新逻辑**：没有JavaScript函数来处理商品选择后的价格更新
3. **商品数据属性缺失**：商品选择器缺少价格相关的data属性

## 解决方案

### 1. 修复表单字段名称

将表单中的字段名称从 `price_series` 改为 `products`，与控制器验证规则保持一致：

```php
// 修复前
<input type="number" name="price_series[0][quantity]" ...>
<input type="number" name="price_series[0][unit_price]" ...>

// 修复后
<input type="number" name="products[0][quantity]" ...>
<input type="number" name="products[0][unit_price]" ...>
```

### 2. 添加商品价格数据属性

在商品选择器中添加价格相关的data属性：

```php
<option value="{{ $product->id }}" 
        data-price="{{ $product->price }}" 
        data-cost="{{ $product->cost_price ?? 0 }}"
        {{ old('products.0.id') == $product->id ? 'selected' : '' }}>
    {{ $product->name }} ({{ $product->code }}) - ¥{{ $product->price }}
</option>
```

### 3. 实现价格自动更新功能

添加JavaScript函数来处理商品选择后的价格更新：

```javascript
// 更新商品价格
function updateProductPrice(select, index) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.value) {
        const price = selectedOption.getAttribute('data-price');
        const priceInput = select.closest('.product-item').querySelector(`input[name="products[${index}][unit_price]"]`);
        if (priceInput && price) {
            priceInput.value = price;
        }
    } else {
        // 清空价格
        const priceInput = select.closest('.product-item').querySelector(`input[name="products[${index}][unit_price]"]`);
        if (priceInput) {
            priceInput.value = '';
        }
    }
}
```

### 4. 设置单价字段为只读

将单价输入框设置为只读，防止用户手动修改：

```php
<input type="number" name="products[0][unit_price]" 
       value="{{ old('products.0.unit_price', 0) }}" 
       min="0" step="0.01" required readonly
       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
       placeholder="请输入单价">
```

### 5. 修复JavaScript动态添加商品

确保新添加的商品项使用正确的字段名称和价格更新逻辑：

```javascript
<select name="products[${productIndex}][id]" 
        class="product-select w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" 
        required onchange="updateProductPrice(this, ${productIndex})">
    <option value="">请选择商品</option>
    ${currentProducts.map(product => 
        `<option value="${product.id}" data-price="${product.price}" data-cost="${product.cost_price || 0}">${product.name} (${product.code}) - ¥${product.price}</option>`
    ).join('')}
</select>
```

## 修复内容

### 修改的文件
- `resources/views/stock-out/create.blade.php`
  - 修复表单字段名称：`price_series` → `products`
  - 添加商品价格数据属性：`data-price`, `data-cost`
  - 实现价格自动更新功能：`updateProductPrice` 函数
  - 设置单价字段为只读：`readonly` 属性
  - 修复JavaScript动态添加商品的字段名称

### 新增功能
1. **商品选择后自动填充单价**：选择商品时自动显示对应的销售价格
2. **单价字段只读**：防止用户手动修改单价，确保数据准确性
3. **价格数据属性**：商品选择器包含完整的价格信息
4. **动态价格更新**：支持动态添加商品时的价格自动更新

## 测试验证

### 测试脚本
- `scripts/test_stock_out_price.php`：测试出库创建页面的商品单价显示功能

### 测试结果
- ✅ 商品ID字段：已修复
- ✅ 数量字段：已修复
- ✅ 单价字段：已修复
- ✅ 价格更新函数：已修复
- ✅ 价格数据属性：已修复
- ✅ 获取仓库商品方法：已实现
- ✅ 单价验证规则：已实现
- ✅ 单价字段处理：已实现

### 验证要点
1. **字段名称一致性**：前端表单字段与后端验证规则完全匹配
2. **价格自动填充**：选择商品后单价字段自动显示正确价格
3. **只读属性**：单价字段设置为只读，防止误操作
4. **动态商品支持**：新添加的商品项也能正确显示价格

## 技术实现细节

### 1. 数据流
```
用户选择仓库 → 加载仓库商品 → 选择商品 → 自动填充单价 → 提交表单
```

### 2. 关键函数
- `loadStoreProducts(storeId)`：根据仓库ID加载商品列表
- `updateProductOptions()`：更新所有商品选择框的选项
- `updateProductPrice(select, index)`：更新指定商品的单价
- `addProduct()`：动态添加新的商品项

### 3. API接口
- 路由：`/api/stock-outs/store-products`
- 方法：`StockOutController@getStoreProducts`
- 返回：包含商品ID、名称、编码、价格、成本价等完整信息

## 部署注意事项

### 1. 缓存清理
```bash
php artisan view:clear
php artisan cache:clear
```

### 2. 权限验证
- 确保用户有权限访问相关仓库
- 验证商品数据的完整性

### 3. 浏览器兼容性
- 支持现代浏览器的ES6+语法
- 确保JavaScript事件处理正常工作

## 后续改进建议

### 1. 短期改进
- 为其他类似页面（入库、退货等）添加相同的价格自动填充功能
- 实现价格格式化和货币符号显示
- 添加价格验证和错误提示

### 2. 长期改进
- 实现价格历史记录和趋势分析
- 添加批量价格更新功能
- 实现价格权限控制和审计日志

## 相关文档

- [出库管理系统文档](./stock-out-management.md)
- [商品管理系统文档](./product-management.md)
- [库存管理系统文档](./inventory-enhancement.md)

## 修复完成时间

**修复完成时间**: 2025年1月3日  
**修复人员**: AI Assistant  
**版本**: v2.6.2  
**修复内容**: 出库创建页面商品单价显示功能 