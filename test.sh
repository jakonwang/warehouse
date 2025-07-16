#!/bin/bash

echo "开始修复Git符号链接问题..."

# 1. 删除符号链接
echo "删除符号链接..."
rm -rf public/storage
rm -rf storage/app/public/products

# 2. 清理缓存文件
echo "清理缓存文件..."
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
rm -rf storage/debugbar/*

# 3. 保留.gitignore文件
echo "创建.gitignore文件..."
touch storage/framework/sessions/.gitignore
touch storage/framework/views/.gitignore
touch storage/debugbar/.gitignore

# 4. 重新创建符号链接
echo "重新创建符号链接..."
php artisan storage:link

# 5. 设置权限
echo "设置文件权限..."
chmod -R 755 storage/
chmod -R 755 public/

# 6. 处理Git
echo "处理Git状态..."
git add .
git commit -m "修复符号链接问题，清理缓存文件"

# 7. 拉取并推送
echo "同步远程分支..."
git pull origin master
git push origin master

echo "修复完成！"