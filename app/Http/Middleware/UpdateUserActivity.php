<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 只对已认证的用户更新活动时间
        if (auth()->check()) {
            try {
                DB::table('users')
                    ->where('id', auth()->id())
                    ->update(['last_activity_at' => now()]);
            } catch (\Exception $e) {
                // 如果字段不存在，忽略错误
                \Log::warning('Failed to update user activity: ' . $e->getMessage());
            }
        }

        return $response;
    }
}
