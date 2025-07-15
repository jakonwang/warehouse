<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 获取可以访问此仓库的用户
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * 获取此仓库的库存记录
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * 获取此仓库的入库记录
     */
    public function stockIns()
    {
        return $this->hasMany(StockInRecord::class);
    }

    /**
     * 获取此仓库的退货记录
     */
    public function returns()
    {
        return $this->hasMany(ReturnRecord::class);
    }

    /**
     * 获取此仓库的盘点记录
     */
    public function inventoryChecks()
    {
        return $this->hasMany(InventoryCheck::class);
    }

    /**
     * 获取此仓库分配的商品
     */
    public function storeProducts()
    {
        return $this->hasMany(StoreProduct::class);
    }

    /**
     * 获取此仓库可销售的商品
     */
    public function availableProducts()
    {
        return $this->belongsToMany(Product::class, 'store_products')
            ->where('store_products.is_active', true)
            ->where('products.is_active', true)
            ->orderBy('store_products.sort_order')
            ->orderBy('products.sort_order');
    }

    /**
     * 获取此仓库可销售的标品
     */
    public function availableStandardProducts()
    {
        return $this->availableProducts()->where('products.type', 'standard');
    }

    /**
     * 获取此仓库可销售的盲袋
     */
    public function availableBlindBagProducts()
    {
        return $this->availableProducts()->where('products.type', 'blind_bag');
    }
} 