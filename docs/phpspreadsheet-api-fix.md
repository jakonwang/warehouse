# PhpSpreadsheet API修复文档

## 问题描述

### 错误信息
```
Call to undefined method PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::setCellValueByColumnAndRow()
```

### 问题原因
PhpSpreadsheet库在新版本中移除了 `setCellValueByColumnAndRow()` 方法，需要使用新的API。

## 解决方案

### 1. 修复表头写入方法

**修改前**:
```php
// 写入表头
foreach ($headers as $colIndex => $header) {
    $sheet->setCellValueByColumnAndRow($colIndex + 1, 1, $header);
}
```

**修改后**:
```php
// 写入表头
foreach ($headers as $colIndex => $header) {
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . '1', $header);
}
```

### 2. 修复数据行写入方法

**修改前**:
```php
// 写入数据行
$sheet->setCellValueByColumnAndRow(1, $rowIndex, $row->product_name ?? '未知商品');
$sheet->setCellValueByColumnAndRow(2, $rowIndex, $row->product_code ?? '未知编码');
// ... 其他列
```

**修改后**:
```php
// 写入数据行
$sheet->setCellValue('A' . $rowIndex, $row->product_name ?? '未知商品');
$sheet->setCellValue('B' . $rowIndex, $row->product_code ?? '未知编码');
// ... 其他列
```

## API变化说明

### 旧版本API (已废弃)
```php
$sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $value);
```

### 新版本API (推荐)
```php
$sheet->setCellValue($coordinate, $value);
```

### 坐标转换
```php
// 列索引转列字母
$columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);

// 完整坐标
$coordinate = $columnLetter . $rowIndex;
```

## 完整的修复代码

### 表头写入
```php
// 设置表头
$headers = [
    '商品名称',
    '商品编码', 
    '商品类型',
    '商品图片',
    '成本价格',
    '总成本',
    '当前库存',
    '最低库存',
    '最高库存',
    '库存状态',
    '最后入库时间',
    '最后出库时间',
    '仓库名称',
    '备注'
];

// 写入表头
foreach ($headers as $colIndex => $header) {
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . '1', $header);
}
```

### 数据行写入
```php
// 写入数据行
$sheet->setCellValue('A' . $rowIndex, $row->product_name ?? '未知商品');
$sheet->setCellValue('B' . $rowIndex, $row->product_code ?? '未知编码');
$sheet->setCellValue('C' . $rowIndex, $row->product_type == 'standard' ? '标品' : '盲袋');
$sheet->setCellValue('D' . $rowIndex, $imageUrl);
$sheet->setCellValue('E' . $rowIndex, number_format($costPrice, 2));
$sheet->setCellValue('F' . $rowIndex, number_format($totalCost, 2));
$sheet->setCellValue('G' . $rowIndex, $row->quantity ?? 0);
$sheet->setCellValue('H' . $rowIndex, $row->min_quantity ?? 0);
$sheet->setCellValue('I' . $rowIndex, $row->max_quantity ?? 0);
$sheet->setCellValue('J' . $rowIndex, $status);
$sheet->setCellValue('K' . $rowIndex, $row->last_stock_in_at ?? '无记录');
$sheet->setCellValue('L' . $rowIndex, $row->last_stock_out_at ?? '无记录');
$sheet->setCellValue('M' . $rowIndex, $row->store_name ?? '未知仓库');
$sheet->setCellValue('N' . $rowIndex, $row->remark ?? '');
```

## 版本兼容性

### PhpSpreadsheet版本
- **4.5.0**: 当前安装版本
- **API变化**: 移除了 `setCellValueByColumnAndRow()` 方法
- **推荐使用**: `setCellValue()` 方法

### 兼容性说明
- ✅ 新版本API更加直观
- ✅ 使用Excel坐标系统（A1, B2等）
- ✅ 更好的性能和可读性

## 测试验证

### 1. 功能测试
```php
// 测试坐标转换
$coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1); // 返回 'A'
$coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(26); // 返回 'Z'
$coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(27); // 返回 'AA'
```

### 2. 单元格写入测试
```php
$sheet->setCellValue('A1', '测试数据');
$value = $sheet->getCell('A1')->getValue(); // 应该返回 '测试数据'
```

## 相关文件

### 修改的文件
- `app/Http/Controllers/InventoryController.php` - 库存控制器

### 相关文档
- `docs/inventory-excel-export.md` - Excel导出功能文档
- `docs/inventory-export-fix.md` - 导出功能修复文档

## 更新记录

- **2025-08-02**: 修复PhpSpreadsheet API兼容性问题
- **修复内容**: 更新单元格写入方法
- **状态**: ✅ 已修复并测试通过

## 注意事项

### 1. 性能考虑
- 新API性能更好
- 减少了方法调用开销
- 更直接的内存使用

### 2. 代码可读性
- 使用Excel坐标更直观
- 代码更容易理解和维护
- 符合Excel用户习惯

### 3. 向后兼容
- 如果需要在旧版本PhpSpreadsheet上运行，需要降级库版本
- 建议使用最新版本的PhpSpreadsheet 