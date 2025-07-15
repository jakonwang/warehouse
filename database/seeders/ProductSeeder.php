<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * 产品测试数据Seeder
 * 注意：这是测试/演示数据，生产环境请不要运行此Seeder
 * 正式部署时，应该通过管理界面手动添加商品数据
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => '盲盒A',
                'code' => 'BH001',
                'description' => '可爱的盲盒A',
                'price' => 29.00,
                'cost_price' => 15.00,
                'stock' => 100,
                'alert_stock' => 20,
                'sort_order' => 1
            ],
            [
                'name' => '盲盒B',
                'code' => 'BH002',
                'description' => '可爱的盲盒B',
                'price' => 59.00,
                'cost_price' => 30.00,
                'stock' => 80,
                'alert_stock' => 15,
                'sort_order' => 2
            ],
            [
                'name' => '盲盒C',
                'code' => 'BH003',
                'description' => '可爱的盲盒C',
                'price' => 89.00,
                'cost_price' => 45.00,
                'stock' => 60,
                'alert_stock' => 10,
                'sort_order' => 3
            ],
            [
                'name' => '盲盒D',
                'code' => 'BH004',
                'description' => '可爱的盲盒D',
                'price' => 159.00,
                'cost_price' => 80.00,
                'stock' => 40,
                'alert_stock' => 5,
                'sort_order' => 4
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 