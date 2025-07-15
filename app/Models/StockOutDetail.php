<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOutDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_out_record_id',
        'series_code',
        'quantity',
        'unit_price',
        'total_amount',
        'total_cost'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * 获取出库记录
     */
    public function stockOutRecord(): BelongsTo
    {
        return $this->belongsTo(StockOutRecord::class);
    }

    /**
     * 获取价格系列
     */
    public function priceSeries(): BelongsTo
    {
        return $this->belongsTo(PriceSeries::class, 'series_code', 'code');
    }
} 