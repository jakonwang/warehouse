<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'store_id',
        'quantity',
        'min_quantity',
        'max_quantity',
        'last_check_date',
        'remark'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'last_check_date' => 'datetime',
    ];

    /**
     * 获取路由键名
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * 获取关联的商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取关联的仓库
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }



    /**
     * 获取库存状态
     */
    public function getStatusAttribute()
    {
        if ($this->quantity <= $this->min_quantity) {
            return 'low';
        } elseif ($this->quantity >= $this->max_quantity) {
            return 'high';
        }
        return 'normal';
    }

    /**
     * 获取库存状态文本
     */
    public function getStatusTextAttribute()
    {
        return [
            'low' => '库存不足',
            'normal' => '库存正常',
            'high' => '库存充足'
        ][$this->status];
    }

    /**
     * 获取库存状态颜色
     */
    public function getStatusColorAttribute()
    {
        return [
            'low' => 'danger',
            'normal' => 'success',
            'high' => 'warning'
        ][$this->status];
    }

    /**
     * 获取库存总价值
     */
    public function getTotalValueAttribute()
    {
        return $this->quantity * ($this->product->selling_price ?? 0);
    }

    /**
     * 获取库存总成本
     */
    public function getTotalCostAttribute()
    {
        return $this->quantity * ($this->product->cost_price ?? 0);
    }

    /**
     * 获取库存记录
     */
    public function records()
    {
        return $this->hasMany(InventoryRecord::class);
    }

    /**
     * 获取盘点记录
     */
    public function checkRecords()
    {
        return $this->hasManyThrough(
            InventoryCheckDetail::class,
            InventoryCheckRecord::class,
            'store_id', // 盘点记录的外键
            'product_id', // 盘点明细的外键
            'store_id', // 库存的外键
            'id' // 盘点记录的主键
        )->where('product_id', $this->product_id);
    }

    /**
     * 更新库存数量
     */
    public function updateQuantity($change, $type = 'adjustment', $reason = null)
    {
        $this->quantity += $change;
        if ($this->quantity < 0) {
            $this->quantity = 0;
        }
        $this->save();

        // 记录库存变更
        $this->records()->create([
            'product_id' => $this->product_id,
            'store_id' => $this->store_id,
            'type' => $type,
            'quantity_change' => $change,
            'quantity_after' => $this->quantity,
            'reason' => $reason,
            'operated_by' => auth()->id(),
        ]);
    }
} 