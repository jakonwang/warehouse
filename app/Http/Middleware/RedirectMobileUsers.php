<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectMobileUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查是否是移动端访问
        $userAgent = $request->header('User-Agent');
        $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
        
        // 如果是移动端访问且不是已认证用户，重定向到移动端登录
        if ($isMobile && !auth()->check() && !$request->is('login') && !$request->is('admin/login')) {
            return redirect()->route('mobile.login');
        }
        
        return $next($request);
    }
} 