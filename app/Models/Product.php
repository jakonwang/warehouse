<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'image',
        'description',
        'price',
        'cost_price',
        'type',
        'category',
        'stock',
        'alert_stock',
        'sort_order',
        'is_active'
    ];

    /**
     * 属性类型转换
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock' => 'integer',
        'alert_stock' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * 商品类型常量
     */
    const TYPE_STANDARD = 'standard';
    const TYPE_BLIND_BAG = 'blind_bag';

    /**
     * 获取商品变体
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * 获取启用的商品变体
     */
    public function activeVariants(): HasMany
    {
        return $this->variants()->active();
    }

    /**
     * 获取默认变体
     */
    public function defaultVariant()
    {
        return $this->variants()->default()->first();
    }

    /**
     * 获取盲袋构成配置
     */
    public function blindBagCompositions(): HasMany
    {
        return $this->hasMany(BlindBagComposition::class);
    }

    /**
     * 获取启用的盲袋构成
     */
    public function activeBlindBagCompositions(): HasMany
    {
        return $this->blindBagCompositions()->active();
    }

    /**
     * 获取盲袋销售记录
     */
    public function blindBagSales(): HasMany
    {
        return $this->hasMany(BlindBagSale::class);
    }

    /**
     * 获取商品的销售记录（标品）
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * 获取库存记录（新统一架构）
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * 获取统一库存记录（如果使用unified_inventories表）
     */
    public function unifiedInventories(): HasMany
    {
        return $this->hasMany(UnifiedInventory::class);
    }

    /**
     * 获取作为盲袋发货的记录
     */
    public function blindBagDeliveries(): HasMany
    {
        return $this->hasMany(BlindBagDelivery::class, 'delivery_product_id');
    }

    /**
     * 获取作为盲袋商品的发货记录
     */
    public function blindBagOrders(): HasMany
    {
        return $this->hasMany(BlindBagDelivery::class, 'blind_bag_product_id');
    }

    /**
     * 获取分配此商品的仓库
     */
    public function storeProducts(): HasMany
    {
        return $this->hasMany(StoreProduct::class);
    }

    /**
     * 获取可销售此商品的仓库
     */
    public function availableStores()
    {
        return $this->belongsToMany(Store::class, 'store_products')
            ->where('store_products.is_active', true)
            ->where('stores.is_active', true)
            ->orderBy('store_products.sort_order')
            ->orderBy('stores.name');
    }

    /**
     * 查询范围 - 标品
     */
    public function scopeStandard(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_STANDARD);
    }

    /**
     * 查询范围 - 盲袋
     */
    public function scopeBlindBag(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_BLIND_BAG);
    }

    /**
     * 查询范围 - 启用的商品
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * 查询范围 - 按分类筛选
     */
    public function scopeInCategory(Builder $query, $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * 检查库存是否低于警戒值
     */
    public function isLowStock()
    {
        if ($this->isStandard()) {
            return $this->variants()->active()->where('stock', '<=', 'alert_stock')->exists();
        }
        return false; // 盲袋商品不直接管理库存
    }

    /**
     * 获取指定仓库的库存数量
     */
    public function getStockInStore($storeId = null)
    {
        if ($this->isBlindBag()) {
            return null; // 盲袋商品不管理库存
        }

        if (Schema::hasTable('unified_inventories')) {
            $query = $this->unifiedInventories()
                ->where('inventory_type', 'product_variant')
                ->where('reference_id', $this->id);
        } else {
            $query = $this->inventories();
        }

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->sum('quantity');
    }

    /**
     * 获取库存数量（getStockInStore的别名）
     */
    public function getStockQuantity($storeId = null)
    {
        // 优先使用currentStoreId属性
        $targetStoreId = $this->currentStoreId ?? $storeId;
        return $this->getStockInStore($targetStoreId);
    }

    /**
     * 获取总库存（所有仓库）
     */
    public function getTotalStockAttribute()
    {
        return $this->getStockInStore();
    }

    /**
     * 检查指定仓库是否有足够库存
     */
    public function hasEnoughStock($quantity, $storeId = null)
    {
        if ($this->isBlindBag()) {
            return true; // 盲袋商品总是可用
        }

        $currentStock = $this->getStockInStore($storeId);
        return $currentStock >= $quantity;
    }

    /**
     * 扣减库存
     */
    public function reduceStock($quantity, $storeId = null)
    {
        if ($this->isBlindBag()) {
            return true; // 盲袋商品不扣减库存
        }

        // 强制只用 inventories 表
        $inventory = $this->inventories()
            ->where('store_id', $storeId)
            ->first();

        if ($inventory && $inventory->quantity >= $quantity) {
            $inventory->quantity -= $quantity;
            $inventory->save();
            return true;
        }

        return false;
    }

    /**
     * 获取商品的总销售额
     */
    public function getTotalSalesAttribute()
    {
        $standardSales = $this->saleDetails()->sum('total');
        $blindBagSales = $this->blindBagSales()->sum('total_amount');
        return $standardSales + $blindBagSales;
    }

    /**
     * 获取商品的总利润
     */
    public function getTotalProfitAttribute()
    {
        $standardProfit = $this->saleDetails()->sum('profit');
        $blindBagProfit = $this->blindBagSales()->sum('profit');
        return $standardProfit + $blindBagProfit;
    }

    /**
     * 获取图片完整URL，优先使用public/uploads/products/目录，仅用文件名查找
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        // 只取文件名
        $filename = basename($this->image);
        // 先查uploads/products
        $uploadsPath = public_path('uploads/products/' . $filename);
        if (file_exists($uploadsPath)) {
            return '/uploads/products/' . $filename;
        }
        // 再查storage/products
        $storagePath = public_path('storage/products/' . $filename);
        if (file_exists($storagePath)) {
            return '/storage/products/' . $filename;
        }
        // fallback
        return \Illuminate\Support\Facades\Storage::url($this->image);
    }

    /**
     * 获取商品类型名称
     */
    public function getTypeNameAttribute()
    {
        return [
            self::TYPE_STANDARD => '标品',
            self::TYPE_BLIND_BAG => '盲袋'
        ][$this->type] ?? '未知类型';
    }

    /**
     * 检查是否是标品
     */
    public function isStandard()
    {
        return $this->type === self::TYPE_STANDARD;
    }

    /**
     * 检查是否是盲袋商品
     */
    public function isBlindBag()
    {
        return $this->type === self::TYPE_BLIND_BAG;
    }

    /**
     * 获取商品状态
     */
    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return '已下架';
        }
        
        if ($this->isStandard()) {
            return $this->isLowStock() ? '库存不足' : '正常';
        }
        
        return '正常';
    }

    /**
     * 获取商品状态颜色
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case '已下架':
                return 'secondary';
            case '库存不足':
                return 'warning';
            default:
                return 'success';
        }
    }

    /**
     * 创建默认变体（标品）
     */
    public function createDefaultVariant()
    {
        if ($this->isStandard() && !$this->defaultVariant()) {
            return $this->variants()->create([
                'sku' => ProductVariant::generateSku($this->code),
                'variant_name' => '默认',
                'price' => $this->price,
                'cost_price' => $this->cost_price,
                'stock' => $this->stock ?? 0,
                'alert_stock' => $this->alert_stock ?? 10,
                'is_default' => true,
                'status' => 'active',
                'sort_order' => 0,
            ]);
        }
        return null;
    }

    /**
     * 验证盲袋构成
     */
    public function validateBlindBagComposition()
    {
        if ($this->isBlindBag()) {
            return BlindBagComposition::validateProbabilities($this->id);
        }
        return true;
    }

    /**
     * 处理保存事件
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (!isset($product->is_active)) {
                $product->is_active = true;
            }
            if (!isset($product->type)) {
                $product->type = self::TYPE_STANDARD;
            }
        });

        static::created(function ($product) {
            // 为标品自动创建默认变体
            if ($product->isStandard()) {
                $product->createDefaultVariant();
            }
        });
    }
} 