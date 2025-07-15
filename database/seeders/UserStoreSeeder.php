<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Store;

class UserStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建仓库数据
        $stores = [
            ['code' => 'LJQ001', 'name' => '李佳琦直播间', 'description' => '李佳琦直播间仓库', 'is_active' => true],
            ['code' => 'WY001', 'name' => '薇娅直播间', 'description' => '薇娅直播间仓库', 'is_active' => true],
            ['code' => 'LYH001', 'name' => '罗永浩直播间', 'description' => '罗永浩直播间仓库', 'is_active' => true],
        ];

        foreach ($stores as $storeData) {
            Store::firstOrCreate(['code' => $storeData['code']], $storeData);
        }

        // 给所有用户分配所有仓库的访问权限
        $allStores = Store::all();
        $users = User::all();

        foreach ($users as $user) {
            $user->stores()->sync($allStores->pluck('id'));
        }

        $this->command->info('Users assigned to stores successfully!');
    }
}
