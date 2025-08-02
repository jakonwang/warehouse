# 线上分类表 slug 字段修复指南

## 修复概述

本文档提供在 Linux 生产环境中修复分类表 slug 字段重复值问题的完整步骤。

## 修复前准备

### 1. 环境检查
```bash
# 检查 PHP 版本
php -v

# 检查 Composer 版本
composer -V

# 检查 Git 版本
git --version

# 检查 MySQL 版本
mysql --version
```

### 2. 权限确认
确保当前用户具有以下权限：
- 项目目录读写权限
- 数据库备份权限
- 服务重启权限

## 修复步骤

### 方法一：使用自动化脚本（推荐）

1. **上传修复脚本**
```bash
# 将 fix_categories_slug_online.sh 上传到服务器
scp deploy_scripts/fix_categories_slug_online.sh user@server:/tmp/
```

2. **设置环境变量**
```bash
# 编辑脚本，设置数据库连接信息
export DB_USERNAME="your_db_user"
export DB_PASSWORD="your_db_password"
export DB_DATABASE="your_db_name"
```

3. **执行修复脚本**
```bash
# 给脚本执行权限
chmod +x /tmp/fix_categories_slug_online.sh

# 执行修复
/tmp/fix_categories_slug_online.sh
```

### 方法二：手动修复步骤

#### 步骤 1: 备份数据库
```bash
# 创建备份目录
mkdir -p /backup/$(date +%Y%m%d)

# 备份数据库
mysqldump -u${DB_USER} -p${DB_PASS} ${DB_NAME} > /backup/$(date +%Y%m%d)/backup_$(date +%H%M%S).sql

# 验证备份文件
ls -la /backup/$(date +%Y%m%d)/
```

#### 步骤 2: 代码部署
```bash
# 进入项目目录
cd /var/www/laravel

# 拉取最新代码
git pull origin master

# 安装依赖
composer install --no-dev --optimize-autoloader
```

#### 步骤 3: 清除缓存
```bash
# 清除所有缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

#### 步骤 4: 运行数据库迁移
```bash
# 运行迁移
php artisan migrate --force

# 验证迁移状态
php artisan migrate:status
```

#### 步骤 5: 运行修复脚本
```bash
# 执行修复脚本
php scripts/fix_categories_slug.php
```

#### 步骤 6: 验证修复结果
```bash
# 检查空的 slug
php artisan tinker --execute="echo '空的 slug 数量: ' . \App\Models\Category::where('slug', '')->orWhereNull('slug')->count();"

# 检查重复的 slug
php artisan tinker --execute="echo '重复的 slug 数量: ' . \App\Models\Category::select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->count();"
```

#### 步骤 7: 重启服务
```bash
# 重启 PHP-FPM（根据实际版本调整）
sudo systemctl restart php8.1-fpm

# 重启 Nginx
sudo systemctl restart nginx

# 检查服务状态
sudo systemctl status php8.1-fpm
sudo systemctl status nginx
```

## 验证修复效果

### 1. 功能测试
- 访问分类管理页面：`/categories`
- 尝试创建新分类
- 尝试编辑现有分类
- 检查分类列表显示

### 2. 数据库验证
```sql
-- 检查是否有空的 slug
SELECT COUNT(*) as empty_slugs FROM categories WHERE slug = '' OR slug IS NULL;

-- 检查是否有重复的 slug
SELECT slug, COUNT(*) as count 
FROM categories 
GROUP BY slug 
HAVING COUNT(*) > 1;

-- 查看所有分类的 slug
SELECT id, name, slug FROM categories ORDER BY id;
```

### 3. 日志检查
```bash
# 检查 Laravel 日志
tail -f storage/logs/laravel.log

# 检查 Nginx 错误日志
sudo tail -f /var/log/nginx/error.log

# 检查 PHP-FPM 日志
sudo tail -f /var/log/php8.1-fpm.log
```

## 回滚方案

如果修复过程中出现问题，可以按以下步骤回滚：

### 1. 恢复数据库
```bash
# 恢复数据库备份
mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME} < /backup/YYYYMMDD/backup_HHMMSS.sql
```

### 2. 回滚代码
```bash
# 回滚到上一个版本
git reset --hard HEAD~1

# 或者回滚到特定版本
git reset --hard <commit_hash>
```

### 3. 重新部署
```bash
# 清除缓存
php artisan optimize:clear

# 重启服务
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

## 常见问题

### Q1: 修复脚本执行失败
**A**: 检查文件权限和数据库连接信息
```bash
# 检查脚本权限
ls -la scripts/fix_categories_slug.php

# 检查数据库连接
php artisan tinker --execute="echo '数据库连接测试: ' . (DB::connection()->getPdo() ? '成功' : '失败');"
```

### Q2: 迁移失败
**A**: 检查数据库权限和表结构
```bash
# 检查迁移状态
php artisan migrate:status

# 查看具体错误
php artisan migrate --force -v
```

### Q3: 服务重启失败
**A**: 检查配置文件和服务状态
```bash
# 检查 Nginx 配置
sudo nginx -t

# 检查 PHP-FPM 配置
sudo php-fpm8.1 -t

# 查看服务状态
sudo systemctl status nginx
sudo systemctl status php8.1-fpm
```

## 联系支持

如果在修复过程中遇到问题，请提供以下信息：
1. 错误日志
2. 服务器环境信息
3. 数据库版本
4. 修复步骤执行到哪一步

## 注意事项

1. **备份优先**: 修复前必须备份数据库
2. **测试环境**: 建议先在测试环境验证
3. **维护窗口**: 选择业务低峰期进行修复
4. **监控服务**: 修复后密切监控系统状态
5. **文档记录**: 记录修复过程和结果 