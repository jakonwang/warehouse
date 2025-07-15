<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCheckRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'status',
        'remark'
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
     * 获取盘点明细
     */
    public function inventoryCheckDetails(): HasMany
    {
        return $this->hasMany(InventoryCheckDetail::class);
    }

    /**
     * 获取总差异数量
     */
    public function getTotalDifferenceAttribute()
    {
        return $this->inventoryCheckDetails->sum('difference');
    }

    /**
     * 获取总差异成本
     */
    public function getTotalCostAttribute()
    {
        return $this->inventoryCheckDetails->sum('total_cost');
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

        // 检查状态是否为待确认
        if ($this->status !== 'pending') {
            return false;
        }

        // 检查创建时间是否在24小时内
        return $this->created_at->diffInHours(now()) <= 24;
    }

    /**
     * 检查是否可以确认
     */
    public function canConfirm()
    {
        // 检查是否是管理员
        if (auth()->user()->isAdmin()) {
            return true;
        }

        // 检查是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($this->store_id)) {
            return false;
        }

        // 检查状态是否为待确认
        return $this->status === 'pending';
    }
} 