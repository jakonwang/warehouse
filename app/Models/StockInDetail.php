<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_in_record_id',
        'series_code',
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'unit_cost',
        'total_cost',
        'remark'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * 获取入库记录
     */
    public function stockInRecord(): BelongsTo
    {
        return $this->belongsTo(StockInRecord::class);
    }

    /**
     * 获取商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
} 