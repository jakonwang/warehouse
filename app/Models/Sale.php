<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'sale_type',
        'customer_name',
        'customer_phone',
        'remark',
        'image_path',
        'total_amount',
        'total_cost',
        'total_profit',
        'profit_rate',
        'user_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'profit_rate' => 'decimal:2',
    ];

    /**
     * 销售类型常量
     */
    const SALE_TYPE_STANDARD = 'standard';
    const SALE_TYPE_BLIND_BAG = 'blind_bag';
    const SALE_TYPE_MIXED = 'mixed';

    /**
     * 获取仓库
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * 获取操作人
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取标品销售明细
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * 获取盲袋销售记录
     */
    public function blindBagSales(): HasMany
    {
        return $this->hasMany(BlindBagSale::class);
    }

    /**
     * 获取销售的价格系列明细（兼容旧系统）
     */
    public function priceSeriesSaleDetails(): HasMany
    {
        return $this->hasMany(PriceSeriesSaleDetail::class);
    }

    /**
     * 获取盲袋发货明细
     */
    public function blindBagDeliveries(): HasMany
    {
        return $this->hasMany(BlindBagDelivery::class);
    }

    /**
     * 查询范围 - 标品销售
     */
    public function scopeStandard(Builder $query): Builder
    {
        return $query->where('sale_type', self::SALE_TYPE_STANDARD);
    }

    /**
     * 查询范围 - 盲袋销售
     */
    public function scopeBlindBag(Builder $query): Builder
    {
        return $query->where('sale_type', self::SALE_TYPE_BLIND_BAG);
    }

    /**
     * 查询范围 - 混合销售
     */
    public function scopeMixed(Builder $query): Builder
    {
        return $query->where('sale_type', self::SALE_TYPE_MIXED);
    }

    public function getOrderNoAttribute()
    {
        // 优先用数据库字段，否则用规则生成
        if (!empty($this->attributes['order_no'])) {
            return $this->attributes['order_no'];
        }
        return 'S' . ($this->created_at ? $this->created_at->format('Ymd') : date('Ymd')) . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getSaleTypeNameAttribute()
    {
        switch ($this->sale_type) {
            case 'standard': return '标品销售';
            case 'blind_bag': return '盲袋销售';
            default: return '混合销售';
        }
    }

    /**
     * 检查是否是标品销售
     */
    public function isStandardSale()
    {
        return $this->sale_type === self::SALE_TYPE_STANDARD;
    }

    /**
     * 检查是否是盲袋销售
     */
    public function isBlindBagSale()
    {
        return $this->sale_type === self::SALE_TYPE_BLIND_BAG;
    }

    /**
     * 检查是否是混合销售
     */
    public function isMixedSale()
    {
        return $this->sale_type === self::SALE_TYPE_MIXED;
    }

    /**
     * 获取总销售数量
     */
    public function getTotalQuantityAttribute()
    {
        $standardQuantity = $this->saleDetails()->sum('quantity');
        $blindBagQuantity = $this->blindBagSales()->sum('quantity');
        $priceSeriesQuantity = $this->priceSeriesSaleDetails()->sum('quantity');
        
        return $standardQuantity + $blindBagQuantity + $priceSeriesQuantity;
    }

    /**
     * 重新计算销售总额和利润
     */
    public function recalculateTotals()
    {
        $standardTotal = $this->saleDetails()->sum('total');
        $standardProfit = $this->saleDetails()->sum('profit');
        
        $blindBagTotal = $this->blindBagSales()->sum('total_amount');
        $blindBagProfit = $this->blindBagSales()->sum('profit');
        
        // 兼容旧的价格系列销售
        $priceSeriesTotal = 0;
        $priceSeriesProfit = 0;
        foreach ($this->priceSeriesSaleDetails as $detail) {
            $amount = $detail->quantity * $detail->priceSeries->code;
            $priceSeriesTotal += $amount;
            $priceSeriesProfit += $amount - $detail->total_cost;
        }
        
        $this->total_amount = $standardTotal + $blindBagTotal + $priceSeriesTotal;
        $this->total_profit = $standardProfit + $blindBagProfit + $priceSeriesProfit;
        $this->total_cost = $this->total_amount - $this->total_profit;
        $this->profit_rate = $this->total_amount > 0 ? ($this->total_profit / $this->total_amount) * 100 : 0;
        
        // 确定销售类型
        $hasStandard = $this->saleDetails()->exists();
        $hasBlindBag = $this->blindBagSales()->exists();
        $hasPriceSeries = $this->priceSeriesSaleDetails()->exists();
        
        if (($hasStandard && $hasBlindBag) || ($hasStandard && $hasPriceSeries) || ($hasBlindBag && $hasPriceSeries)) {
            $this->sale_type = self::SALE_TYPE_MIXED;
        } elseif ($hasBlindBag || $hasPriceSeries) {
            $this->sale_type = self::SALE_TYPE_BLIND_BAG;
        } else {
            $this->sale_type = self::SALE_TYPE_STANDARD;
        }
        
        $this->save();
        return $this;
    }

    /**
     * 获取销售摘要
     */
    public function getSummaryAttribute()
    {
        $items = [];
        
        if ($this->saleDetails()->exists()) {
            $count = $this->saleDetails()->count();
            $items[] = "{$count}个标品";
        }
        
        if ($this->blindBagSales()->exists()) {
            $count = $this->blindBagSales()->sum('quantity');
            $items[] = "{$count}个盲袋";
        }
        
        if ($this->priceSeriesSaleDetails()->exists()) {
            $count = $this->priceSeriesSaleDetails()->sum('quantity');
            $items[] = "{$count}个价格系列商品";
        }
        
        return implode(', ', $items);
    }

    /**
     * 检查是否可以删除销售记录
     * 通常只有创建者或管理员可以删除
     */
    public function canDelete()
    {
        $user = auth()->user();
        
        // 管理员可以删除任何记录
        if ($user->isAdmin()) {
            return true;
        }
        
        // 创建者可以删除自己的记录
        if ($this->user_id === $user->id) {
            return true;
        }
        
        // 可以添加其他业务逻辑，比如时间限制等
        // 例如：只能删除24小时内的记录
        // if ($this->created_at->diffInHours(now()) <= 24) {
        //     return true;
        // }
        
        return false;
    }

    /**
     * 自动处理销售类型
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (!isset($sale->sale_type)) {
                $sale->sale_type = self::SALE_TYPE_STANDARD;
            }
        });
    }
} 