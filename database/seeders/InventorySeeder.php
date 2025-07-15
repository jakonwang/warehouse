<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventory;

/**
 * 库存测试数据Seeder
 * 注意：这是测试/演示数据，生产环境请不要运行此Seeder
 * 此Seeder使用已废弃的series_code字段，现已统一使用Product模型
 * 新版本库存数据应通过入库管理界面创建
 */
class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @deprecated 此Seeder已废弃，请使用新的Product+Inventory结构
     */
    public function run(): void
    {
        $inventoryData = [
            [
                'series_code' => '29',
                'quantity' => 1200,
                'min_quantity' => 100,
                'max_quantity' => 2000,
                'last_check_date' => now()->subDays(5),
                'remark' => '29元系列盲袋，热销商品'
            ],
            [
                'series_code' => '59',
                'quantity' => 45,
                'min_quantity' => 50,
                'max_quantity' => 1500,
                'last_check_date' => now()->subDays(2),
                'remark' => '59元系列盲袋，库存预警'
            ],
            [
                'series_code' => '89',
                'quantity' => 0,
                'min_quantity' => 30,
                'max_quantity' => 1000,
                'last_check_date' => now()->subDays(1),
                'remark' => '89元系列盲袋，急需补货'
            ],
            [
                'series_code' => '159',
                'quantity' => 800,
                'min_quantity' => 20,
                'max_quantity' => 800,
                'last_check_date' => now()->subDays(3),
                'remark' => '159元系列盲袋，高端商品'
            ],
            [
                'series_code' => '299',
                'quantity' => 150,
                'min_quantity' => 10,
                'max_quantity' => 500,
                'last_check_date' => now()->subDays(7),
                'remark' => '299元系列盲袋，限量版'
            ]
        ];

        foreach ($inventoryData as $data) {
            Inventory::updateOrCreate(
                ['series_code' => $data['series_code']],
                $data
            );
        }
    }
}
