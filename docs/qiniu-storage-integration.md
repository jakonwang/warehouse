# 七牛云存储集成文档

## 功能概述

系统已集成七牛云对象存储服务，所有图片上传和显示功能已迁移到七牛云服务器，提升图片访问速度和系统性能。

## 配置说明

### 1. 环境变量配置

在 `.env` 文件中添加以下配置：

```env
# 七牛云配置
QINIU_ACCESS_KEY=Gr61Z33pLjdunaMnwQDCuTJaHOeJa-cwibVgIPbF
QINIU_SECRET_KEY=QuAbFxTsqS6AGKfPAA8aufp_QTEE0rcCC7Q7l6R9
QINIU_BUCKET=videowarehouse
QINIU_DOMAIN=https://storage.banono-us.com
```

### 2. 安装依赖

运行以下命令安装七牛云SDK：

```bash
composer install
# 或
composer require qiniu/php-sdk
```

### 3. 配置验证

确保以下文件已正确配置：

- `config/filesystems.php` - 已添加七牛云存储驱动配置
- `config/app.php` - 已注册 `QiniuServiceProvider`
- `app/Providers/QiniuServiceProvider.php` - 七牛云服务提供者
- `app/Services/QiniuStorageService.php` - 七牛云存储服务
- `app/Filesystem/QiniuFilesystemAdapter.php` - 七牛云文件系统适配器

## 功能特性

### 1. 图片上传

所有图片上传功能已迁移到七牛云：

- **商品图片**：`ProductController` - 商品创建和编辑
- **销售凭证**：`SaleController` 和 `Mobile/SaleController` - 销售记录
- **退货凭证**：`ReturnController` - 退货记录
- **入库凭证**：`StockInController` - 入库记录
- **出库凭证**：`StockOutController` - 出库记录
- **盲袋销售照片**：`Mobile/BlindBagSaleController` - 盲袋销售

### 2. 图片显示

所有图片显示功能已支持七牛云URL：

- 使用 `get_image_url()` 辅助函数统一处理图片URL
- 自动识别七牛云URL和本地存储路径
- 兼容旧数据，支持平滑迁移

### 3. 图片删除

删除功能已支持七牛云：

- 使用 `deleteFromQiniu()` 方法删除七牛云文件
- 自动处理URL和路径转换

## 技术实现

### 1. 核心组件

#### QiniuStorageService
七牛云存储服务类，提供：
- `put()` - 上传文件到七牛云
- `delete()` - 删除七牛云文件
- `url()` - 获取文件URL
- `exists()` - 检查文件是否存在

#### HasQiniuUpload Trait
图片上传辅助Trait，提供：
- `uploadToQiniu()` - 上传文件到七牛云
- `deleteFromQiniu()` - 删除七牛云文件
- `getQiniuUrl()` - 获取七牛云URL

#### ImageHelper
图片URL辅助函数：
- `get_image_url()` - 统一处理图片URL（支持七牛云和本地存储）

### 2. 存储路径规范

七牛云存储路径规范：

- 商品图片：`products/文件名`
- 销售凭证：`sales/文件名`
- 退货凭证：`returns/文件名`
- 入库凭证：`stock-in/文件名`
- 出库凭证：`stock-out/文件名`
- 盲袋销售照片：`blind-bag-sales/文件名`

### 3. 数据库存储

数据库中存储的是完整的七牛云URL，格式为：
```
https://storage.banono-us.com/products/xxx.jpg
```

## 批量迁移脚本

### 使用方法

运行以下命令将现有图片批量上传到七牛云：

```bash
php scripts/migrate_images_to_qiniu.php
```

### 功能说明

脚本会自动：

1. 扫描所有需要迁移的图片：
   - 商品图片（`products` 表）
   - 销售凭证（`sales` 表）
   - 退货凭证（`return_records` 表）
   - 入库凭证（`stock_in_records` 表）
   - 出库凭证（`stock_out_records` 表）

2. 上传到七牛云并更新数据库

3. 输出迁移统计信息

### 注意事项

- 脚本会自动跳过已经是七牛云URL的记录
- 如果本地文件不存在，会跳过并记录
- 迁移过程中会显示详细的进度信息
- 建议在迁移前备份数据库

## 兼容性说明

### 旧数据兼容

系统完全兼容旧数据：

1. **图片路径识别**：
   - 自动识别七牛云URL（以 `http` 开头）
   - 自动识别本地存储路径（`storage/`、`uploads/`）
   - 自动识别七牛云路径（`products/`、`sales/` 等）

2. **显示逻辑**：
   - 优先使用七牛云URL
   - 如果不存在，回退到本地存储
   - 支持混合使用（部分图片在七牛云，部分在本地）

### 平滑迁移

迁移过程不影响系统使用：

- 新上传的图片直接存储到七牛云
- 旧图片可以逐步迁移
- 系统自动处理URL转换

## 故障排除

### 1. 上传失败

**问题**：图片上传到七牛云失败

**解决方案**：
- 检查 `.env` 文件中的七牛云配置是否正确
- 检查七牛云账户权限
- 检查网络连接
- 查看日志文件：`storage/logs/laravel.log`

### 2. 图片显示失败

**问题**：图片无法显示

**解决方案**：
- 检查七牛云域名配置是否正确
- 检查图片URL是否完整
- 检查七牛云CDN配置
- 使用浏览器开发者工具查看网络请求

### 3. 迁移脚本错误

**问题**：批量迁移脚本执行失败

**解决方案**：
- 检查七牛云配置是否正确
- 检查本地文件是否存在
- 检查文件权限
- 查看脚本输出的错误信息

## 性能优化

### 1. CDN加速

七牛云支持CDN加速，建议：

1. 在七牛云控制台配置CDN
2. 更新 `QINIU_DOMAIN` 为CDN域名
3. 清除浏览器缓存

### 2. 图片压缩

建议在上传前压缩图片：

- 使用图片压缩工具
- 设置合理的图片尺寸
- 使用WebP格式（如果支持）

### 3. 缓存策略

- 浏览器缓存：设置合理的缓存时间
- CDN缓存：利用七牛云CDN缓存
- 应用缓存：缓存图片URL（如果需要）

## 安全建议

1. **访问密钥安全**：
   - 不要将访问密钥提交到代码仓库
   - 使用环境变量存储密钥
   - 定期更换访问密钥

2. **权限控制**：
   - 使用七牛云权限策略
   - 限制上传文件类型和大小
   - 设置防盗链规则

3. **数据备份**：
   - 定期备份数据库
   - 保留本地图片备份（迁移前）
   - 使用七牛云数据备份功能

## 相关文件

- `app/Services/QiniuStorageService.php` - 七牛云存储服务
- `app/Filesystem/QiniuFilesystemAdapter.php` - 文件系统适配器
- `app/Providers/QiniuServiceProvider.php` - 服务提供者
- `app/Traits/HasQiniuUpload.php` - 上传辅助Trait
- `app/Helpers/ImageHelper.php` - 图片URL辅助函数
- `scripts/migrate_images_to_qiniu.php` - 批量迁移脚本
- `config/filesystems.php` - 存储配置

## 更新日志

### 2025-01-XX
- ✅ 集成七牛云对象存储
- ✅ 修改所有图片上传逻辑
- ✅ 修改所有图片显示逻辑
- ✅ 创建批量迁移脚本
- ✅ 添加兼容性支持

---

**开发完成时间**: 2025年1月XX日  
**版本**: v2.7.0  
**功能**: 七牛云存储集成

