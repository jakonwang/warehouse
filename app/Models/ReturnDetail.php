<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_record_id',
        'product_id',
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
     * 获取退货记录
     */
    public function returnRecord(): BelongsTo
    {
        return $this->belongsTo(ReturnRecord::class);
    }

    /**
     * 获取商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
} 