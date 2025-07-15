<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store;
use App\Models\Product;
use App\Models\StoreProduct;

class StoreProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::all();
        $products = Product::where('is_active', true)->get();

        if ($stores->isEmpty() || $products->isEmpty()) {
            $this->command->info('No stores or products found. Skipping store product assignment.');
            return;
        }

        // 为每个仓库分配一些商品
        foreach ($stores as $store) {
            // 随机选择3-5个商品分配给每个仓库
            $randomProducts = $products->random(rand(3, min(5, $products->count())));
            
            foreach ($randomProducts as $product) {
                StoreProduct::firstOrCreate([
                    'store_id' => $store->id,
                    'product_id' => $product->id,
                ], [
                    'is_active' => true,
                    'sort_order' => rand(0, 100),
                    'remark' => '自动分配'
                ]);
            }
        }

        $this->command->info('Store products assigned successfully!');
    }
} 