<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIn extends Model
{
    protected $fillable = [
        'price_29_quantity',
        'price_59_quantity',
        'price_89_quantity',
        'price_159_quantity',
        'total_quantity',
        'remark',
        'user_id'
    ];

    /**
     * 获取入库操作人
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 