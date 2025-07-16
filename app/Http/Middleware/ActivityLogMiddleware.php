<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class ActivityLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // 记录登录操作（无论是否已认证）和其他已认证用户的操作
        if (Auth::check() || $this->isLoginRequest($request)) {
            try {
                $this->logActivity($request, $response);
            } catch (\Exception $e) {
                // 记录日志失败不应影响正常请求
                Log::error('Activity log failed: ' . $e->getMessage());
            }
        }
        
        return $response;
    }
    
    /**
     * 记录操作日志
     */
    private function logActivity(Request $request, Response $response): void
    {
        $user = Auth::user();
        $route = $request->route();
        
        // 获取操作类型
        $action = $this->getActionType($request);
        
        // 获取模型信息
        $modelType = $route ? class_basename($route->getController()) : null;
        $modelId = $route ? $route->parameter('id') : null;
        
        // 跳过某些不需要记录的请求
        if ($this->shouldSkipLogging($request)) {
            return;
        }
        
        // 对于登录操作，尝试从请求数据中获取用户ID
        $userId = null;
        if ($this->isLoginRequest($request)) {
            $username = $request->input('username');
            if ($username) {
                $userModel = \App\Models\User::where('username', $username)->first();
                $userId = $userModel ? $userModel->id : null;
            }
        } else {
            $userId = $user ? $user->id : null;
        }
        
        // 如果没有找到用户ID，跳过记录
        if (!$userId) {
            return;
        }
        
        // 创建活动日志记录
        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'request_data' => $this->sanitizeRequestData($request),
            'response_size' => strlen($response->getContent()),
            'execution_time' => microtime(true) - LARAVEL_START,
        ]);
    }
    
    /**
     * 获取操作类型
     */
    private function getActionType(Request $request): string
    {
        $method = $request->method();
        
        switch ($method) {
            case 'GET':
                return 'view';
            case 'POST':
                return 'create';
            case 'PUT':
            case 'PATCH':
                return 'update';
            case 'DELETE':
                return 'delete';
            default:
                return 'other';
        }
    }
    
    /**
     * 判断是否应该跳过日志记录
     */
    private function shouldSkipLogging(Request $request): bool
    {
        // 跳过静态资源请求
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $request->path())) {
            return true;
        }
        
        // 跳过某些不需要记录的路径
        $skipPaths = [
            'activity-logs',
            'debugbar',
            'horizon',
            'telescope',
            'statistics',
            'system-config',
            'backup',
            'system-monitor',
            'categories',
            'products',
            'users',
            'profile',
            'dashboard',
        ];
        
        foreach ($skipPaths as $path) {
            if (str_contains($request->path(), $path)) {
                return true;
            }
        }
        
        // 只记录特定的操作
        $allowedPaths = [
            'stores',           // 仓库管理
            'stock-ins',        // 入库管理
            'stock-outs',       // 出库管理
            'inventory',        // 库存管理
            'sales',            // 销售管理
            'returns',          // 退货管理
            'login',            // 登录
            'logout',           // 登出
        ];
        
        $currentPath = $request->path();
        $hasAllowedPath = false;
        
        foreach ($allowedPaths as $path) {
            if (str_contains($currentPath, $path)) {
                $hasAllowedPath = true;
                break;
            }
        }
        
        // 如果没有匹配到允许的路径，则跳过记录
        if (!$hasAllowedPath) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 检查是否是登录请求
     */
    private function isLoginRequest(Request $request): bool
    {
        $loginPaths = ['login', 'admin/login'];
        $currentPath = $request->path();
        
        foreach ($loginPaths as $path) {
            if (str_contains($currentPath, $path) && $request->method() === 'POST') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 清理请求数据，移除敏感信息
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        // 移除敏感字段
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***';
            }
        }
        
        return $data;
    }
}
