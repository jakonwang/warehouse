<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemConfig extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * 获取系统配置
     */
    public static function getConfig($key, $default = null)
    {
        $configs = Cache::remember('system_configs', 3600, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
        
        return $configs[$key] ?? $default;
    }

    /**
     * 获取基础系列价格
     */
    public static function getBasicSeriesPrice($series)
    {
        return self::getConfig("basic_series_{$series}", 0);
    }

    /**
     * 获取价格系列成本
     */
    public static function getPriceSeriesCost($price)
    {
        return self::getConfig("price_series_{$price}", 0);
    }

    /**
     * 清除配置缓存
     */
    public static function clearCache()
    {
        Cache::forget('system_configs');
    }

    /**
     * 保存配置时清除缓存
     */
    protected static function booted()
    {
        static::saved(function ($config) {
            self::clearCache();
        });

        static::deleted(function ($config) {
            self::clearCache();
        });
    }
} 