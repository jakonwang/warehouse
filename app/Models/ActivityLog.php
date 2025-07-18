<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'status_code',
        'request_data',
        'response_size',
        'execution_time',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'request_data' => 'array',
        'execution_time' => 'decimal:4',
    ];

    /**
     * 获取操作用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取操作类型名称
     */
    public function getActionNameAttribute(): string
    {
        $actions = __('activity-logs.actions');
        return $actions[$this->action] ?? $actions['other'];
    }

    /**
     * 获取模型类型名称
     */
    public function getModelTypeNameAttribute(): string
    {
        $models = __('activity-logs.models');
        return $models[$this->model_type] ?? $this->model_type;
    }

    /**
     * 获取状态码描述
     */
    public function getStatusCodeTextAttribute(): string
    {
        $statusCodes = __('activity-logs.status_codes');
        return $statusCodes[$this->status_code] ?? $statusCodes['unknown'];
    }

    /**
     * 获取执行时间描述
     */
    public function getExecutionTimeTextAttribute(): string
    {
        if ($this->execution_time < 1) {
            return round($this->execution_time * 1000, 2) . 'ms';
        }
        return round($this->execution_time, 2) . 's';
    }

    /**
     * 获取响应大小描述
     */
    public function getResponseSizeTextAttribute(): string
    {
        if ($this->response_size < 1024) {
            return $this->response_size . 'B';
        } elseif ($this->response_size < 1024 * 1024) {
            return round($this->response_size / 1024, 2) . 'KB';
        } else {
            return round($this->response_size / (1024 * 1024), 2) . 'MB';
        }
    }

    /**
     * 作用域：按用户筛选
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 作用域：按操作类型筛选
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * 作用域：按时间范围筛选
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 作用域：按IP地址筛选
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * 作用域：异常操作
     */
    public function scopeAbnormal($query)
    {
        return $query->where(function($q) {
            $q->where('status_code', '>=', 400)
              ->orWhere('execution_time', '>', 5)
              ->orWhere('response_size', '>', 1024 * 1024); // 1MB
        });
    }
}
