<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceSeries extends Model
{
    protected $table = 'price_series';

    protected $fillable = [
        'name',
        'code',
        'price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * 获取最新的成本价格
     */
    public function getCostAttribute()
    {
        $latestCost = $this->costs()->latest('effective_date')->first();
        return $latestCost ? $latestCost->cost : $this->price * 0.7; // 默认成本为售价的70%
    }

    /**
     * 获取价格系列的成本记录
     */
    public function costs()
    {
        return $this->hasMany(PriceSeriesCost::class);
    }

    /**
     * 获取入库明细
     */
    public function stockInDetails()
    {
        return $this->hasMany(StockInDetail::class, 'series_code', 'code');
    }
} 