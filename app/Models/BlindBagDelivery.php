<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlindBagDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'blind_bag_product_id',
        'delivery_product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'remark',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * 关联销售记录
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * 关联盲袋商品
     */
    public function blindBagProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'blind_bag_product_id');
    }

    /**
     * 关联实际发货商品
     */
    public function deliveryProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'delivery_product_id');
    }

    /**
     * 获取发货摘要
     */
    public function getDeliverySummaryAttribute(): string
    {
        return "{$this->deliveryProduct->name} x{$this->quantity}";
    }

    /**
     * 获取成本明细
     */
    public function getCostDetailsAttribute(): string
    {
        return "单价: ¥{$this->unit_cost} × {$this->quantity} = ¥{$this->total_cost}";
    }
}
