#!/bin/bash

# 快速修复脚本 - 适用于紧急情况
# 仅修复数据库问题，不进行代码部署

set -e

echo "=== 快速修复分类表 slug 字段问题 ==="
echo "时间: $(date)"
echo ""

# 检查是否在项目目录
if [ ! -f "artisan" ]; then
    echo "❌ 错误：请在 Laravel 项目根目录执行此脚本"
    exit 1
fi

# 1. 快速备份
echo "1. 创建快速备份..."
BACKUP_FILE="quick_backup_$(date +%Y%m%d_%H%M%S).sql"
mysqldump -u${DB_USERNAME:-root} -p${DB_PASSWORD} ${DB_DATABASE} categories > /tmp/${BACKUP_FILE}
echo "✅ 备份完成: /tmp/${BACKUP_FILE}"

# 2. 运行修复脚本
echo ""
echo "2. 运行修复脚本..."
php scripts/fix_categories_slug.php

# 3. 快速验证
echo ""
echo "3. 验证修复结果..."
php artisan tinker --execute="
\$empty = \App\Models\Category::where('slug', '')->orWhereNull('slug')->count();
\$duplicate = \App\Models\Category::select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->count();
echo '空的 slug: ' . \$empty . PHP_EOL;
echo '重复的 slug: ' . \$duplicate . PHP_EOL;
if (\$empty == 0 && \$duplicate == 0) {
    echo '✅ 修复成功！' . PHP_EOL;
} else {
    echo '❌ 修复失败，请检查！' . PHP_EOL;
}
"

echo ""
echo "=== 快速修复完成 ==="
echo "请测试分类功能是否正常" 