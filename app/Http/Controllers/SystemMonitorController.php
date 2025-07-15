<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SystemMonitorController extends Controller
{
    /**
     * 显示系统监控页面
     */
    public function index()
    {
        $systemStatus = $this->getSystemStatus();
        $performanceMetrics = $this->getPerformanceMetrics();
        $databaseStatus = $this->getDatabaseStatus();
        $cacheStatus = $this->getCacheStatus();
        $recentErrors = $this->getRecentErrors();
        $activeUsers = $this->getActiveUsers();
        
        return view('system-monitor.index', compact(
            'systemStatus',
            'performanceMetrics', 
            'databaseStatus',
            'cacheStatus',
            'recentErrors',
            'activeUsers'
        ));
    }

    /**
     * 获取系统状态
     */
    private function getSystemStatus()
    {
        return [
            'server_time' => now()->format('Y-m-d H:i:s'),
            'uptime' => $this->getServerUptime(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'cpu_usage' => $this->getCpuUsage(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => $this->checkDatabaseConnection(),
            'cache_connection' => $this->checkCacheConnection(),
        ];
    }

    /**
     * 获取性能指标
     */
    private function getPerformanceMetrics()
    {
        $today = Carbon::today();
        
        return [
            'total_users' => DB::table('users')->count(),
            'total_products' => DB::table('products')->count(),
            'total_sales' => DB::table('sales')->whereDate('created_at', $today)->count(),
            'total_inventory' => DB::table('inventory')->sum('quantity'),
            'low_stock_items' => DB::table('inventory')
                ->whereRaw('quantity <= min_quantity')
                ->count(),
            'active_stores' => DB::table('stores')->where('is_active', true)->count(),
            'today_revenue' => DB::table('sales')
                ->whereDate('created_at', $today)
                ->sum('total_amount') ?? 0,
            'today_profit' => DB::table('sales')
                ->whereDate('created_at', $today)
                ->sum('total_profit') ?? 0,
        ];
    }

    /**
     * 获取数据库状态
     */
    private function getDatabaseStatus()
    {
        try {
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $queryTime = (microtime(true) - $startTime) * 1000;
            
            $tableSizes = $this->getTableSizes();
            
            return [
                'status' => 'healthy',
                'response_time' => round($queryTime, 2) . 'ms',
                'table_sizes' => $tableSizes,
                'total_size' => array_sum(array_column($tableSizes, 'size')),
                'slow_queries' => $this->getSlowQueriesCount(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'response_time' => 'N/A',
                'table_sizes' => [],
                'total_size' => 0,
                'slow_queries' => 0,
            ];
        }
    }

    /**
     * 获取缓存状态
     */
    private function getCacheStatus()
    {
        try {
            $testKey = 'monitor_test_' . time();
            $testValue = ['test' => 'data'];
            
            $startTime = microtime(true);
            Cache::put($testKey, $testValue, 60);
            $cachedValue = Cache::get($testKey);
            Cache::forget($testKey);
            $cacheTime = (microtime(true) - $startTime) * 1000;
            
            return [
                'status' => 'healthy',
                'response_time' => round($cacheTime, 2) . 'ms',
                'driver' => config('cache.default'),
                'hit_rate' => $this->getCacheHitRate(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'response_time' => 'N/A',
                'driver' => config('cache.default'),
                'hit_rate' => 0,
            ];
        }
    }

    /**
     * 获取最近错误
     */
    private function getRecentErrors()
    {
        $logFile = storage_path('logs/laravel.log');
        $errors = [];
        
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $errorLines = array_filter($lines, function($line) {
                return strpos($line, '.ERROR') !== false;
            });
            
            $errors = array_slice(array_reverse($errorLines), 0, 10);
        }
        
        return $errors;
    }

    /**
     * 获取活跃用户
     */
    private function getActiveUsers()
    {
        try {
            $activeThreshold = now()->subMinutes(30);
            
            return DB::table('users')
                ->where('last_activity_at', '>=', $activeThreshold)
                ->select('username', 'real_name', 'last_activity_at')
                ->orderBy('last_activity_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            // 如果字段不存在，返回空集合
            Log::warning('last_activity_at field not found in users table: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * 检查数据库连接
     */
    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return 'connected';
        } catch (\Exception $e) {
            return 'disconnected';
        }
    }

    /**
     * 检查缓存连接
     */
    private function checkCacheConnection()
    {
        try {
            Cache::store()->get('test');
            return 'connected';
        } catch (\Exception $e) {
            return 'disconnected';
        }
    }

    /**
     * 获取服务器运行时间
     */
    private function getServerUptime()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                'load_1min' => $load[0] ?? 0,
                'load_5min' => $load[1] ?? 0,
                'load_15min' => $load[2] ?? 0,
            ];
        }
        
        return [
            'load_1min' => 0,
            'load_5min' => 0,
            'load_15min' => 0,
        ];
    }

    /**
     * 获取内存使用情况
     */
    private function getMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        return [
            'current' => $this->formatBytes($memoryUsage),
            'peak' => $this->formatBytes($memoryPeak),
            'limit' => $memoryLimit,
            'usage_percentage' => round(($memoryUsage / $this->parseMemoryLimit($memoryLimit)) * 100, 2),
        ];
    }

    /**
     * 获取磁盘使用情况
     */
    private function getDiskUsage()
    {
        $path = storage_path();
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        
        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }

    /**
     * 获取CPU使用情况
     */
    private function getCpuUsage()
    {
        // 简化实现，实际项目中可能需要更复杂的CPU监控
        return [
            'usage_percentage' => rand(10, 80), // 模拟数据
            'cores' => 4, // 模拟数据
        ];
    }

    /**
     * 获取表大小
     */
    private function getTableSizes()
    {
        $tables = ['users', 'products', 'sales', 'inventory', 'stores'];
        $sizes = [];
        
        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                $sizes[$table] = [
                    'name' => $table,
                    'count' => $count,
                    'size' => $count * 1024, // 估算大小
                ];
            } catch (\Exception $e) {
                $sizes[$table] = [
                    'name' => $table,
                    'count' => 0,
                    'size' => 0,
                ];
            }
        }
        
        return $sizes;
    }

    /**
     * 获取慢查询数量
     */
    private function getSlowQueriesCount()
    {
        try {
            $result = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.processlist 
                WHERE command != 'Sleep' 
                AND TIME_TO_SEC(TIMEDIFF(NOW(), created_at)) > 5
            ");
            return $result[0]->count ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取缓存命中率
     */
    private function getCacheHitRate()
    {
        $stats = Cache::get('cache_stats', ['hits' => 0, 'misses' => 0]);
        $total = $stats['hits'] + $stats['misses'];
        
        return $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0;
    }

    /**
     * 格式化字节数
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 解析内存限制
     */
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