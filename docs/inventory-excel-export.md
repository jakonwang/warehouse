# 库存Excel导出功能文档

## 功能概述

### 新增功能
为库存导出功能增加了Excel格式支持，可以在导出的Excel文件中直接显示商品图片，而不仅仅是图片URL。

### 功能特色
1. **图片嵌入**：Excel文件中直接显示商品图片
2. **格式选择**：支持CSV和Excel两种导出格式
3. **美观样式**：Excel文件包含表头样式和列宽设置
4. **完整数据**：包含所有库存信息，包括成本价格

## 技术实现

### 1. 依赖库
- **PhpSpreadsheet**：用于生成Excel文件
- **安装命令**：`composer require phpoffice/phpspreadsheet`

### 2. 导出格式选择
```php
$format = $request->input('format', 'csv'); // 支持csv和excel格式

if ($format === 'excel') {
    return $this->exportToExcel($data);
} else {
    return $this->exportToCSV($data);
}
```

### 3. Excel文件生成
```php
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 设置表头样式
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4472C4'],
    ],
];
```

### 4. 图片嵌入
```php
if ($imagePath && file_exists($imagePath)) {
    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing->setName('Product_' . $row->product_code);
    $drawing->setPath($imagePath);
    $drawing->setHeight(50);
    $drawing->setWidth(50);
    $drawing->setCoordinates('D' . $rowIndex);
    $drawing->setWorksheet($sheet);
}
```

## 使用方法

### 1. 安装依赖
```bash
# 方法1：使用安装脚本
php scripts/install_excel_support.php

# 方法2：手动安装
composer require phpoffice/phpspreadsheet
```

### 2. 导出操作
1. 进入库存页面 (`/inventory`)
2. 点击导出按钮（下载图标）
3. 选择导出格式：
   - **导出CSV**：传统CSV格式，包含图片URL
   - **导出Excel（含图片）**：Excel格式，直接显示图片

### 3. 文件格式对比

| 特性 | CSV格式 | Excel格式 |
|------|---------|-----------|
| 图片显示 | URL链接 | 直接显示图片 |
| 文件大小 | 较小 | 较大（包含图片） |
| 兼容性 | 通用 | 需要Excel软件 |
| 样式 | 纯文本 | 带样式和格式 |

## Excel文件结构

### 列结构
1. **A列**：商品名称
2. **B列**：商品编码
3. **C列**：商品类型
4. **D列**：商品图片（直接显示）
5. **E列**：成本价格
6. **F列**：总成本
7. **G列**：当前库存
8. **H列**：最低库存
9. **I列**：最高库存
10. **J列**：库存状态
11. **K列**：最后入库时间
12. **L列**：最后出库时间
13. **M列**：仓库名称
14. **N列**：备注

### 样式设置
- **表头**：蓝色背景，白色粗体字
- **列宽**：根据内容自动调整
- **行高**：60像素（为图片留出空间）
- **图片尺寸**：50x50像素

## 图片处理

### 1. 图片路径支持
- **HTTP链接**：直接使用
- **uploads目录**：`public_path($row->product_image)`
- **storage目录**：`storage_path('app/public/...')`
- **其他路径**：`Storage::url()`

### 2. 图片验证
```php
if ($imagePath && file_exists($imagePath)) {
    // 添加图片到Excel
} else {
    // 只显示图片URL
}
```

### 3. 错误处理
```php
try {
    $drawing->setWorksheet($sheet);
} catch (\Exception $e) {
    \Log::warning('添加图片到Excel失败: ' . $e->getMessage());
}
```

## 性能优化

### 1. 内存管理
- 使用流式输出避免内存溢出
- 及时释放对象引用

### 2. 文件大小控制
- 图片尺寸限制为50x50像素
- 支持图片压缩（如果可能）

### 3. 错误处理
- 图片不存在时优雅降级
- 记录错误日志便于调试

## 安装指南

### 1. 自动安装
```bash
php scripts/install_excel_support.php
```

### 2. 手动安装
```bash
composer require phpoffice/phpspreadsheet
```

### 3. 验证安装
```php
if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    echo "✅ PhpSpreadsheet库已安装";
} else {
    echo "❌ PhpSpreadsheet库未安装";
}
```

## 故障排除

### 1. 常见问题

#### 问题：Excel文件无法打开
**解决方案**：
- 检查PhpSpreadsheet是否正确安装
- 确认PHP内存限制足够
- 检查文件权限

#### 问题：图片不显示
**解决方案**：
- 确认图片文件存在
- 检查图片路径是否正确
- 查看错误日志

#### 问题：文件下载失败
**解决方案**：
- 检查输出缓冲区设置
- 确认没有其他输出干扰
- 检查HTTP头设置

### 2. 调试方法
```php
// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查图片路径
var_dump($imagePath);
var_dump(file_exists($imagePath));
```

## 更新记录

- **2025-08-02**: 新增Excel导出功能，支持图片嵌入
- **功能状态**: ✅ 已完成
- **测试状态**: ✅ 已测试
- **部署状态**: ✅ 已部署

## 后续优化

### 1. 功能增强
- 支持更多图片格式（PNG、GIF等）
- 添加图片压缩功能
- 支持自定义图片尺寸

### 2. 性能优化
- 添加导出进度显示
- 支持异步导出
- 添加导出缓存

### 3. 用户体验
- 添加导出预览功能
- 支持自定义导出字段
- 添加导出模板功能 