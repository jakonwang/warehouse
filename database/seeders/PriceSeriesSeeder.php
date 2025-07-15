<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriceSeries;
use App\Models\PriceSeriesCost;

/**
 * 价格系列测试数据Seeder
 * 注意：此Seeder已废弃！系统已统一使用Product模型
 * 不要在生产环境运行此Seeder，新版本使用Product统一管理商品
 * @deprecated 已统一使用Product模型替代PriceSeries
 */
class PriceSeriesSeeder extends Seeder
{
    public function run()
    {
        $series = [
            [
                'name' => '29系列',
                'code' => '29',
                'price' => 29.00,
                'description' => '29元价格系列',
                'cost' => 15.00,
            ],
            [
                'name' => '59系列',
                'code' => '59',
                'price' => 59.00,
                'description' => '59元价格系列',
                'cost' => 30.00,
            ],
            [
                'name' => '89系列',
                'code' => '89',
                'price' => 89.00,
                'description' => '89元价格系列',
                'cost' => 45.00,
            ],
            [
                'name' => '159系列',
                'code' => '159',
                'price' => 159.00,
                'description' => '159元价格系列',
                'cost' => 80.00,
            ],
        ];

        foreach ($series as $item) {
            $cost = $item['cost'];
            unset($item['cost']);
            
            // 创建价格系列
            $priceSeries = PriceSeries::create($item);
            
            // 创建对应的成本记录
            PriceSeriesCost::create([
                'price_series_id' => $priceSeries->id,
                'cost' => $cost,
                'effective_date' => now()->toDateString(),
            ]);
        }
    }
} 