<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LanguageManager;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    protected $languageManager;
    
    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }
    
    /**
     * 切换语言
     */
    public function switchLanguage(Request $request, $language)
    {
        $this->languageManager->setLanguage($language);
        return redirect()->back();
    }
    
    /**
     * 获取当前语言
     */
    public function getCurrentLanguage()
    {
        try {
            $currentLanguage = $this->languageManager->getCurrentLanguage();
            $supportedLanguages = $this->languageManager->getSupportedLanguages();
            
            return response()->json([
                'success' => true,
                'current_language' => $currentLanguage,
                'supported_languages' => $supportedLanguages
            ]);
            
        } catch (\Exception $e) {
            Log::error("Get current language failed", [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get current language',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取缺失的翻译
     */
    public function getMissingTranslations(Request $request)
    {
        try {
            $language = $request->get('language');
            $missingTranslations = $this->languageManager->getMissingTranslations($language);
            
            return response()->json([
                'success' => true,
                'missing_translations' => $missingTranslations,
                'count' => count($missingTranslations)
            ]);
            
        } catch (\Exception $e) {
            Log::error("Get missing translations failed", [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get missing translations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 验证翻译文件
     */
    public function validateTranslationFiles()
    {
        try {
            $validationResults = $this->languageManager->validateTranslationFiles();
            
            return response()->json([
                'success' => true,
                'validation_results' => $validationResults
            ]);
            
        } catch (\Exception $e) {
            Log::error("Validate translation files failed", [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate translation files',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 清除翻译缓存
     */
    public function clearTranslationCache(Request $request)
    {
        try {
            $language = $request->get('language');
            $this->languageManager->clearMissingTranslationsCache($language);
            
            return response()->json([
                'success' => true,
                'message' => 'Translation cache cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Clear translation cache failed", [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear translation cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 