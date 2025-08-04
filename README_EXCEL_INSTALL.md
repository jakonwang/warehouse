# Excel导出功能安装说明

## 功能说明

库存系统新增了Excel导出功能，可以在导出的Excel文件中直接显示商品图片，而不仅仅是图片URL。

## 安装步骤

### 1. 安装PhpSpreadsheet库

在项目根目录执行以下命令：

```bash
composer require phpoffice/phpspreadsheet
```

### 2. 验证安装

安装完成后，可以通过以下方式验证：

```bash
php -r "if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) { echo '✅ PhpSpreadsheet库已安装'; } else { echo '❌ PhpSpreadsheet库未安装'; }"
```

### 3. 使用方法

1. 进入库存页面 (`/inventory`)
2. 点击导出按钮（下载图标）
3. 选择导出格式：
   - **导出CSV**：传统CSV格式，包含图片URL
   - **导出Excel（含图片）**：Excel格式，直接显示图片

## 功能特性

- ✅ 图片嵌入：Excel文件中直接显示商品图片
- ✅ 格式选择：支持CSV和Excel两种导出格式
- ✅ 美观样式：Excel文件包含表头样式和列宽设置
- ✅ 完整数据：包含所有库存信息，包括成本价格

## 文件格式对比

| 特性 | CSV格式 | Excel格式 |
|------|---------|-----------|
| 图片显示 | URL链接 | 直接显示图片 |
| 文件大小 | 较小 | 较大（包含图片） |
| 兼容性 | 通用 | 需要Excel软件 |
| 样式 | 纯文本 | 带样式和格式 |

## 故障排除

### 问题：Excel文件无法打开
**解决方案**：
- 检查PhpSpreadsheet是否正确安装
- 确认PHP内存限制足够
- 检查文件权限

### 问题：图片不显示
**解决方案**：
- 确认图片文件存在
- 检查图片路径是否正确
- 查看错误日志

### 问题：composer命令不存在
**解决方案**：
1. 下载并安装Composer：https://getcomposer.org/download/
2. 将Composer添加到系统PATH
3. 重新执行安装命令

## 技术文档

详细的技术文档请参考：
- `docs/inventory-excel-export.md` - Excel导出功能详细文档
- `docs/inventory-export-fix.md` - 导出功能修复文档

## 更新记录

- **2025-08-02**: 新增Excel导出功能，支持图片嵌入显示
- **版本**: v1.0.4
- **状态**: ✅ 已完成并部署 