<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'variant_name',
        'price',
        'cost_price',
        'stock',
        'alert_stock',
        'is_default',
        'status',
        'image',
        'attributes',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_default' => 'boolean',
        'attributes' => 'json',
    ];

    /**
     * 获取所属商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取销售明细
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * 获取统一库存记录
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(UnifiedInventory::class, 'reference_id')->where('inventory_type', 'product_variant');
    }

    /**
     * 获取指定仓库的库存
     */
    public function getInventoryByStore($storeId)
    {
        return $this->inventories()->where('store_id', $storeId)->first();
    }

    /**
     * 获取总库存
     */
    public function getTotalStockAttribute()
    {
        return $this->inventories()->sum('quantity');
    }

    /**
     * 获取可用库存
     */
    public function getAvailableStockAttribute()
    {
        return $this->inventories()->sum('available_quantity');
    }

    /**
     * 获取变体完整名称
     */
    public function getFullNameAttribute()
    {
        return $this->product->name . ($this->variant_name ? ' - ' . $this->variant_name : '');
    }

    /**
     * 获取图片URL
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return $this->product->image_url ?? null;
        }
        
        // 如果路径以 'products/' 开头，说明是新的上传格式，直接拼接 uploads 路径
        if (str_starts_with($this->image, 'products/')) {
            return request()->getSchemeAndHttpHost() . '/uploads/' . $this->image;
        }
        
        // 如果图片存在于 uploads/products/ 目录（完整文件名）
        if (file_exists(public_path('uploads/products/' . $this->image))) {
            return request()->getSchemeAndHttpHost() . '/uploads/products/' . $this->image;
        }
        
        // 向后兼容旧位置
        return request()->getSchemeAndHttpHost() . '/storage/products/' . $this->image;
    }

    /**
     * 查询范围 - 启用的变体
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * 查询范围 - 默认变体
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * 查询范围 - 低库存预警
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('stock <= alert_stock');
    }

    /**
     * 生成唯一SKU
     */
    public static function generateSku($productCode, $variantName = null)
    {
        $baseSku = $productCode;
        if ($variantName) {
            $baseSku .= '-' . strtoupper(substr(md5($variantName), 0, 4));
        }
        
        $counter = 1;
        $sku = $baseSku;
        
        while (self::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }
        
        return $sku;
    }
}
