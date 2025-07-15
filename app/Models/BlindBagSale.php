<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlindBagSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'total_cost',
        'profit',
        'profit_rate',
        'remark'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'profit' => 'decimal:2',
        'profit_rate' => 'decimal:2',
    ];

    /**
     * 获取所属销售记录
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * 获取所属盲袋商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取出货明细
     */
    public function details(): HasMany
    {
        return $this->hasMany(BlindBagDetail::class);
    }

    /**
     * 自动计算利润相关字段
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blindBagSale) {
            $blindBagSale->total_amount = $blindBagSale->quantity * $blindBagSale->unit_price;
            
            if ($blindBagSale->total_cost > 0) {
                $blindBagSale->profit = $blindBagSale->total_amount - $blindBagSale->total_cost;
                $blindBagSale->profit_rate = ($blindBagSale->profit / $blindBagSale->total_amount) * 100;
            }
        });

        static::updating(function ($blindBagSale) {
            $blindBagSale->total_amount = $blindBagSale->quantity * $blindBagSale->unit_price;
            
            if ($blindBagSale->total_cost > 0) {
                $blindBagSale->profit = $blindBagSale->total_amount - $blindBagSale->total_cost;
                $blindBagSale->profit_rate = ($blindBagSale->profit / $blindBagSale->total_amount) * 100;
            }
        });
    }

    /**
     * 更新总成本（从出货明细计算）
     */
    public function updateTotalCost()
    {
        $this->total_cost = $this->details()->sum('total_cost');
        $this->profit = $this->total_amount - $this->total_cost;
        $this->profit_rate = $this->total_amount > 0 ? ($this->profit / $this->total_amount) * 100 : 0;
        $this->save();
        
        return $this;
    }

    /**
     * 获取出货内容摘要
     */
    public function getContentSummaryAttribute()
    {
        $summary = [];
        foreach ($this->details as $detail) {
            $summary[] = "{$detail->price_series_code} x{$detail->quantity}";
        }
        return implode(', ', $summary);
    }

    /**
     * 获取平均成本
     */
    public function getAverageCostAttribute()
    {
        return $this->quantity > 0 ? $this->total_cost / $this->quantity : 0;
    }
}
