<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PerformanceTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:test {url?} {--iterations=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试指定URL的性能';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url') ?: 'http://localhost/debug-test';
        $iterations = $this->option('iterations');

        $this->info("开始性能测试...");
        $this->info("测试URL: {$url}");
        $this->info("测试次数: {$iterations}");
        $this->line('');

        $results = [];
        $totalTime = 0;
        $totalMemory = 0;

        for ($i = 1; $i <= $iterations; $i++) {
            $this->info("第 {$i} 次测试...");
            
            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);
            
            try {
                $response = Http::timeout(30)->get($url);
                $statusCode = $response->status();
            } catch (\Exception $e) {
                $this->error("请求失败: " . $e->getMessage());
                continue;
            }
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            
            $executionTime = ($endTime - $startTime) * 1000; // 转换为毫秒
            $memoryUsed = $endMemory - $startMemory;
            
            $results[] = [
                'iteration' => $i,
                'time' => $executionTime,
                'memory' => $memoryUsed,
                'status' => $statusCode
            ];
            
            $totalTime += $executionTime;
            $totalMemory += $memoryUsed;
            
            $this->line("  执行时间: " . round($executionTime, 2) . "ms");
            $this->line("  内存使用: " . round($memoryUsed / 1024 / 1024, 2) . "MB");
            $this->line("  状态码: {$statusCode}");
            $this->line('');
        }

        // 生成统计报告
        $this->generateReport($results, $totalTime, $totalMemory, $iterations);
    }

    /**
     * 生成性能报告
     */
    private function generateReport($results, $totalTime, $totalMemory, $iterations)
    {
        $this->info("=== 性能测试报告 ===");
        $this->line('');
        
        // 计算统计数据
        $times = array_column($results, 'time');
        $memories = array_column($results, 'memory');
        
        $avgTime = $totalTime / $iterations;
        $avgMemory = $totalMemory / $iterations;
        $minTime = min($times);
        $maxTime = max($times);
        $minMemory = min($memories);
        $maxMemory = max($memories);
        
        $this->table(
            ['指标', '最小值', '平均值', '最大值'],
            [
                ['执行时间 (ms)', round($minTime, 2), round($avgTime, 2), round($maxTime, 2)],
                ['内存使用 (MB)', round($minMemory / 1024 / 1024, 2), round($avgMemory / 1024 / 1024, 2), round($maxMemory / 1024 / 1024, 2)],
            ]
        );
        
        $this->line('');
        
        // 性能评估
        $this->info("性能评估:");
        if ($avgTime < 100) {
            $this->info("✅ 执行时间: 优秀 (< 100ms)");
        } elseif ($avgTime < 500) {
            $this->warn("⚠️  执行时间: 良好 (100-500ms)");
        } else {
            $this->error("❌ 执行时间: 需要优化 (> 500ms)");
        }
        
        if ($avgMemory < 50 * 1024 * 1024) { // 50MB
            $this->info("✅ 内存使用: 优秀 (< 50MB)");
        } elseif ($avgMemory < 100 * 1024 * 1024) { // 100MB
            $this->warn("⚠️  内存使用: 良好 (50-100MB)");
        } else {
            $this->error("❌ 内存使用: 需要优化 (> 100MB)");
        }
        
        $this->line('');
        $this->info("详细结果:");
        foreach ($results as $result) {
            $status = $result['status'] === 200 ? '✅' : '❌';
            $this->line("  {$status} 第{$result['iteration']}次: " . round($result['time'], 2) . "ms, " . round($result['memory'] / 1024 / 1024, 2) . "MB");
        }
    }
} 