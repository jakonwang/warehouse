<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        
        // 检查是否是移动端路径
        if ($request->is('mobile*') || $request->is('login')) {
            return route('mobile.login');
        }
        
        // 后台管理路径
        return route('admin.login');
    }
} 