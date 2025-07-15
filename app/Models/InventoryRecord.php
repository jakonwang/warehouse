<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'quantity',
        'unit_price',
        'total_amount',
        'type',
        'reference_type',
        'reference_id',
        'note'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * 获取关联的库存
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
} 