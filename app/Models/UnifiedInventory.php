<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class UnifiedInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'inventory_type',
        'reference_id',
        'reference_code',
        'quantity',
        'reserved_quantity',
        'min_quantity',
        'max_quantity',
        'last_check_at',
        'remark'
    ];

    protected $casts = [
        'last_check_at' => 'datetime',
    ];

    /**
     * 获取所属仓库
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * 获取关联的商品变体（当inventory_type为product_variant时）
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'reference_id');
    }

    /**
     * 获取关联的价格系列（当inventory_type为price_series时）
     */
    public function priceSeries(): BelongsTo
    {
        return $this->belongsTo(PriceSeries::class, 'reference_id');
    }

    /**
     * 多态关联 - 根据类型动态关联
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 查询范围 - 商品变体库存
     */
    public function scopeProductVariants(Builder $query): Builder
    {
        return $query->where('inventory_type', 'product_variant');
    }

    /**
     * 查询范围 - 价格系列库存
     */
    public function scopePriceSeries(Builder $query): Builder
    {
        return $query->where('inventory_type', 'price_series');
    }

    /**
     * 查询范围 - 低库存预警
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('quantity <= min_quantity');
    }

    /**
     * 查询范围 - 按仓库筛选
     */
    public function scopeInStore(Builder $query, $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * 获取可用库存（动态计算）
     */
    public function getAvailableQuantityAttribute()
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * 库存入库
     */
    public function stockIn($quantity, $remark = null)
    {
        $this->quantity += $quantity;
        if ($remark) {
            $this->remark = $remark;
        }
        $this->save();
        
        return $this;
    }

    /**
     * 库存出库
     */
    public function stockOut($quantity, $remark = null)
    {
        $availableQuantity = $this->getAvailableQuantityAttribute();
        if ($availableQuantity < $quantity) {
            throw new \Exception("可用库存不足，当前可用: {$availableQuantity}，需要: {$quantity}");
        }
        
        $this->quantity -= $quantity;
        if ($remark) {
            $this->remark = $remark;
        }
        $this->save();
        
        return $this;
    }

    /**
     * 预留库存
     */
    public function reserve($quantity, $remark = null)
    {
        $availableQuantity = $this->getAvailableQuantityAttribute();
        if ($availableQuantity < $quantity) {
            throw new \Exception("可用库存不足，无法预留");
        }
        
        $this->reserved_quantity += $quantity;
        if ($remark) {
            $this->remark = $remark;
        }
        $this->save();
        
        return $this;
    }

    /**
     * 释放预留库存
     */
    public function releaseReservation($quantity, $remark = null)
    {
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        if ($remark) {
            $this->remark = $remark;
        }
        $this->save();
        
        return $this;
    }

    /**
     * 盘点更新
     */
    public function updateCheck($actualQuantity, $remark = null)
    {
        $this->quantity = $actualQuantity;
        $this->last_check_at = Carbon::now();
        if ($remark) {
            $this->remark = $remark;
        }
        $this->save();
        
        return $this;
    }

    /**
     * 获取库存状态
     */
    public function getStatusAttribute()
    {
        if ($this->quantity <= 0) {
            return '缺货';
        } elseif ($this->quantity <= $this->min_quantity) {
            return '预警';
        } elseif ($this->quantity >= $this->max_quantity) {
            return '满仓';
        }
        return '正常';
    }

    /**
     * 获取库存状态颜色
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case '缺货':
                return 'danger';
            case '预警':
                return 'warning';
            case '满仓':
                return 'info';
            default:
                return 'success';
        }
    }

    /**
     * 获取库存详情描述
     */
    public function getDetailDescriptionAttribute()
    {
        if ($this->inventory_type === 'product_variant') {
            $variant = $this->productVariant;
            return $variant ? $variant->full_name : "变体ID: {$this->reference_id}";
        } else {
            $series = $this->priceSeries;
            return $series ? "价格系列: {$series->code}" : "系列ID: {$this->reference_id}";
        }
    }
}
