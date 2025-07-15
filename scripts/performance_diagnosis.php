<?php

/**
 * 系统性能诊断脚本
 * 用于检测Laravel应用的性能瓶颈
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// 启动Laravel应用
$app = Application::configure(basePath: __DIR__ . '/..')
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

class PerformanceDiagnosis
{
    private $results = [];
    private $startTime;
    private $startMemory;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }

    /**
     * 运行完整性能诊断
     */
    public function runDiagnosis()
    {
        echo "=== Laravel 系统性能诊断 ===\n";
        echo "时间: " . date('Y-m-d H:i:s') . "\n\n";

        $this->checkEnvironment();
        $this->checkDatabasePerformance();
        $this->checkCachePerformance();
        $this->checkFileSystem();
        $this->checkMemoryUsage();
        $this->checkConfiguration();
        $this->checkRoutes();
        $this->checkViews();
        $this->checkDebugbar();
        $this->generateReport();
    }

    /**
     * 检查环境配置
     */
    private function checkEnvironment()
    {
        echo "1. 环境配置检查...\n";
        
        $this->results['environment'] = [
            'app_debug' => config('app.debug'),
            'app_env' => config('app.env'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'database_connection' => config('database.default'),
            'timezone' => config('app.timezone'),
        ];

        echo "   ✅ APP_DEBUG: " . (config('app.debug') ? '启用' : '禁用') . "\n";
        echo "   ✅ 环境: " . config('app.env') . "\n";
        echo "   ✅ 缓存驱动: " . config('cache.default') . "\n";
        echo "   ✅ 数据库连接: " . config('database.default') . "\n\n";
    }

    /**
     * 检查数据库性能
     */
    private function checkDatabasePerformance()
    {
        echo "2. 数据库性能检查...\n";
        
        try {
            $startTime = microtime(true);
            
            // 测试基本查询
            $users = \Illuminate\Support\Facades\DB::table('users')->count();
            $products = \Illuminate\Support\Facades\DB::table('products')->count();
            $sales = \Illuminate\Support\Facades\DB::table('sales')->count();
            
            $queryTime = microtime(true) - $startTime;

            // 检查慢查询
            $slowQueries = \Illuminate\Support\Facades\DB::select("
                SELECT 
                    COUNT(*) as count,
                    AVG(TIME_TO_SEC(TIMEDIFF(NOW(), created_at))) as avg_time
                FROM information_schema.processlist 
                WHERE command != 'Sleep' 
                AND TIME_TO_SEC(TIMEDIFF(NOW(), created_at)) > 5
            ");

            $this->results['database'] = [
                'users_count' => $users,
                'products_count' => $products,
                'sales_count' => $sales,
                'basic_query_time' => round($queryTime * 1000, 2),
                'slow_queries_count' => $slowQueries[0]->count ?? 0,
                'connection_status' => '正常'
            ];

            echo "   ✅ 用户数量: $users\n";
            echo "   ✅ 商品数量: $products\n";
            echo "   ✅ 销售数量: $sales\n";
            echo "   ✅ 基础查询时间: " . round($queryTime * 1000, 2) . "ms\n";
            echo "   ✅ 慢查询数量: " . ($slowQueries[0]->count ?? 0) . "\n\n";

        } catch (\Exception $e) {
            $this->results['database'] = [
                'error' => $e->getMessage(),
                'connection_status' => '错误'
            ];
            echo "   ❌ 数据库连接错误: " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * 检查缓存性能
     */
    private function checkCachePerformance()
    {
        echo "3. 缓存性能检查...\n";
        
        try {
            $startTime = microtime(true);
            
            // 测试缓存写入
            \Illuminate\Support\Facades\Cache::put('test_key', 'test_value', 60);
            $writeTime = microtime(true) - $startTime;
            
            // 测试缓存读取
            $startTime = microtime(true);
            $value = \Illuminate\Support\Facades\Cache::get('test_key');
            $readTime = microtime(true) - $startTime;
            
            // 清理测试数据
            \Illuminate\Support\Facades\Cache::forget('test_key');

            $this->results['cache'] = [
                'driver' => config('cache.default'),
                'write_time' => round($writeTime * 1000, 2),
                'read_time' => round($readTime * 1000, 2),
                'status' => '正常'
            ];

            echo "   ✅ 缓存驱动: " . config('cache.default') . "\n";
            echo "   ✅ 写入时间: " . round($writeTime * 1000, 2) . "ms\n";
            echo "   ✅ 读取时间: " . round($readTime * 1000, 2) . "ms\n\n";

        } catch (\Exception $e) {
            $this->results['cache'] = [
                'error' => $e->getMessage(),
                'status' => '错误'
            ];
            echo "   ❌ 缓存错误: " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * 检查文件系统
     */
    private function checkFileSystem()
    {
        echo "4. 文件系统检查...\n";
        
        $storagePath = storage_path();
        $bootstrapPath = bootstrap_path();
        $configPath = config_path();
        
        $this->results['filesystem'] = [
            'storage_writable' => is_writable($storagePath),
            'bootstrap_writable' => is_writable($bootstrapPath),
            'config_writable' => is_writable($configPath),
            'storage_size' => $this->formatBytes($this->getDirSize($storagePath)),
            'bootstrap_size' => $this->formatBytes($this->getDirSize($bootstrapPath)),
        ];

        echo "   ✅ 存储目录可写: " . (is_writable($storagePath) ? '是' : '否') . "\n";
        echo "   ✅ Bootstrap目录可写: " . (is_writable($bootstrapPath) ? '是' : '否') . "\n";
        echo "   ✅ 存储目录大小: " . $this->formatBytes($this->getDirSize($storagePath)) . "\n";
        echo "   ✅ Bootstrap目录大小: " . $this->formatBytes($this->getDirSize($bootstrapPath)) . "\n\n";
    }

    /**
     * 检查内存使用
     */
    private function checkMemoryUsage()
    {
        echo "5. 内存使用检查...\n";
        
        $currentMemory = memory_get_usage();
        $peakMemory = memory_get_peak_usage();
        $memoryLimit = ini_get('memory_limit');
        
        $this->results['memory'] = [
            'current_usage' => $this->formatBytes($currentMemory),
            'peak_usage' => $this->formatBytes($peakMemory),
            'memory_limit' => $memoryLimit,
            'usage_percentage' => round(($currentMemory / $this->parseMemoryLimit($memoryLimit)) * 100, 2)
        ];

        echo "   ✅ 当前内存使用: " . $this->formatBytes($currentMemory) . "\n";
        echo "   ✅ 峰值内存使用: " . $this->formatBytes($peakMemory) . "\n";
        echo "   ✅ 内存限制: $memoryLimit\n";
        echo "   ✅ 使用百分比: " . round(($currentMemory / $this->parseMemoryLimit($memoryLimit)) * 100, 2) . "%\n\n";
    }

    /**
     * 检查配置
     */
    private function checkConfiguration()
    {
        echo "6. 配置检查...\n";
        
        $this->results['configuration'] = [
            'debugbar_enabled' => config('debugbar.enabled'),
            'debugbar_collectors' => config('debugbar.collectors'),
            'view_cache' => config('view.cache'),
            'session_lifetime' => config('session.lifetime'),
            'cache_ttl' => config('cache.ttl'),
        ];

        echo "   ✅ Debugbar启用: " . (config('debugbar.enabled') ? '是' : '否') . "\n";
        echo "   ✅ 视图缓存: " . (config('view.cache') ? '启用' : '禁用') . "\n";
        echo "   ✅ 会话生命周期: " . config('session.lifetime') . "分钟\n";
        echo "   ✅ 缓存TTL: " . config('cache.ttl') . "秒\n\n";
    }

    /**
     * 检查路由
     */
    private function checkRoutes()
    {
        echo "7. 路由检查...\n";
        
        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        $routeCount = count($routes);
        
        $this->results['routes'] = [
            'total_routes' => $routeCount,
            'web_routes' => 0,
            'api_routes' => 0,
        ];

        foreach ($routes as $route) {
            if (in_array('web', $route->middleware())) {
                $this->results['routes']['web_routes']++;
            }
            if (in_array('api', $route->middleware())) {
                $this->results['routes']['api_routes']++;
            }
        }

        echo "   ✅ 总路由数: $routeCount\n";
        echo "   ✅ Web路由: " . $this->results['routes']['web_routes'] . "\n";
        echo "   ✅ API路由: " . $this->results['routes']['api_routes'] . "\n\n";
    }

    /**
     * 检查视图
     */
    private function checkViews()
    {
        echo "8. 视图检查...\n";
        
        $viewsPath = resource_path('views');
        $viewFiles = $this->countFiles($viewsPath, 'blade.php');
        
        $this->results['views'] = [
            'total_views' => $viewFiles,
            'views_path' => $viewsPath,
            'views_size' => $this->formatBytes($this->getDirSize($viewsPath))
        ];

        echo "   ✅ 视图文件数量: $viewFiles\n";
        echo "   ✅ 视图目录大小: " . $this->formatBytes($this->getDirSize($viewsPath)) . "\n\n";
    }

    /**
     * 检查Debugbar
     */
    private function checkDebugbar()
    {
        echo "9. Debugbar检查...\n";
        
        $debugbarEnabled = config('debugbar.enabled');
        $debugbarStorage = config('debugbar.storage.driver');
        
        $this->results['debugbar'] = [
            'enabled' => $debugbarEnabled,
            'storage_driver' => $debugbarStorage,
            'collectors_enabled' => array_filter(config('debugbar.collectors'))
        ];

        echo "   ✅ Debugbar启用: " . ($debugbarEnabled ? '是' : '否') . "\n";
        echo "   ✅ 存储驱动: $debugbarStorage\n";
        echo "   ✅ 启用的收集器: " . count(array_filter(config('debugbar.collectors'))) . "\n\n";
    }

    /**
     * 生成报告
     */
    private function generateReport()
    {
        echo "=== 性能诊断报告 ===\n";
        
        $totalTime = microtime(true) - $this->startTime;
        $totalMemory = memory_get_usage() - $this->startMemory;
        
        echo "总执行时间: " . round($totalTime * 1000, 2) . "ms\n";
        echo "总内存使用: " . $this->formatBytes($totalMemory) . "\n\n";

        // 性能建议
        echo "=== 性能建议 ===\n";
        
        if (config('app.debug')) {
            echo "⚠️  建议在生产环境禁用 APP_DEBUG\n";
        }
        
        if (config('debugbar.enabled')) {
            echo "⚠️  建议在生产环境禁用 Debugbar\n";
        }
        
        if (!$this->results['filesystem']['storage_writable']) {
            echo "❌ 存储目录不可写，请检查权限\n";
        }
        
        if ($this->results['memory']['usage_percentage'] > 80) {
            echo "⚠️  内存使用率过高，建议优化\n";
        }
        
        if (isset($this->results['database']['basic_query_time']) && $this->results['database']['basic_query_time'] > 100) {
            echo "⚠️  数据库查询时间过长，建议优化查询\n";
        }
        
        if (isset($this->results['cache']['write_time']) && $this->results['cache']['write_time'] > 50) {
            echo "⚠️  缓存写入时间过长，建议检查缓存配置\n";
        }

        echo "\n=== 详细结果 ===\n";
        echo json_encode($this->results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

    /**
     * 辅助方法
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function getDirSize($dir)
    {
        $size = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->getDirSize($each);
        }
        return $size;
    }

    private function countFiles($dir, $extension)
    {
        $count = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            if (is_file($each) && pathinfo($each, PATHINFO_EXTENSION) === $extension) {
                $count++;
            } elseif (is_dir($each)) {
                $count += $this->countFiles($each, $extension);
            }
        }
        return $count;
    }

    private function parseMemoryLimit($limit)
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);
        
        switch ($unit) {
            case 'k': return $value * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'g': return $value * 1024 * 1024 * 1024;
            default: return $value;
        }
    }
}

// 运行诊断
$diagnosis = new PerformanceDiagnosis();
$diagnosis->runDiagnosis(); 