#!/bin/bash

# 线上分类表 slug 字段修复脚本
# 适用于 Linux 生产环境

set -e  # 遇到错误立即退出

echo "=== 开始线上分类表 slug 字段修复 ==="
echo "时间: $(date)"
echo ""

# 1. 备份数据库
echo "1. 备份数据库..."
BACKUP_FILE="backup_categories_$(date +%Y%m%d_%H%M%S).sql"
mysqldump -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} > /tmp/${BACKUP_FILE}
echo "✅ 数据库备份完成: /tmp/${BACKUP_FILE}"

# 2. 进入项目目录
echo ""
echo "2. 进入项目目录..."
cd /var/www/laravel  # 根据实际项目路径调整

# 3. 拉取最新代码
echo ""
echo "3. 拉取最新代码..."
git pull origin master

# 4. 安装依赖
echo ""
echo "4. 安装依赖..."
composer install --no-dev --optimize-autoloader

# 5. 清除缓存
echo ""
echo "5. 清除缓存..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 6. 运行数据库迁移
echo ""
echo "6. 运行数据库迁移..."
php artisan migrate --force

# 7. 运行修复脚本
echo ""
echo "7. 运行分类 slug 修复脚本..."
php scripts/fix_categories_slug.php

# 8. 验证修复结果
echo ""
echo "8. 验证修复结果..."
php artisan tinker --execute="
echo '验证空的 slug 数量: ';
echo \App\Models\Category::where('slug', '')->orWhereNull('slug')->count();
echo '验证重复的 slug 数量: ';
echo \App\Models\Category::select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->count();
"

# 9. 重启服务
echo ""
echo "9. 重启服务..."
sudo systemctl restart php8.1-fpm  # 根据实际PHP版本调整
sudo systemctl restart nginx

# 10. 健康检查
echo ""
echo "10. 健康检查..."
sleep 5
if curl -f http://localhost/health > /dev/null 2>&1; then
    echo "✅ 服务健康检查通过"
else
    echo "⚠️  服务健康检查失败，请手动检查"
fi

echo ""
echo "=== 线上修复完成 ==="
echo "时间: $(date)"
echo "请检查以下内容："
echo "1. 分类管理页面是否正常访问"
echo "2. 创建/编辑分类是否正常工作"
echo "3. 数据库备份文件: /tmp/${BACKUP_FILE}" 