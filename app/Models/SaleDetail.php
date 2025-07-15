<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_variant_id',
        'sku',
        'quantity',
        'price',
        'cost',
        'total',
        'profit',
        'remark'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'total' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    /**
     * 获取关联的销售记录
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * 获取关联的产品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取关联的商品变体
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * 获取商品名称（包含变体信息）
     */
    public function getProductNameAttribute()
    {
        if ($this->productVariant) {
            return $this->productVariant->full_name;
        }
        return $this->product->name ?? 'Unknown Product';
    }

    /**
     * 获取商品图片
     */
    public function getProductImageAttribute()
    {
        if ($this->productVariant && $this->productVariant->image) {
            return $this->productVariant->image_url;
        }
        return $this->product->image_url ?? null;
    }

    /**
     * 在保存前自动计算总金额和利润
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($saleDetail) {
            $saleDetail->total = $saleDetail->quantity * $saleDetail->price;
            if ($saleDetail->cost !== null) {
                $saleDetail->profit = $saleDetail->quantity * ($saleDetail->price - $saleDetail->cost);
            }
            
            // 自动设置SKU
            if ($saleDetail->product_variant_id && !$saleDetail->sku) {
                $saleDetail->sku = $saleDetail->productVariant->sku ?? null;
            }
        });

        static::updating(function ($saleDetail) {
            $saleDetail->total = $saleDetail->quantity * $saleDetail->price;
            if ($saleDetail->cost !== null) {
                $saleDetail->profit = $saleDetail->quantity * ($saleDetail->price - $saleDetail->cost);
            }
        });
    }
} 