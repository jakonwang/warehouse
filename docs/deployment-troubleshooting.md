# 部署问题解决指南

## 常见部署问题及解决方案

### 1. 图片上传和访问问题

#### 问题描述
产品图片上传后无法正常显示，访问路径出现错误。

#### 问题原因
- Linux服务器上storage软链接配置不正确
- 文件权限设置不当
- Web服务器配置问题

#### 解决方案

**步骤1：检查软链接状态**
```bash
# 检查public/storage是否为软链接
ls -la public/storage

# 如果显示为目录而不是软链接，需要重新创建
rm -rf public/storage
php artisan storage:link

# 验证软链接
readlink public/storage
# 应该显示: ../storage/app/public
```

**步骤2：设置正确的文件权限**
```bash
# 设置目录权限
chmod -R 755 storage
chmod -R 755 public

# 设置所有者（根据您的web服务器用户调整）
chown -R www-data:www-data storage
chown -R www-data:www-data public

# 或者使用Apache用户
chown -R apache:apache storage
chown -R apache:apache public
```

**步骤3：验证图片访问**
```bash
# 检查图片文件是否存在
ls -la storage/app/public/products/

# 测试通过软链接访问
ls -la public/storage/products/

# 检查web服务器是否可以访问
curl -I http://your-domain.com/storage/products/test.jpg
```

#### 文件路径说明
- **实际存储位置**：`storage/app/public/products/filename.jpg`
- **数据库中存储**：`products/filename.jpg`
- **访问URL**：`http://domain.com/storage/products/filename.jpg`

### 2. 数据库连接问题

#### 问题描述
系统无法连接到数据库，出现连接错误。

#### 解决方案

**检查数据库配置**
```bash
# 检查.env文件配置
cat .env | grep DB_

# 测试数据库连接
php artisan tinker
# 在tinker中执行: DB::connection()->getPdo();
```

**常见配置问题**
```env
# 正确的数据库配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**权限问题解决**
```bash
# 确保MySQL用户有正确权限
mysql -u root -p
GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

### 3. 权限和文件系统问题

#### 问题描述
系统无法写入文件，出现权限错误。

#### 解决方案

**设置正确的文件权限**
```bash
# 设置项目目录权限
sudo chown -R www-data:www-data /path/to/laravel
sudo chmod -R 755 /path/to/laravel

# 特别设置storage和bootstrap/cache权限
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

**SELinux问题（CentOS/RHEL）**
```bash
# 检查SELinux状态
sestatus

# 如果启用了SELinux，设置正确的上下文
sudo semanage fcontext -a -t httpd_exec_t "/path/to/laravel/storage(/.*)?"
sudo restorecon -Rv /path/to/laravel/storage
```

### 4. Web服务器配置问题

#### Nginx配置问题

**正确的Nginx配置**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/laravel/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**常见Nginx错误**
```bash
# 检查Nginx配置语法
sudo nginx -t

# 重启Nginx
sudo systemctl restart nginx

# 查看Nginx错误日志
sudo tail -f /var/log/nginx/error.log
```

#### Apache配置问题

**正确的Apache配置**
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/laravel/public
    
    <Directory /path/to/laravel/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

**启用必要的Apache模块**
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

### 5. 性能优化问题

#### 缓存配置
```bash
# 优化自动加载
composer install --optimize-autoloader --no-dev

# 缓存配置
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 清除缓存（开发环境）
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 定时任务设置
```bash
# 编辑crontab
crontab -e

# 添加Laravel调度任务
* * * * * cd /path/to/laravel && php artisan schedule:run >> /dev/null 2>&1
```

### 6. 日志和调试

#### 查看错误日志
```bash
# Laravel日志
tail -f storage/logs/laravel.log

# Nginx错误日志
sudo tail -f /var/log/nginx/error.log

# Apache错误日志
sudo tail -f /var/log/apache2/error.log

# PHP-FPM日志
sudo tail -f /var/log/php8.1-fpm.log
```

#### 调试模式
```env
# 开发环境启用调试
APP_DEBUG=true

# 生产环境关闭调试
APP_DEBUG=false
```

### 7. 安全配置

#### 文件权限安全
```bash
# 设置安全的文件权限
find /path/to/laravel -type f -exec chmod 644 {} \;
find /path/to/laravel -type d -exec chmod 755 {} \;

# 设置特殊目录权限
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

#### 环境变量安全
```bash
# 确保.env文件权限正确
chmod 600 .env
chown www-data:www-data .env
```

### 8. 备份和恢复

#### 数据库备份
```bash
# 创建备份脚本
php artisan backup:run

# 手动备份数据库
mysqldump -u username -p database_name > backup.sql

# 恢复数据库
mysql -u username -p database_name < backup.sql
```

#### 文件备份
```bash
# 备份上传文件
tar -czf uploads_backup.tar.gz storage/app/public/

# 恢复上传文件
tar -xzf uploads_backup.tar.gz
```

## 常见错误代码及解决方案

### 500 Internal Server Error
- 检查文件权限
- 查看错误日志
- 验证.env配置

### 404 Not Found
- 检查路由配置
- 验证.htaccess文件
- 确认URL重写规则

### 403 Forbidden
- 检查文件权限
- 验证SELinux设置
- 确认Web服务器配置

### 数据库连接错误
- 验证数据库配置
- 检查数据库服务状态
- 确认用户权限

## 联系支持

如果遇到其他问题，请：
1. 查看Laravel官方文档
2. 检查项目日志文件
3. 联系技术支持团队 