<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceSeriesSaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'series_code',
        'quantity',
        'total_cost'
    ];

    /**
     * 获取关联的销售记录
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * 获取关联的价格系列
     */
    public function priceSeries(): BelongsTo
    {
        return $this->belongsTo(PriceSeries::class, 'series_code', 'code');
    }
} 