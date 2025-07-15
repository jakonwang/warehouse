<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCheckDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_check_record_id',
        'product_id',
        'system_quantity',
        'actual_quantity',
        'difference',
        'unit_cost',
        'total_cost'
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'actual_quantity' => 'integer',
        'difference' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * 获取盘点记录
     */
    public function inventoryCheckRecord(): BelongsTo
    {
        return $this->belongsTo(InventoryCheckRecord::class);
    }

    /**
     * 获取商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
} 