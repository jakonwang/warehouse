<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 获取超级管理员角色
        $superAdminRole = \App\Models\Role::where('code', 'super_admin')->first();
        
        if ($superAdminRole) {
            User::create([
                'username' => 'jakonwang',
                'password' => Hash::make('heng,275113124'),
                'real_name' => '管理员',
                'email' => 'jakonwang@163.com',
                'phone' => '13800138000',
                'is_active' => true,
                'role_id' => $superAdminRole->id,
            ]);
        }
    }
} 