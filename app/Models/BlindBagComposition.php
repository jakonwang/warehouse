<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class BlindBagComposition extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'price_series_code',
        'probability',
        'min_quantity',
        'max_quantity',
        'description',
        'is_active'
    ];

    protected $casts = [
        'probability' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * 获取所属商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取关联的价格系列
     */
    public function priceSeries(): BelongsTo
    {
        return $this->belongsTo(PriceSeries::class, 'price_series_code', 'code');
    }

    /**
     * 查询范围 - 启用的构成
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * 查询范围 - 按商品筛选
     */
    public function scopeForProduct(Builder $query, $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * 获取随机发货内容
     * 
     * @param int $blindBagQuantity 盲袋数量
     * @return array
     */
    public static function generateRandomContent($productId, $blindBagQuantity = 1)
    {
        $compositions = self::active()
            ->forProduct($productId)
            ->with('priceSeries')
            ->get();

        if ($compositions->isEmpty()) {
            throw new \Exception('该盲袋商品未配置价格系列构成');
        }

        $result = [];
        
        for ($i = 0; $i < $blindBagQuantity; $i++) {
            $randomValue = mt_rand(1, 10000) / 100; // 0.01 - 100.00
            $accumulated = 0;
            
            foreach ($compositions as $composition) {
                $accumulated += $composition->probability;
                
                if ($randomValue <= $accumulated) {
                    $quantity = mt_rand($composition->min_quantity, $composition->max_quantity);
                    
                    if (isset($result[$composition->price_series_code])) {
                        $result[$composition->price_series_code]['quantity'] += $quantity;
                    } else {
                        $result[$composition->price_series_code] = [
                            'price_series_code' => $composition->price_series_code,
                            'quantity' => $quantity,
                            'unit_cost' => $composition->priceSeries->cost,
                            'total_cost' => $quantity * $composition->priceSeries->cost,
                        ];
                    }
                    break;
                }
            }
        }

        // 重新计算总成本
        foreach ($result as $code => &$item) {
            $item['total_cost'] = $item['quantity'] * $item['unit_cost'];
        }

        return array_values($result);
    }

    /**
     * 验证概率总和
     */
    public static function validateProbabilities($productId)
    {
        $totalProbability = self::active()
            ->forProduct($productId)
            ->sum('probability');
            
        return abs($totalProbability - 100) < 0.01; // 允许0.01的误差
    }

    /**
     * 获取构成描述
     */
    public function getDescriptionTextAttribute()
    {
        return $this->description ?: 
            "价格系列 {$this->price_series_code}，概率 {$this->probability}%，数量 {$this->min_quantity}-{$this->max_quantity}";
    }
}
