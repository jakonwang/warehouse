<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    /**
     * 获取翻译，支持多层回退
     */
    public static function get($key, $params = [], $fallback = null)
    {
        try {
            $currentLocale = App::getLocale();
            $fallbackLocale = config('app.fallback_locale');
            
            // 尝试当前语言
            $translation = self::tryGetTranslation($key, $params, $currentLocale);
            if ($translation !== $key) {
                return $translation;
            }
            
            // 尝试备用语言
            if ($currentLocale !== $fallbackLocale) {
                $translation = self::tryGetTranslation($key, $params, $fallbackLocale);
                if ($translation !== $key) {
                    return $translation;
                }
            }
            
            // 记录缺失的翻译键
            self::logMissingTranslation($key, $currentLocale);
            
            // 返回备用文本或键名
            return $fallback ?? $key;
            
        } catch (\Exception $e) {
            self::logTranslationError($key, $e);
            return $fallback ?? $key;
        }
    }
    
    /**
     * 尝试获取翻译
     */
    private static function tryGetTranslation($key, $params, $locale)
    {
        $originalLocale = App::getLocale();
        App::setLocale($locale);
        
        try {
            if (empty($params)) {
                $translation = __($key);
            } else {
                $translation = __($key, $params);
            }
        } finally {
            App::setLocale($originalLocale);
        }
        
        return $translation;
    }
    
    /**
     * 记录缺失的翻译键
     */
    private static function logMissingTranslation($key, $locale)
    {
        if (config('app.debug')) {
            $cacheKey = "missing_translation_{$locale}_{$key}";
            
            // 避免重复记录同一个缺失的翻译键
            if (!Cache::has($cacheKey)) {
                Cache::put($cacheKey, true, now()->addHours(24));
                
                Log::warning('Missing translation key', [
                    'key' => $key,
                    'locale' => $locale,
                    'url' => request()->url(),
                    'user_agent' => request()->userAgent()
                ]);
            }
        }
    }
    
    /**
     * 记录翻译错误
     */
    private static function logTranslationError($key, $exception)
    {
        if (config('app.debug')) {
            Log::error('Translation error', [
                'key' => $key,
                'error' => $exception->getMessage(),
                'locale' => App::getLocale(),
                'url' => request()->url()
            ]);
        }
    }
    
    /**
     * 获取所有缺失的翻译键
     */
    public static function getMissingTranslations($locale = null)
    {
        $locale = $locale ?? App::getLocale();
        $cacheKey = "missing_translations_{$locale}";
        
        return Cache::get($cacheKey, []);
    }
    
    /**
     * 检查翻译键是否存在
     */
    public static function has($key, $locale = null)
    {
        $locale = $locale ?? App::getLocale();
        $translation = self::tryGetTranslation($key, [], $locale);
        
        return $translation !== $key;
    }
} 