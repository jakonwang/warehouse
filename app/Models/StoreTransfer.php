<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_no',
        'source_store_id',
        'target_store_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'status',
        'reason',
        'remark',
        'requested_by',
        'approved_by',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // 状态常量
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute(): string
    {
        $statusMap = [
            self::STATUS_PENDING => '待审批',
            self::STATUS_APPROVED => '已审批',
            self::STATUS_REJECTED => '已拒绝',
            self::STATUS_IN_TRANSIT => '调拨中',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_CANCELLED => '已取消',
        ];

        return $statusMap[$this->status] ?? '未知状态';
    }

    /**
     * 获取状态颜色
     */
    public function getStatusColorAttribute(): string
    {
        $colorMap = [
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'blue',
            self::STATUS_REJECTED => 'red',
            self::STATUS_IN_TRANSIT => 'purple',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CANCELLED => 'gray',
        ];

        return $colorMap[$this->status] ?? 'gray';
    }

    /**
     * 源仓库
     */
    public function sourceStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'source_store_id');
    }

    /**
     * 目标仓库
     */
    public function targetStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'target_store_id');
    }

    /**
     * 商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 申请人
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * 审批人
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 检查是否可以审批
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * 检查是否可以完成
     */
    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * 检查是否可以取消
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    /**
     * 生成调拨单号
     */
    public static function generateTransferNo(): string
    {
        $prefix = 'TF';
        $date = now()->format('Ymd');
        $sequence = self::whereDate('created_at', today())->count() + 1;
        
        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }

    /**
     * 获取可调拨的商品
     */
    public static function getAvailableProducts($sourceStoreId, $targetStoreId)
    {
        return Product::whereHas('inventories', function ($query) use ($sourceStoreId) {
            $query->where('store_id', $sourceStoreId)
                  ->where('quantity', '>', 0);
        })->whereDoesntHave('inventories', function ($query) use ($targetStoreId) {
            $query->where('store_id', $targetStoreId);
        })->orWhereHas('inventories', function ($query) use ($targetStoreId) {
            $query->where('store_id', $targetStoreId)
                  ->where('quantity', '<=', 10); // 库存不足的商品
        })->get();
    }
} 