<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor extends Command
{
    protected $signature = 'app:performance-monitor';
    protected $description = '监控系统性能并生成报告';

    public function handle()
    {
        $this->info('开始性能监控...');

        $report = [
            'timestamp' => now()->toDateTimeString(),
            'database' => $this->checkDatabasePerformance(),
            'cache' => $this->checkCachePerformance(),
            'memory' => $this->checkMemoryUsage(),
            'recommendations' => []
        ];

        // 生成建议
        $report['recommendations'] = $this->generateRecommendations($report);

        // 输出报告
        $this->displayReport($report);

        // 记录到日志
        Log::info('性能监控报告', $report);

        $this->info('性能监控完成！');
    }

    private function checkDatabasePerformance()
    {
        $this->info('检查数据库性能...');

        try {
            $startTime = microtime(true);
            
            // 测试查询性能
            $users = DB::table('users')->count();
            $products = DB::table('products')->count();
            $sales = DB::table('sales')->count();
            
            $queryTime = microtime(true) - $startTime;

            // 检查慢查询
            $slowQueries = DB::select("
                SELECT 
                    COUNT(*) as count,
                    AVG(TIME_TO_SEC(TIMEDIFF(NOW(), created_at))) as avg_time
                FROM information_schema.processlist 
                WHERE command != 'Sleep' 
                AND TIME_TO_SEC(TIMEDIFF(NOW(), created_at)) > 5
            ");

            return [
                'status' => 'healthy',
                'query_time' => round($queryTime * 1000, 2) . 'ms',
                'total_users' => $users,
                'total_products' => $products,
                'total_sales' => $sales,
                'slow_queries' => $slowQueries[0]->count ?? 0,
                'avg_slow_query_time' => round($slowQueries[0]->avg_time ?? 0, 2) . 's'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkCachePerformance()
    {
        $this->info('检查缓存性能...');

        try {
            $startTime = microtime(true);
            
            // 测试缓存读写
            $testKey = 'performance_test_' . time();
            $testValue = ['test' => 'data'];
            
            Cache::put($testKey, $testValue, 60);
            $cachedValue = Cache::get($testKey);
            Cache::forget($testKey);
            
            $cacheTime = microtime(true) - $startTime;

            // 检查缓存命中率
            $cacheStats = Cache::get('cache_stats', [
                'hits' => 0,
                'misses' => 0
            ]);

            $hitRate = $cacheStats['hits'] + $cacheStats['misses'] > 0 
                ? round(($cacheStats['hits'] / ($cacheStats['hits'] + $cacheStats['misses'])) * 100, 2)
                : 0;

            return [
                'status' => 'healthy',
                'response_time' => round($cacheTime * 1000, 2) . 'ms',
                'hit_rate' => $hitRate . '%',
                'hits' => $cacheStats['hits'],
                'misses' => $cacheStats['misses']
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkMemoryUsage()
    {
        $this->info('检查内存使用...');

        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');

        return [
            'current_usage' => $this->formatBytes($memoryUsage),
            'peak_usage' => $this->formatBytes($memoryPeak),
            'memory_limit' => $memoryLimit,
            'usage_percentage' => round(($memoryUsage / $this->parseMemoryLimit($memoryLimit)) * 100, 2) . '%'
        ];
    }

    private function generateRecommendations($report)
    {
        $recommendations = [];

        // 数据库建议
        if (isset($report['database']['status']) && $report['database']['status'] === 'healthy') {
            if ($report['database']['slow_queries'] > 0) {
                $recommendations[] = '发现慢查询，建议优化数据库索引';
            }
            
            if ($report['database']['query_time'] > 100) {
                $recommendations[] = '查询时间过长，建议优化查询语句';
            }
        }

        // 缓存建议
        if (isset($report['cache']['status']) && $report['cache']['status'] === 'healthy') {
            if ($report['cache']['hit_rate'] < 80) {
                $recommendations[] = '缓存命中率较低，建议增加缓存策略';
            }
            
            if ($report['cache']['response_time'] > 10) {
                $recommendations[] = '缓存响应时间过长，建议检查缓存配置';
            }
        }

        // 内存建议
        if (isset($report['memory']['usage_percentage'])) {
            $usagePercentage = (float) str_replace('%', '', $report['memory']['usage_percentage']);
            if ($usagePercentage > 80) {
                $recommendations[] = '内存使用率过高，建议增加内存限制或优化代码';
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = '系统性能良好，无需特别优化';
        }

        return $recommendations;
    }

    private function displayReport($report)
    {
        $this->info('=== 性能监控报告 ===');
        $this->info('时间: ' . $report['timestamp']);
        
        $this->newLine();
        
        // 数据库性能
        $this->info('📊 数据库性能:');
        if (isset($report['database']['status'])) {
            $status = $report['database']['status'] === 'healthy' ? '✅ 正常' : '❌ 异常';
            $this->line("   状态: {$status}");
            
            if (isset($report['database']['query_time'])) {
                $this->line("   查询时间: {$report['database']['query_time']}");
            }
            
            if (isset($report['database']['slow_queries'])) {
                $this->line("   慢查询数量: {$report['database']['slow_queries']}");
            }
        }
        
        $this->newLine();
        
        // 缓存性能
        $this->info('💾 缓存性能:');
        if (isset($report['cache']['status'])) {
            $status = $report['cache']['status'] === 'healthy' ? '✅ 正常' : '❌ 异常';
            $this->line("   状态: {$status}");
            
            if (isset($report['cache']['hit_rate'])) {
                $this->line("   命中率: {$report['cache']['hit_rate']}");
            }
            
            if (isset($report['cache']['response_time'])) {
                $this->line("   响应时间: {$report['cache']['response_time']}");
            }
        }
        
        $this->newLine();
        
        // 内存使用
        $this->info('🧠 内存使用:');
        if (isset($report['memory']['current_usage'])) {
            $this->line("   当前使用: {$report['memory']['current_usage']}");
            $this->line("   峰值使用: {$report['memory']['peak_usage']}");
            $this->line("   使用率: {$report['memory']['usage_percentage']}");
        }
        
        $this->newLine();
        
        // 建议
        $this->info('💡 优化建议:');
        foreach ($report['recommendations'] as $recommendation) {
            $this->line("   • {$recommendation}");
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

    private function parseMemoryLimit($limit)
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }
} 