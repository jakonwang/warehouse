# 数据备份系统使用指南

## 概述

本系统提供完整的数据备份和恢复功能，支持数据库备份、文件备份和完整备份三种类型，确保数据安全和业务连续性。

## 功能特性

### 1. 备份类型

#### 1.1 数据库备份
- **功能**: 备份MySQL数据库结构和数据
- **格式**: SQL文件
- **用途**: 数据恢复、迁移、版本控制
- **文件命名**: `database_backup_YYYY-MM-DD_HH-mm-ss.sql`

#### 1.2 文件备份
- **功能**: 备份系统上传的文件和图片
- **格式**: ZIP压缩包
- **包含内容**: 
  - `storage/app/public/` 目录下的所有文件
  - `public/uploads/` 目录下的所有文件
- **文件命名**: `files_backup_YYYY-MM-DD_HH-mm-ss.zip`

#### 1.3 完整备份
- **功能**: 同时备份数据库和文件
- **格式**: ZIP压缩包
- **包含内容**:
  - 数据库SQL文件（位于 `database/` 目录）
  - 系统文件（位于 `files/` 目录）
- **文件命名**: `full_backup_YYYY-MM-DD_HH-mm-ss.zip`

### 2. 备份管理

#### 2.1 手动备份
1. 访问系统管理 → 数据备份
2. 选择备份类型：
   - 点击"数据库备份"创建数据库备份
   - 点击"文件备份"创建文件备份
   - 点击"完整备份"创建完整备份
3. 等待备份完成

#### 2.2 自动备份
使用命令行工具进行自动备份：

```bash
# 创建数据库备份
php artisan backup:auto database

# 创建文件备份
php artisan backup:auto files

# 创建完整备份
php artisan backup:auto full

# 指定保留天数（默认30天）
php artisan backup:auto database --retention=7
```

#### 2.3 定时备份
设置Cron任务实现定时备份：

```bash
# 编辑Cron任务
crontab -e

# 添加以下任务（每天凌晨2点进行完整备份）
0 2 * * * cd /path/to/your/project && php artisan backup:auto full --retention=30

# 每周日凌晨3点进行数据库备份
0 3 * * 0 cd /path/to/your/project && php artisan backup:auto database --retention=90
```

### 3. 备份恢复

#### 3.1 数据库恢复
1. 在备份管理页面找到要恢复的数据库备份
2. 点击恢复按钮（仅数据库备份支持恢复）
3. 确认恢复操作
4. 等待恢复完成

#### 3.2 文件恢复
1. 下载对应的备份文件
2. 解压ZIP文件
3. 将文件复制到对应目录：
   - `public/` 目录下的文件复制到 `storage/app/public/`
   - `uploads/` 目录下的文件复制到 `public/uploads/`

### 4. 备份存储

#### 4.1 本地存储
- **位置**: `storage/app/backups/`
- **权限**: 确保目录可写
- **清理**: 自动清理超过保留期的备份

#### 4.2 远程存储（可选）
可以配置将备份文件同步到远程存储：

```bash
# 使用rsync同步到远程服务器
rsync -avz storage/app/backups/ user@remote-server:/backup/laravel/

# 使用scp上传备份文件
scp storage/app/backups/* user@remote-server:/backup/laravel/
```

## 使用场景

### 1. 日常备份
- **频率**: 每日自动备份
- **类型**: 完整备份
- **保留**: 30天

### 2. 重要操作前备份
- 系统升级前
- 数据库结构修改前
- 大量数据导入前

### 3. 灾难恢复
- 服务器故障
- 数据丢失
- 系统迁移

## 最佳实践

### 1. 备份策略
- **每日备份**: 完整备份，保留7天
- **每周备份**: 完整备份，保留4周
- **每月备份**: 完整备份，保留12个月

### 2. 存储策略
- **本地存储**: 快速恢复
- **远程存储**: 灾难保护
- **多重备份**: 确保数据安全

### 3. 测试恢复
- 定期测试备份文件完整性
- 验证恢复流程
- 记录恢复时间

## 故障排除

### 1. 常见问题

#### 备份失败
```bash
# 检查磁盘空间
df -h

# 检查目录权限
ls -la storage/app/backups/

# 检查MySQL连接
mysql -u username -p database_name
```

#### 恢复失败
```bash
# 检查备份文件完整性
file backup_file.sql

# 检查SQL文件语法
head -n 10 backup_file.sql

# 手动恢复测试
mysql -u username -p database_name < backup_file.sql
```

### 2. 日志查看
```bash
# 查看备份日志
tail -f storage/logs/laravel.log | grep backup

# 查看错误日志
tail -f storage/logs/laravel.log | grep error
```

## 安全考虑

### 1. 备份文件安全
- 设置适当的文件权限
- 加密敏感备份文件
- 限制备份目录访问

### 2. 传输安全
- 使用SSH传输备份文件
- 加密备份文件传输
- 验证备份文件完整性

### 3. 存储安全
- 异地备份存储
- 多重备份副本
- 定期验证备份可用性

## 性能优化

### 1. 备份优化
- 使用压缩减少文件大小
- 增量备份减少备份时间
- 并行备份提高效率

### 2. 存储优化
- 定期清理旧备份
- 使用压缩存储
- 配置备份轮转

## 监控告警

### 1. 备份监控
- 监控备份执行状态
- 检查备份文件大小
- 验证备份完整性

### 2. 告警设置
```bash
# 备份失败告警
if [ $? -ne 0 ]; then
    echo "Backup failed at $(date)" | mail -s "Backup Alert" admin@example.com
fi
```

## 总结

数据备份系统是确保业务连续性的重要组成部分。通过合理的备份策略、安全的存储方案和定期的测试验证，可以有效保护系统数据安全，为业务运营提供可靠的数据保障。 