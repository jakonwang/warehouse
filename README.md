# Laravel 库存管理系统

一个基于Laravel框架开发的现代化库存管理系统，支持多仓库管理、商品管理、销售记录、库存盘点等功能。

## 功能特性

### 核心功能
- **多仓库管理** - 支持直播仓库、实体仓库、快闪店等多种仓库类型
- **商品管理** - 支持标品和盲袋两种商品类型
- **库存管理** - 实时库存跟踪、库存预警、库存盘点
- **销售管理** - 销售记录、退货处理、销售统计
- **用户权限** - 基于角色的权限管理系统
- **多语言支持** - 支持中文、英文、越南语

### 高级功能
- **盲袋系统** - 支持盲袋商品配置和发货管理
- **数据导出** - 支持Excel格式的数据导出
- **备份系统** - 自动数据库备份功能
- **性能优化** - 缓存机制、数据库优化
- **移动端适配** - 响应式设计，支持移动设备

## 技术栈

- **后端框架**: Laravel 10.x
- **数据库**: MySQL 8.0+
- **前端**: Blade模板 + Alpine.js + Tailwind CSS
- **缓存**: Redis (可选)
- **文件存储**: 本地存储 + 软链接

## 系统要求

- PHP >= 8.1
- MySQL >= 8.0
- Composer
- Node.js >= 16 (用于前端构建)
- Web服务器 (Apache/Nginx)

## 安装部署

### 1. 环境准备

```bash
# 安装PHP扩展
sudo apt-get update
sudo apt-get install php8.1 php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd

# 安装Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. 项目安装

```bash
# 克隆项目
git clone <repository-url>
cd laravel

# 安装PHP依赖
composer install --no-dev --optimize-autoloader

# 安装前端依赖
npm install
npm run build

# 复制环境配置文件
cp .env.example .env
```

### 3. 环境配置

编辑 `.env` 文件：

```env
APP_NAME="库存管理系统"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

FILESYSTEM_DISK=public
```

### 4. 数据库设置

```bash
# 生成应用密钥
php artisan key:generate

# 运行数据库迁移
php artisan migrate

# 运行数据填充
php artisan db:seed

# 创建默认管理员账户
php artisan make:admin
```

### 5. 文件存储配置

```bash
# 创建storage软链接
php artisan storage:link

# 设置目录权限
chmod -R 755 storage
chmod -R 755 public
chown -R www-data:www-data storage
chown -R www-data:www-data public
```

### 6. Web服务器配置

#### Nginx配置示例

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

#### Apache配置示例

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

## 常见问题解决

### 1. 图片上传问题

如果产品图片无法正常显示，检查以下配置：

```bash
# 检查软链接是否正确
ls -la public/storage
# 应该显示: storage -> ../storage/app/public

# 重新创建软链接
rm -rf public/storage
php artisan storage:link

# 检查目录权限
chmod -R 755 storage/app/public
chown -R www-data:www-data storage/app/public
```

### 2. 权限问题

```bash
# 设置正确的文件权限
sudo chown -R www-data:www-data /path/to/laravel
sudo chmod -R 755 /path/to/laravel/storage
sudo chmod -R 755 /path/to/laravel/public
```

### 3. 数据库连接问题

```bash
# 检查数据库连接
php artisan tinker
# 在tinker中执行: DB::connection()->getPdo();

# 清除配置缓存
php artisan config:clear
php artisan cache:clear
```

### 4. 性能优化

```bash
# 优化自动加载
composer install --optimize-autoloader --no-dev

# 缓存配置
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 设置定时任务
crontab -e
# 添加: * * * * * cd /path/to/laravel && php artisan schedule:run >> /dev/null 2>&1
```

## 开发文档

### 项目结构

```
laravel/
├── app/
│   ├── Http/Controllers/     # 控制器
│   ├── Models/              # 数据模型
│   ├── Services/            # 业务服务
│   └── View/Components/     # 视图组件
├── database/
│   ├── migrations/          # 数据库迁移
│   └── seeders/            # 数据填充
├── resources/
│   ├── views/              # 视图模板
│   └── lang/               # 多语言文件
├── routes/                 # 路由定义
├── storage/                # 文件存储
└── public/                 # 公共资源
```

### 主要功能模块

1. **用户管理** - 用户注册、登录、权限管理
2. **仓库管理** - 仓库信息、库存分配
3. **商品管理** - 商品信息、分类管理
4. **库存管理** - 库存跟踪、盘点记录
5. **销售管理** - 销售记录、退货处理
6. **盲袋系统** - 盲袋配置、发货管理
7. **数据统计** - 销售统计、库存报表

### API接口

系统提供RESTful API接口，支持移动端应用：

- `GET /api/products` - 获取商品列表
- `POST /api/sales` - 创建销售记录
- `GET /api/inventory` - 获取库存信息
- `POST /api/stock-in` - 入库操作

## 维护指南

### 日常维护

```bash
# 数据库备份
php artisan backup:run

# 清理日志文件
php artisan log:clear

# 更新系统
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate
php artisan config:cache
```

### 监控指标

- 数据库连接数
- 文件存储空间
- 系统响应时间
- 错误日志监控

## 技术支持

如有问题，请查看：
- [项目文档](./docs/)
- [开发文档](./requirements.md)
- [问题反馈](issues)

## 许可证

本项目采用 MIT 许可证，详见 [LICENSE](LICENSE) 文件。

---

**版本**: 1.0.0  
**最后更新**: 2024年12月  
**维护者**: 开发团队 