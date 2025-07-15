<?php

/**
 * 简单性能测试脚本
 * 用于快速检测Laravel应用的性能问题
 */

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

class SimplePerformanceTest
{
    private $results = [];
    private $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * 运行性能测试
     */
    public function runTest()
    {
        echo "=== Laravel 系统性能测试 ===\n";
        echo "时间: " . date('Y-m-d H:i:s') . "\n\n";

        $this->testEnvironment();
        $this->testDatabase();
        $this->testCache();
        $this->testMemory();
        $this->testConfiguration();
        $this->generateReport();
    }

    /**
     * 测试环境配置
     */
    private function testEnvironment()
    {
        echo "1. 环境配置测试...\n";
        
        $debug = config('app.debug');
        $env = config('app.env');
        $cache = config('cache.default');
        $session = config('session.driver');
        
        echo "   ✅ APP_DEBUG: " . ($debug ? '启用' : '禁用') . "\n";
        echo "   ✅ 环境: $env\n";
        echo "   ✅ 缓存驱动: $cache\n";
        echo "   ✅ 会话驱动: $session\n";
        
        if ($debug) {
            echo "   ⚠️  警告: 生产环境建议禁用 APP_DEBUG\n";
        }
        
        echo "\n";
    }

    /**
     * 测试数据库性能
     */
    private function testDatabase()
    {
        echo "2. 数据库性能测试...\n";
        
        try {
            $startTime = microtime(true);
            
            // 测试基本查询
            $users = \Illuminate\Support\Facades\DB::table('users')->count();
            $products = \Illuminate\Support\Facades\DB::table('products')->count();
            $sales = \Illuminate\Support\Facades\DB::table('sales')->count();
            
            $queryTime = (microtime(true) - $startTime) * 1000;
            
            echo "   ✅ 用户数量: $users\n";
            echo "   ✅ 商品数量: $products\n";
            echo "   ✅ 销售数量: $sales\n";
            echo "   ✅ 查询时间: " . round($queryTime, 2) . "ms\n";
            
            if ($queryTime > 100) {
                echo "   ⚠️  警告: 数据库查询时间过长\n";
            }
            
            $this->results['database'] = [
                'query_time' => round($queryTime, 2),
                'status' => '正常'
            ];
            
        } catch (\Exception $e) {
            echo "   ❌ 数据库错误: " . $e->getMessage() . "\n";
            $this->results['database'] = [
                'error' => $e->getMessage(),
                'status' => '错误'
            ];
        }
        
        echo "\n";
    }

    /**
     * 测试缓存性能
     */
    private function testCache()
    {
        echo "3. 缓存性能测试...\n";
        
        try {
            $startTime = microtime(true);
            
            // 测试缓存写入
            \Illuminate\Support\Facades\Cache::put('test_key', 'test_value', 60);
            $writeTime = (microtime(true) - $startTime) * 1000;
            
            // 测试缓存读取
            $startTime = microtime(true);
            $value = \Illuminate\Support\Facades\Cache::get('test_key');
            $readTime = (microtime(true) - $startTime) * 1000;
            
            // 清理测试数据
            \Illuminate\Support\Facades\Cache::forget('test_key');
            
            echo "   ✅ 写入时间: " . round($writeTime, 2) . "ms\n";
            echo "   ✅ 读取时间: " . round($readTime, 2) . "ms\n";
            
            if ($writeTime > 50 || $readTime > 20) {
                echo "   ⚠️  警告: 缓存操作时间过长\n";
            }
            
            $this->results['cache'] = [
                'write_time' => round($writeTime, 2),
                'read_time' => round($readTime, 2),
                'status' => '正常'
            ];
            
        } catch (\Exception $e) {
            echo "   ❌ 缓存错误: " . $e->getMessage() . "\n";
            $this->results['cache'] = [
                'error' => $e->getMessage(),
                'status' => '错误'
            ];
        }
        
        echo "\n";
    }

    /**
     * 测试内存使用
     */
    private function testMemory()
    {
        echo "4. 内存使用测试...\n";
        
        $currentMemory = memory_get_usage();
        $peakMemory = memory_get_peak_usage();
        $memoryLimit = ini_get('memory_limit');
        
        $currentMB = round($currentMemory / 1024 / 1024, 2);
        $peakMB = round($peakMemory / 1024 / 1024, 2);
        
        echo "   ✅ 当前内存: {$currentMB}MB\n";
        echo "   ✅ 峰值内存: {$peakMB}MB\n";
        echo "   ✅ 内存限制: $memoryLimit\n";
        
        if ($currentMB > 100) {
            echo "   ⚠️  警告: 内存使用过高\n";
        }
        
        $this->results['memory'] = [
            'current_mb' => $currentMB,
            'peak_mb' => $peakMB,
            'limit' => $memoryLimit
        ];
        
        echo "\n";
    }

    /**
     * 测试配置
     */
    private function testConfiguration()
    {
        echo "5. 配置测试...\n";
        
        $debugbar = config('debugbar.enabled');
        $viewCache = config('view.cache');
        $sessionLifetime = config('session.lifetime');
        
        echo "   ✅ Debugbar: " . ($debugbar ? '启用' : '禁用') . "\n";
        echo "   ✅ 视图缓存: " . ($viewCache ? '启用' : '禁用') . "\n";
        echo "   ✅ 会话生命周期: {$sessionLifetime}分钟\n";
        
        if ($debugbar) {
            echo "   ⚠️  警告: 生产环境建议禁用 Debugbar\n";
        }
        
        if (!$viewCache) {
            echo "   ⚠️  警告: 建议启用视图缓存\n";
        }
        
        $this->results['configuration'] = [
            'debugbar_enabled' => $debugbar,
            'view_cache' => $viewCache,
            'session_lifetime' => $sessionLifetime
        ];
        
        echo "\n";
    }

    /**
     * 生成报告
     */
    private function generateReport()
    {
        echo "=== 性能测试报告 ===\n";
        
        $totalTime = (microtime(true) - $this->startTime) * 1000;
        
        echo "总执行时间: " . round($totalTime, 2) . "ms\n\n";
        
        // 性能建议
        echo "=== 性能建议 ===\n";
        
        if (config('app.debug')) {
            echo "🔧 建议在生产环境禁用 APP_DEBUG\n";
        }
        
        if (config('debugbar.enabled')) {
            echo "🔧 建议在生产环境禁用 Debugbar\n";
        }
        
        if (!config('view.cache')) {
            echo "🔧 建议启用视图缓存以提高性能\n";
        }
        
        if (isset($this->results['database']['query_time']) && $this->results['database']['query_time'] > 100) {
            echo "🔧 建议优化数据库查询\n";
        }
        
        if (isset($this->results['cache']['write_time']) && $this->results['cache']['write_time'] > 50) {
            echo "🔧 建议检查缓存配置\n";
        }
        
        if (isset($this->results['memory']['current_mb']) && $this->results['memory']['current_mb'] > 100) {
            echo "🔧 建议优化内存使用\n";
        }
        
        echo "\n=== 主要问题分析 ===\n";
        
        // 分析主要性能瓶颈
        $issues = [];
        
        if (config('app.debug')) {
            $issues[] = "调试模式启用 - 影响性能";
        }
        
        if (config('debugbar.enabled')) {
            $issues[] = "Debugbar启用 - 增加开销";
        }
        
        if (!config('view.cache')) {
            $issues[] = "视图缓存未启用 - 影响渲染速度";
        }
        
        if (isset($this->results['database']['query_time']) && $this->results['database']['query_time'] > 100) {
            $issues[] = "数据库查询缓慢 - 需要优化";
        }
        
        if (isset($this->results['memory']['current_mb']) && $this->results['memory']['current_mb'] > 100) {
            $issues[] = "内存使用过高 - 需要优化";
        }
        
        if (empty($issues)) {
            echo "✅ 未发现明显的性能问题\n";
        } else {
            echo "发现以下性能问题:\n";
            foreach ($issues as $index => $issue) {
                echo ($index + 1) . ". $issue\n";
            }
        }
        
        echo "\n=== 优化建议 ===\n";
        echo "1. 在生产环境禁用 APP_DEBUG 和 Debugbar\n";
        echo "2. 启用视图缓存: php artisan view:cache\n";
        echo "3. 清理缓存: php artisan cache:clear\n";
        echo "4. 优化数据库查询和索引\n";
        echo "5. 考虑使用 Redis 缓存\n";
        echo "6. 启用 OPcache 加速\n";
    }
}

// 运行测试
$test = new SimplePerformanceTest();
$test->runTest(); 