<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlindBagDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'blind_bag_sale_id',
        'price_series_code',
        'quantity',
        'unit_cost',
        'total_cost',
        'remark'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * 获取所属盲袋销售记录
     */
    public function blindBagSale(): BelongsTo
    {
        return $this->belongsTo(BlindBagSale::class);
    }

    /**
     * 获取关联的价格系列
     */
    public function priceSeries(): BelongsTo
    {
        return $this->belongsTo(PriceSeries::class, 'price_series_code', 'code');
    }

    /**
     * 自动计算总成本
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($detail) {
            $detail->total_cost = $detail->quantity * $detail->unit_cost;
        });

        static::updating(function ($detail) {
            $detail->total_cost = $detail->quantity * $detail->unit_cost;
        });

        // 当明细保存后，更新主记录的总成本
        static::saved(function ($detail) {
            $detail->blindBagSale->updateTotalCost();
        });

        // 当明细删除后，更新主记录的总成本
        static::deleted(function ($detail) {
            if ($detail->blindBagSale) {
                $detail->blindBagSale->updateTotalCost();
            }
        });
    }

    /**
     * 获取系列名称
     */
    public function getSeriesNameAttribute()
    {
        return $this->priceSeries ? "价格系列 {$this->priceSeries->code}" : $this->price_series_code;
    }

    /**
     * 获取明细描述
     */
    public function getDescriptionAttribute()
    {
        return "{$this->series_name} x{$this->quantity} = ¥{$this->total_cost}";
    }
}
