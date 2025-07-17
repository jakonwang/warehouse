<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'real_name',
        'email',
        'phone',
        'is_active',
        'role_id',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * 获取用户的销售记录
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * 获取用户的角色
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 获取用户可以访问的仓库
     */
    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }

    /**
     * 检查用户是否有权限访问指定仓库
     */
    public function canAccessStore($storeId)
    {
        // 超级管理员可以访问所有仓库
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        return $this->stores()->where('stores.id', $storeId)->exists();
    }

    /**
     * 判断用户是否为超级管理员
     */
    public function isSuperAdmin(): bool
    {
        // 检查用户名是否为admin，或者角色代码是否为super_admin
        return $this->username === 'admin' || ($this->role && $this->role->code === 'super_admin');
    }

    /**
     * 判断用户是否为管理员
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        if (!$this->role) {
            return false;
        }
        
        // 检查角色代码是否为admin或super_admin
        return in_array($this->role->code, ['admin', 'super_admin']);
    }

    /**
     * 检查用户是否有特定权限
     */
    public function hasPermission($permission): bool
    {
        // 超级管理员拥有所有权限
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 根据角色检查权限
        $role = $this->role;
        if (!$role) {
            return false;
        }

        // 权限映射
        $permissionMap = [
            // 用户管理权限
            'user_manage' => ['super_admin', 'admin'],
            'role_manage' => ['super_admin'],
            'permission_manage' => ['super_admin'],
            
            // 商品管理权限
            'product_manage' => ['super_admin', 'admin'],
            'product_view' => ['super_admin', 'admin', 'inventory_manager'],
            'product_create' => ['super_admin', 'admin'],
            'product_edit' => ['super_admin', 'admin'],
            'product_delete' => ['super_admin', 'admin'],
            
            // 库存管理权限
            'inventory_manage' => ['super_admin', 'admin', 'inventory_manager'],
            'inventory_view' => ['super_admin', 'admin', 'inventory_manager', 'sales'],
            'stock_in_manage' => ['super_admin', 'admin', 'inventory_manager'],
            'stock_out_manage' => ['super_admin', 'admin', 'inventory_manager'],
            'return_manage' => ['super_admin', 'admin', 'inventory_manager'],
            'inventory_check' => ['super_admin', 'admin', 'inventory_manager'],
            
            // 销售管理权限 - 库存管理员现在也拥有销售权限
            'sale_manage' => ['super_admin', 'admin', 'inventory_manager', 'sales'],
            'sale_create' => ['super_admin', 'admin', 'inventory_manager', 'sales'],
            'sale_view' => ['super_admin', 'admin', 'inventory_manager', 'sales'],
            'sale_edit' => ['super_admin', 'admin', 'inventory_manager', 'sales'],
            'sale_delete' => ['super_admin', 'admin'],
            
            // 仓库管理权限
            'store_manage' => ['super_admin', 'admin'],
            'store_view' => ['super_admin', 'admin', 'inventory_manager', 'sales'],
            
            // 系统管理权限
            'system_config' => ['super_admin'],
            'price_config' => ['super_admin', 'admin'],
            'data_backup' => ['super_admin'],
            
            // 报表权限 - 库存管理员现在也拥有销售报表权限
            'report_view' => ['super_admin', 'admin', 'inventory_manager', 'sales'],
            'report_sales' => ['super_admin', 'admin', 'inventory_manager', 'sales'],
            'report_inventory' => ['super_admin', 'admin', 'inventory_manager'],
            
            // 移动端权限
            'mobile_access' => ['super_admin', 'admin', 'inventory_manager', 'sales'],
        ];

        return in_array($role->code, $permissionMap[$permission] ?? []);
    }

    /**
     * 获取用户可访问的仓库列表
     */
    public function getAccessibleStores()
    {
        if ($this->isSuperAdmin()) {
            return \App\Models\Store::all();
        }
        // 其他角色只返回分配的仓库
        return $this->stores;
    }

    /**
     * 检查用户是否可以管理用户
     */
    public function canManageUsers(): bool
    {
        // 超级管理员可以管理用户
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // 检查角色是否存在
        if (!$this->role) {
            return false;
        }
        
        // 管理员和超级管理员都可以管理用户
        return in_array($this->role->code, ['super_admin', 'admin']);
    }

    /**
     * 检查用户是否可以查看利润和成本信息
     * 只有超级管理员可以查看
     */
    public function canViewProfitAndCost(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * 检查用户是否可以访问移动端
     */
    public function canAccessMobile(): bool
    {
        return $this->hasPermission('mobile_access');
    }

    /**
     * 检查用户是否可以管理商品
     */
    public function canManageProducts(): bool
    {
        return $this->hasPermission('product_manage');
    }

    /**
     * 检查用户是否可以查看商品
     */
    public function canViewProducts(): bool
    {
        return $this->hasPermission('product_view');
    }

    /**
     * 检查用户是否可以管理库存
     */
    public function canManageInventory(): bool
    {
        return $this->hasPermission('inventory_manage');
    }

    /**
     * 检查用户是否可以管理销售
     */
    public function canManageSales(): bool
    {
        return $this->hasPermission('sale_manage');
    }

    /**
     * 检查用户是否可以管理仓库
     */
    public function canManageStores(): bool
    {
        return $this->hasPermission('store_manage');
    }

    /**
     * 检查用户是否可以查看报表
     */
    public function canViewReports(): bool
    {
        return $this->hasPermission('report_view');
    }

    /**
     * 检查用户是否可以管理系统配置
     */
    public function canManageSystemConfig(): bool
    {
        return $this->hasPermission('system_config');
    }
} 