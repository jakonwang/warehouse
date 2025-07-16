<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "库存管理员仓库访问测试:\n";
echo "====================\n";

// 查找库存管理员角色
$inventoryManagerRole = \App\Models\Role::where('code', 'inventory_manager')->first();

if (!$inventoryManagerRole) {
    echo "❌ 未找到库存管理员角色\n";
    exit;
}

// 创建测试库存管理员用户（如果不存在）
$testUser = \App\Models\User::where('username', 'test_inventory_manager')->first();
if (!$testUser) {
    $testUser = \App\Models\User::create([
        'username' => 'test_inventory_manager',
        'password' => bcrypt('password'),
        'real_name' => '测试库存管理员',
        'email' => 'test_inventory@example.com',
        'role_id' => $inventoryManagerRole->id,
        'is_active' => true,
    ]);
    echo "✅ 创建测试库存管理员用户\n";
} else {
    echo "✅ 使用现有测试库存管理员用户\n";
}

echo "\n用户信息:\n";
echo "==========\n";
echo "用户名: {$testUser->username}\n";
echo "真实姓名: {$testUser->real_name}\n";
echo "角色: " . ($testUser->role ? $testUser->role->name : '无') . "\n";
echo "是否超级管理员: " . ($testUser->isSuperAdmin() ? '是' : '否') . "\n";

echo "\n仓库访问测试:\n";
echo "============\n";

// 测试不同的仓库获取方法
echo "1. 使用 stores() 方法:\n";
$stores1 = $testUser->stores()->where('is_active', true)->get();
echo "   直接分配的仓库数量: " . $stores1->count() . "\n";
foreach ($stores1 as $store) {
    echo "   - {$store->name} (ID: {$store->id})\n";
}

echo "\n2. 使用 getAccessibleStores() 方法:\n";
$stores2 = $testUser->getAccessibleStores()->where('is_active', true);
echo "   可访问的仓库数量: " . $stores2->count() . "\n";
foreach ($stores2 as $store) {
    echo "   - {$store->name} (ID: {$store->id})\n";
}

echo "\n3. 测试 canAccessStore() 方法:\n";
$allStores = \App\Models\Store::where('is_active', true)->get();
foreach ($allStores as $store) {
    $canAccess = $testUser->canAccessStore($store->id) ? '✅' : '❌';
    echo "   {$store->name} (ID: {$store->id}): {$canAccess}\n";
}

echo "\n4. 模拟销售创建页面:\n";
echo "============\n";

// 模拟销售创建页面的逻辑
$stores = $testUser->getAccessibleStores()->where('is_active', true);
echo "可操作的仓库数量: " . $stores->count() . "\n";

if ($stores->isEmpty()) {
    echo "❌ 没有可操作的仓库权限\n";
} else {
    echo "✅ 有可操作的仓库权限\n";
    foreach ($stores as $store) {
        echo "   - {$store->name} (ID: {$store->id})\n";
        
        // 测试获取商品
        $standardProducts = $store->availableStandardProducts()->get();
        $blindBagProducts = $store->availableBlindBagProducts()->get();
        
        echo "     标品商品数量: " . $standardProducts->count() . "\n";
        echo "     盲袋商品数量: " . $blindBagProducts->count() . "\n";
    }
}

// 清理测试用户
if ($testUser->username === 'test_inventory_manager') {
    $testUser->delete();
    echo "\n✅ 清理测试用户\n";
} 