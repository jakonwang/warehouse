<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $user = Auth::user();
        
        if (!$user) {
            // 检查是否是移动端路径
            if ($request->is('mobile*') || $request->is('login')) {
                return redirect()->route('mobile.login');
            }
            
            // 后台管理路径
            return redirect()->route('admin.login');
        }

        // 获取仓库ID
        $storeId = $request->route('store') ?? session('current_store_id');
        
        if (!$storeId) {
            return redirect()->route('dashboard')->with('error', '请先选择仓库');
        }

        // 检查用户是否有权限访问该仓库
        if (!$user->canAccessStore($storeId)) {
            return redirect()->route('dashboard')->with('error', '无权访问该仓库');
        }

        // 如果有特定权限要求，进行权限检查
        if ($permission && !$this->hasPermission($user, $permission)) {
            return redirect()->route('dashboard')->with('error', '权限不足');
        }

        return $next($request);
    }

    /**
     * 检查用户是否有特定权限
     */
    private function hasPermission($user, $permission)
    {
        // 超级管理员拥有所有权限
        if ($user->isSuperAdmin()) {
            return true;
        }

        // 根据角色检查权限
        $role = $user->role;
        if (!$role) {
            return false;
        }

        // 权限映射
        $permissionMap = [
            'product_manage' => ['super_admin', 'inventory_manager'],
            'inventory_manage' => ['super_admin', 'inventory_manager'],
            'sale_manage' => ['super_admin', 'sales'],
            'system_config' => ['super_admin'],
            'report_view' => ['super_admin', 'inventory_manager', 'sales', 'viewer'],
        ];

        return in_array($role->code, $permissionMap[$permission] ?? []);
    }
} 