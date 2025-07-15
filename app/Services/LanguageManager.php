<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LanguageManager
{
    protected $supportedLanguages = ['zh_CN', 'vi', 'en'];
    protected $defaultLanguage = 'zh_CN';
    protected $fallbackLanguage = 'zh_CN';
    
    /**
     * 获取当前语言
     */
    public function getCurrentLanguage()
    {
        // 首先检查 URL 查询参数
        $language = request()->get('lang');
        
        // 如果 URL 参数存在且支持，则设置到 Session
        if ($language && in_array($language, $this->supportedLanguages)) {
            Session::put('locale', $language);
        } else {
            // 否则使用 Session 中的语言或默认语言
            $language = Session::get('locale') ?? App::getLocale();
        }
        
        // 验证语言是否支持
        if (!in_array($language, $this->supportedLanguages)) {
            $language = $this->defaultLanguage;
            Session::put('locale', $language);
        }
        
        return $language;
    }
    
    /**
     * 设置语言
     */
    public function setLanguage($language)
    {
        if (!in_array($language, $this->supportedLanguages)) {
            Log::warning("Unsupported language: {$language}");
            $language = $this->defaultLanguage;
        }
        
        Session::put('locale', $language);
        App::setLocale($language);
        
        // 清除翻译缓存
        Cache::forget("translations_{$language}");
        
        return $language;
    }
    
    /**
     * 获取翻译，带错误处理
     */
    public function getTranslation($key, $parameters = [], $language = null)
    {
        $language = $language ?? $this->getCurrentLanguage();
        
        try {
            // 临时设置语言
            $originalLocale = App::getLocale();
            App::setLocale($language);
            
            // 尝试获取翻译
            $translation = __($key, $parameters);
            
            // 恢复原始语言
            App::setLocale($originalLocale);
            
            // 如果返回的是键名本身，说明翻译不存在
            if ($translation === "messages.{$key}") {
                // 尝试回退语言
                if ($language !== $this->fallbackLanguage) {
                    App::setLocale($this->fallbackLanguage);
                    $translation = __("messages.{$key}", $parameters);
                    App::setLocale($originalLocale);
                    
                    if ($translation === "messages.{$key}") {
                        // 记录缺失的翻译
                        $this->logMissingTranslation($key, $language);
                        return $this->formatMissingKey($key);
                    }
                } else {
                    // 记录缺失的翻译
                    $this->logMissingTranslation($key, $language);
                    return $this->formatMissingKey($key);
                }
            }
            
            return $translation;
            
        } catch (\Exception $e) {
            Log::error("Translation error for key: {$key}", [
                'language' => $language,
                'error' => $e->getMessage()
            ]);
            
            return $this->formatMissingKey($key);
        }
    }
    
    /**
     * 格式化缺失的键
     */
    protected function formatMissingKey($key)
    {
        // 开发环境显示键名，生产环境显示友好的错误信息
        if (config('app.debug')) {
            return "[{$key}]";
        }
        
        return $this->getFallbackText($key);
    }
    
    /**
     * 获取回退文本
     */
    protected function getFallbackText($key)
    {
        $fallbacks = [
            'dashboard.title' => 'Dashboard',
            'products.title' => 'Products',
            'inventory.title' => 'Inventory',
            'sales.title' => 'Sales',
            'categories.title' => 'Categories',
            'users.title' => 'Users',
            'stores.title' => 'Stores',
            'reports.title' => 'Reports',
            'settings.title' => 'Settings',
        ];
        
        return $fallbacks[$key] ?? Str::title(str_replace('.', ' ', $key));
    }
    
    /**
     * 记录缺失的翻译
     */
    protected function logMissingTranslation($key, $language)
    {
        $cacheKey = "missing_translations_{$language}";
        $missing = Cache::get($cacheKey, []);
        
        if (!in_array($key, $missing)) {
            $missing[] = $key;
            Cache::put($cacheKey, $missing, 3600); // 缓存1小时
        }
        
        Log::info("Missing translation", [
            'key' => $key,
            'language' => $language
        ]);
    }
    
    /**
     * 获取缺失的翻译列表
     */
    public function getMissingTranslations($language = null)
    {
        $language = $language ?? $this->getCurrentLanguage();
        $cacheKey = "missing_translations_{$language}";
        
        return Cache::get($cacheKey, []);
    }
    
    /**
     * 清除缺失翻译缓存
     */
    public function clearMissingTranslationsCache($language = null)
    {
        $language = $language ?? $this->getCurrentLanguage();
        $cacheKey = "missing_translations_{$language}";
        
        Cache::forget($cacheKey);
    }
    
    /**
     * 获取支持的语言列表
     */
    public function getSupportedLanguages()
    {
        return [
            'zh_CN' => '中文',
            'vi' => 'Tiếng Việt',
            'en' => 'English',
        ];
    }
    
    /**
     * 验证翻译文件完整性
     */
    public function validateTranslationFiles()
    {
        $results = [];
        
        foreach ($this->supportedLanguages as $language) {
            $filePath = resource_path("lang/{$language}/messages.php");
            
            if (!file_exists($filePath)) {
                $results[$language] = [
                    'status' => 'missing',
                    'message' => "Translation file not found: {$filePath}"
                ];
                continue;
            }
            
            try {
                $translations = require $filePath;
                $results[$language] = [
                    'status' => 'valid',
                    'count' => count($translations, COUNT_RECURSIVE)
                ];
            } catch (\Exception $e) {
                $results[$language] = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
} 