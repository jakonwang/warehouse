<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

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
        
        // 只记录已认证用户的操作
        if (Auth::check()) {
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
        
        // 记录日志
        Log::info('User Activity', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => $action,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'model_type' => $modelType,
            'model_id' => $modelId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
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
