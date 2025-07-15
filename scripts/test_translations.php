<?php

/**
 * 翻译测试脚本
 * 用于验证翻译是否正常工作
 */

require_once __DIR__ . '/../vendor/autoload.php';

// 启动Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

class TranslationTest
{
    public function runTest()
    {
        echo "=== 翻译测试 ===\n";
        echo "时间: " . date('Y-m-d H:i:s') . "\n\n";

        // 测试缺失的翻译项
        $testKeys = [
            'messages.notifications.latest_notifications',
            'messages.logout',
            'messages.stores.all_stores',
        ];

        foreach ($testKeys as $key) {
            $translation = __($key);
            $status = $translation !== $key ? '✅ 正常' : '❌ 缺失';
            echo "{$key}: {$translation} ({$status})\n";
        }
        
        // 测试其他相关翻译
        $otherKeys = [
            'messages.system_config.title',
            'messages.profile.personal_info',
        ];
        
        echo "\n其他翻译测试:\n";
        foreach ($otherKeys as $key) {
            $translation = __($key);
            $status = $translation !== $key ? '✅ 正常' : '❌ 缺失';
            echo "{$key}: {$translation} ({$status})\n";
        }

        echo "\n=== 测试完成 ===\n";
    }
}

// 运行测试
$test = new TranslationTest();
$test->runTest(); 