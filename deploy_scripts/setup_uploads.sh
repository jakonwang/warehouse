#!/bin/bash

# 创建上传目录
mkdir -p public/uploads/sales
mkdir -p public/uploads/returns

# 设置目录权限
chmod -R 775 public/uploads
chown -R www-data:www-data public/uploads

echo "Upload directories created and permissions set." 