<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemConfig;

class SystemConfigSeeder extends Seeder
{
    public function run()
    {
        $configs = [
            [
                'key' => 'inventory_warning_threshold',
                'value' => '10',
                'description' => '库存预警阈值',
            ],
            [
                'key' => 'system_name',
                'value' => '越南盲袋库存管理系统',
                'description' => '系统名称',
            ],
            [
                'key' => 'system_language',
                'value' => 'zh_CN',
                'description' => '系统默认语言',
            ],
            [
                'key' => 'currency',
                'value' => 'CNY',
                'description' => '系统货币',
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'description' => '日期格式',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i:s',
                'description' => '时间格式',
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::create($config);
        }
    }
} 