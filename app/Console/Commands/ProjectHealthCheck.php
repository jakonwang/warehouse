<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class ProjectHealthCheck extends Command
{
    protected $signature = 'app:health-check';
    protected $description = '检查项目功能完整性和潜在问题';

    public function handle()
    {
        $this->info('开始项目健康检查...');

        $report = [
            'timestamp' => now()->toDateTimeString(),
            'database' => $this->checkDatabase(),
            'routes' => $this->checkRoutes(),
            'translations' => $this->checkTranslations(),
            'files' => $this->checkFiles(),
            'performance' => $this->checkPerformance(),
            'issues' => [],
            'recommendations' => []
        ];

        // 分析问题并生成建议
        $this->analyzeIssues($report);

        // 显示报告
        $this->displayReport($report);

        $this->info('项目健康检查完成！');
    }

    private function checkDatabase()
    {
        $this->info('检查数据库...');

        $tables = [
            'users', 'roles', 'stores', 'products', 'categories',
            'inventory', 'sales', 'sale_details', 'blind_bag_deliveries',
            'stock_in_records', 'stock_out_records', 'return_records',
            'activities', 'system_configs'
        ];

        $results = [];
        foreach ($tables as $table) {
            $exists = Schema::hasTable($table);
            $count = $exists ? DB::table($table)->count() : 0;
            
            $results[$table] = [
                'exists' => $exists,
                'count' => $count,
                'status' => $exists ? '✅' : '❌'
            ];
        }

        return $results;
    }

    private function checkRoutes()
    {
        $this->info('检查路由...');

        $routes = Route::getRoutes();
        $routeGroups = [
            'admin' => 0,
            'mobile' => 0,
            'api' => 0,
            'auth' => 0
        ];

        foreach ($routes as $route) {
            $uri = $route->uri();
            if (str_starts_with($uri, 'admin')) {
                $routeGroups['admin']++;
            } elseif (str_starts_with($uri, 'mobile')) {
                $routeGroups['mobile']++;
            } elseif (str_starts_with($uri, 'api')) {
                $routeGroups['api']++;
            } elseif (str_starts_with($uri, 'login') || str_starts_with($uri, 'logout')) {
                $routeGroups['auth']++;
            }
        }

        return [
            'total' => count($routes),
            'groups' => $routeGroups
        ];
    }

    private function checkTranslations()
    {
        $this->info('检查多语言翻译...');

        $languages = ['zh_CN', 'en', 'vi'];
        $results = [];

        foreach ($languages as $lang) {
            $messagesFile = resource_path("lang/{$lang}/messages.php");
            $exists = File::exists($messagesFile);
            
            if ($exists) {
                $content = File::get($messagesFile);
                $lines = count(explode("\n", $content));
                $results[$lang] = [
                    'exists' => true,
                    'lines' => $lines,
                    'status' => '✅'
                ];
            } else {
                $results[$lang] = [
                    'exists' => false,
                    'lines' => 0,
                    'status' => '❌'
                ];
            }
        }

        return $results;
    }

    private function checkFiles()
    {
        $this->info('检查关键文件...');

        $files = [
            'app/Http/Controllers/DashboardController.php' => '仪表盘控制器',
            'app/Http/Controllers/ProductController.php' => '商品控制器',
            'app/Http/Controllers/SaleController.php' => '销售控制器',
            'app/Http/Controllers/InventoryController.php' => '库存控制器',
            'app/Http/Controllers/Mobile/DashboardController.php' => '移动端仪表盘控制器',
            'resources/views/dashboard.blade.php' => '仪表盘视图',
            'resources/views/mobile/dashboard.blade.php' => '移动端仪表盘视图',
            'database/migrations/' => '数据库迁移文件',
            'routes/web.php' => 'Web路由文件',
            'routes/api.php' => 'API路由文件',
            'config/app.php' => '应用配置文件',
            '.env' => '环境配置文件'
        ];

        $results = [];
        foreach ($files as $file => $description) {
            $exists = File::exists($file);
            $results[$description] = [
                'exists' => $exists,
                'status' => $exists ? '✅' : '❌'
            ];
        }

        return $results;
    }

    private function checkPerformance()
    {
        $this->info('检查性能指标...');

        try {
            $startTime = microtime(true);
            
            // 测试数据库连接
            DB::connection()->getPdo();
            
            // 测试基本查询
            $users = DB::table('users')->count();
            $products = DB::table('products')->count();
            
            $queryTime = microtime(true) - $startTime;

            return [
                'database_connection' => '✅ 正常',
                'query_time' => round($queryTime * 1000, 2) . 'ms',
                'total_users' => $users,
                'total_products' => $products,
                'memory_usage' => $this->formatBytes(memory_get_usage(true))
            ];
        } catch (\Exception $e) {
            return [
                'database_connection' => '❌ 异常',
                'error' => $e->getMessage()
            ];
        }
    }

    private function analyzeIssues(&$report)
    {
        $issues = [];
        $recommendations = [];

        // 检查数据库问题
        foreach ($report['database'] as $table => $info) {
            if (!$info['exists']) {
                $issues[] = "数据库表 {$table} 不存在";
                $recommendations[] = "运行数据库迁移: php artisan migrate";
            }
        }

        // 检查翻译问题
        foreach ($report['translations'] as $lang => $info) {
            if (!$info['exists']) {
                $issues[] = "翻译文件 {$lang} 不存在";
                $recommendations[] = "创建翻译文件: resources/lang/{$lang}/messages.php";
            }
        }

        // 检查文件问题
        foreach ($report['files'] as $description => $info) {
            if (!$info['exists']) {
                $issues[] = "关键文件缺失: {$description}";
                $recommendations[] = "检查并创建缺失的文件";
            }
        }

        // 检查性能问题
        if (isset($report['performance']['query_time'])) {
            $queryTime = (float) str_replace('ms', '', $report['performance']['query_time']);
            if ($queryTime > 100) {
                $issues[] = "数据库查询时间过长: {$report['performance']['query_time']}";
                $recommendations[] = "优化数据库查询和索引";
            }
        }

        // 检查路由数量
        if ($report['routes']['total'] < 20) {
            $issues[] = "路由数量较少，可能功能不完整";
            $recommendations[] = "检查是否所有功能路由都已定义";
        }

        $report['issues'] = $issues;
        $report['recommendations'] = $recommendations;
    }

    private function displayReport($report)
    {
        $this->info('=== 项目健康检查报告 ===');
        $this->info('时间: ' . $report['timestamp']);
        
        $this->newLine();
        
        // 数据库状态
        $this->info('📊 数据库状态:');
        foreach ($report['database'] as $table => $info) {
            $status = $info['status'];
            $count = $info['count'];
            $this->line("   {$status} {$table}: {$count} 条记录");
        }
        
        $this->newLine();
        
        // 路由状态
        $this->info('🛣️ 路由状态:');
        $this->line("   总路由数: {$report['routes']['total']}");
        foreach ($report['routes']['groups'] as $group => $count) {
            $this->line("   {$group} 路由: {$count} 个");
        }
        
        $this->newLine();
        
        // 翻译状态
        $this->info('🌍 翻译状态:');
        foreach ($report['translations'] as $lang => $info) {
            $status = $info['status'];
            $lines = $info['lines'];
            $this->line("   {$status} {$lang}: {$lines} 行");
        }
        
        $this->newLine();
        
        // 文件状态
        $this->info('📁 文件状态:');
        foreach ($report['files'] as $description => $info) {
            $status = $info['status'];
            $this->line("   {$status} {$description}");
        }
        
        $this->newLine();
        
        // 性能状态
        $this->info('⚡ 性能状态:');
        foreach ($report['performance'] as $key => $value) {
            $this->line("   {$key}: {$value}");
        }
        
        $this->newLine();
        
        // 问题列表
        if (!empty($report['issues'])) {
            $this->warn('⚠️ 发现的问题:');
            foreach ($report['issues'] as $issue) {
                $this->line("   • {$issue}");
            }
            $this->newLine();
        }
        
        // 建议列表
        if (!empty($report['recommendations'])) {
            $this->info('💡 优化建议:');
            foreach ($report['recommendations'] as $recommendation) {
                $this->line("   • {$recommendation}");
            }
        } else {
            $this->info('✅ 项目状态良好，无需特别优化');
        }
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 