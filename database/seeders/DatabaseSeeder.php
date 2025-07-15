<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SystemConfigSeeder::class,
            UserSeeder::class,
            UserStoreSeeder::class, // 用户和仓库关联
        ]);
    }
} 