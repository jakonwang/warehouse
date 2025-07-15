<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up()
    {
        // 注意：角色创建已移至 RoleSeeder，此处仅创建管理员用户
        // 查找 super_admin 角色的 ID
        $superAdminRole = DB::table('roles')->where('code', 'super_admin')->first();
        
        if ($superAdminRole) {
            // 创建管理员用户
            DB::table('users')->insert([
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'real_name' => '系统管理员',
                'email' => 'admin@example.com',
                'phone' => '13800138000',
                'is_active' => true,
                'role_id' => $superAdminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('users')->where('username', 'admin')->delete();
        // 角色删除由 RoleSeeder 管理，此处不需要删除
    }
}; 