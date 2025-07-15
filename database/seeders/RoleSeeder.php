<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // 创建角色
        $roles = [
            [
                'name' => '超级管理员',
                'code' => 'super_admin',
                'display_name' => '超级管理员',
                'description' => '系统超级管理员，拥有所有权限',
            ],
            [
                'name' => '管理员',
                'code' => 'admin',
                'display_name' => '管理员',
                'description' => '系统管理员，拥有大部分权限',
            ],
            [
                'name' => '库存管理员',
                'code' => 'inventory_manager',
                'display_name' => '库存管理员',
                'description' => '负责库存管理',
            ],
            [
                'name' => '销售员',
                'code' => 'sales',
                'display_name' => '销售员',
                'description' => '负责销售操作',
            ],
            [
                'name' => '普通用户',
                'code' => 'user',
                'display_name' => '普通用户',
                'description' => '普通用户，只有基本权限',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        // 创建权限
        $permissions = [
            // 用户管理权限
            ['name' => '用户管理', 'code' => 'user_manage'],
            ['name' => '角色管理', 'code' => 'role_manage'],
            ['name' => '权限管理', 'code' => 'permission_manage'],

            // 库存管理权限
            ['name' => '入库管理', 'code' => 'stock_in_manage'],
            ['name' => '退货管理', 'code' => 'return_manage'],
            ['name' => '库存查询', 'code' => 'inventory_query'],
            ['name' => '库存统计', 'code' => 'inventory_statistics'],

            // 销售管理权限
            ['name' => '销售管理', 'code' => 'sale_manage'],
            ['name' => '销售统计', 'code' => 'sale_statistics'],

            // 系统管理权限
            ['name' => '系统配置', 'code' => 'system_config'],
            ['name' => '价格配置', 'code' => 'price_config'],
            ['name' => '数据备份', 'code' => 'data_backup'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
} 