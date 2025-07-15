<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizePerformance extends Command
{
    protected $signature = 'app:optimize-performance';
    protected $description = '优化应用性能，添加数据库索引和缓存配置';

    public function handle()
    {
        $this->info('开始性能优化...');

        // 1. 添加数据库索引
        $this->addDatabaseIndexes();

        // 2. 清理缓存
        $this->clearCaches();

        // 3. 预热缓存
        $this->warmupCaches();

        $this->info('性能优化完成！');
    }

    private function addDatabaseIndexes()
    {
        $this->info('添加数据库索引...');

        try {
            // 用户表索引
            if (!Schema::hasIndex('users', 'users_email_index')) {
                DB::statement('CREATE INDEX users_email_index ON users(email)');
            }
            if (!Schema::hasIndex('users', 'users_username_index')) {
                DB::statement('CREATE INDEX users_username_index ON users(username)');
            }
            if (!Schema::hasIndex('users', 'users_is_active_index')) {
                DB::statement('CREATE INDEX users_is_active_index ON users(is_active)');
            }

            // 商品表索引
            if (!Schema::hasIndex('products', 'products_type_index')) {
                DB::statement('CREATE INDEX products_type_index ON products(type)');
            }
            if (!Schema::hasIndex('products', 'products_is_active_index')) {
                DB::statement('CREATE INDEX products_is_active_index ON products(is_active)');
            }
            if (!Schema::hasIndex('products', 'products_name_index')) {
                DB::statement('CREATE INDEX products_name_index ON products(name)');
            }

            // 库存表索引
            if (!Schema::hasIndex('inventory', 'inventory_product_store_index')) {
                DB::statement('CREATE INDEX inventory_product_store_index ON inventory(product_id, store_id)');
            }
            if (!Schema::hasIndex('inventory', 'inventory_quantity_index')) {
                DB::statement('CREATE INDEX inventory_quantity_index ON inventory(quantity)');
            }

            // 销售表索引
            if (!Schema::hasIndex('sales', 'sales_created_at_index')) {
                DB::statement('CREATE INDEX sales_created_at_index ON sales(created_at)');
            }
            if (!Schema::hasIndex('sales', 'sales_store_id_index')) {
                DB::statement('CREATE INDEX sales_store_id_index ON sales(store_id)');
            }

            // 分类表索引
            if (!Schema::hasIndex('categories', 'categories_parent_id_index')) {
                DB::statement('CREATE INDEX categories_parent_id_index ON categories(parent_id)');
            }
            if (!Schema::hasIndex('categories', 'categories_is_active_index')) {
                DB::statement('CREATE INDEX categories_is_active_index ON categories(is_active)');
            }

            $this->info('数据库索引添加完成');
        } catch (\Exception $e) {
            $this->error('添加数据库索引失败: ' . $e->getMessage());
        }
    }

    private function clearCaches()
    {
        $this->info('清理缓存...');

        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');

            $this->info('缓存清理完成');
        } catch (\Exception $e) {
            $this->error('清理缓存失败: ' . $e->getMessage());
        }
    }

    private function warmupCaches()
    {
        $this->info('预热缓存...');

        try {
            // 缓存系统配置
            $configs = \App\Models\SystemConfig::all()->pluck('value', 'key')->toArray();
            \Illuminate\Support\Facades\Cache::put('system_configs', $configs, 3600);

            // 缓存常用分类
            $categories = \App\Models\Category::where('is_active', true)->get();
            \Illuminate\Support\Facades\Cache::put('active_categories', $categories, 1800);

            $this->info('缓存预热完成');
        } catch (\Exception $e) {
            $this->error('预热缓存失败: ' . $e->getMessage());
        }
    }
} 