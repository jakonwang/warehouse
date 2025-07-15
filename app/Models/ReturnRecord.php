<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'customer',
        'total_amount',
        'total_cost',
        'remark',
        'image_path'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * 获取操作人
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取仓库
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * 获取退货明细
     */
    public function returnDetails(): HasMany
    {
        return $this->hasMany(ReturnDetail::class);
    }

    /**
     * 获取总数量
     */
    public function getTotalQuantityAttribute()
    {
        return $this->returnDetails->sum('quantity');
    }

    /**
     * 检查是否可以删除
     */
    public function canDelete()
    {
        // 检查是否是管理员
        if (auth()->user()->isAdmin()) {
            return true;
        }

        // 检查是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($this->store_id)) {
            return false;
        }

        // 检查是否是自己的记录
        if (auth()->id() !== $this->user_id) {
            return false;
        }

        // 检查创建时间是否在24小时内
        return $this->created_at->diffInHours(now()) <= 24;
    }
} 