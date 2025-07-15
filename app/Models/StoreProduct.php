<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StoreProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'product_id',
        'is_active',
        'sort_order',
        'remark'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 获取关联的仓库
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * 获取关联的商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 查询范围 - 启用的分配
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * 查询范围 - 按仓库筛选
     */
    public function scopeInStore(Builder $query, $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * 查询范围 - 按商品类型筛选
     */
    public function scopeByProductType(Builder $query, $type): Builder
    {
        return $query->whereHas('product', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    /**
     * 查询范围 - 按排序
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
} 