<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 获取用户ID，未登录用户使用IP地址
        $identifier = Auth::check() ? Auth::id() : $request->ip();
        $key = 'api_rate_limit_' . $identifier;
        
        // 设置限流规则
        $limit = 100; // 每分钟100次请求
        $window = 60; // 60秒窗口期
        
        // 检查当前请求次数
        $currentRequests = Cache::get($key, 0);
        
        if ($currentRequests >= $limit) {
            return response()->json([
                'error' => '请求过于频繁，请稍后再试',
                'retry_after' => Cache::get($key . '_reset', $window)
            ], 429);
        }
        
        // 增加请求计数
        Cache::increment($key);
        
        // 设置过期时间
        if (!Cache::has($key . '_reset')) {
            Cache::put($key . '_reset', $window, $window);
        }
        
        // 添加限流头信息
        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', $limit - $currentRequests - 1);
        $response->headers->set('X-RateLimit-Reset', time() + $window);
        
        return $response;
    }
}
