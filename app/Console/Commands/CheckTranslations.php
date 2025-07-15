<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LanguageManager;
use Illuminate\Support\Facades\File;

class CheckTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:check {language?} {--fix : 自动修复缺失的翻译}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查翻译文件的完整性';

    protected $languageManager;

    public function __construct(LanguageManager $languageManager)
    {
        parent::__construct();
        $this->languageManager = $languageManager;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $language = $this->argument('language');
        $fix = $this->option('fix');

        if ($language) {
            $this->checkSingleLanguage($language, $fix);
        } else {
            $this->checkAllLanguages($fix);
        }
    }

    /**
     * 检查单个语言
     */
    protected function checkSingleLanguage($language, $fix)
    {
        $this->info("检查语言: {$language}");

        // 验证翻译文件
        $validationResults = $this->languageManager->validateTranslationFiles();
        
        if (!isset($validationResults[$language])) {
            $this->error("语言 {$language} 不支持");
            return;
        }

        $result = $validationResults[$language];
        
        if ($result['status'] === 'missing') {
            $this->error("翻译文件缺失: {$result['message']}");
            return;
        }

        if ($result['status'] === 'error') {
            $this->error("翻译文件错误: {$result['message']}");
            return;
        }

        // 获取缺失的翻译
        $missingTranslations = $this->languageManager->getMissingTranslations($language);
        
        if (empty($missingTranslations)) {
            $this->info("✓ 语言 {$language} 翻译完整");
            return;
        }

        $this->warn("发现 " . count($missingTranslations) . " 个缺失的翻译:");
        
        foreach ($missingTranslations as $key) {
            $this->line("  - {$key}");
        }

        if ($fix) {
            $this->fixMissingTranslations($language, $missingTranslations);
        }
    }

    /**
     * 检查所有语言
     */
    protected function checkAllLanguages($fix)
    {
        $this->info("检查所有支持的语言...");

        $supportedLanguages = $this->languageManager->getSupportedLanguages();
        $totalMissing = 0;

        foreach ($supportedLanguages as $code => $name) {
            $this->line("\n检查语言: {$name} ({$code})");
            
            $missingTranslations = $this->languageManager->getMissingTranslations($code);
            $count = count($missingTranslations);
            $totalMissing += $count;

            if ($count === 0) {
                $this->info("✓ 翻译完整");
            } else {
                $this->warn("发现 {$count} 个缺失的翻译");
                
                if ($fix) {
                    $this->fixMissingTranslations($code, $missingTranslations);
                }
            }
        }

        $this->line("\n总计: {$totalMissing} 个缺失的翻译");
    }

    /**
     * 修复缺失的翻译
     */
    protected function fixMissingTranslations($language, $missingTranslations)
    {
        $this->info("开始修复缺失的翻译...");

        $filePath = resource_path("lang/{$language}/messages.php");
        
        if (!File::exists($filePath)) {
            $this->error("翻译文件不存在: {$filePath}");
            return;
        }

        $translations = require $filePath;
        $addedCount = 0;

        foreach ($missingTranslations as $key) {
            if (!$this->hasTranslation($translations, $key)) {
                $translations = $this->addTranslation($translations, $key, $this->generateDefaultTranslation($key));
                $addedCount++;
            }
        }

        if ($addedCount > 0) {
            $this->saveTranslations($filePath, $translations);
            $this->info("✓ 添加了 {$addedCount} 个翻译");
        } else {
            $this->info("没有需要添加的翻译");
        }
    }

    /**
     * 检查是否有翻译
     */
    protected function hasTranslation($translations, $key)
    {
        $keys = explode('.', $key);
        $current = $translations;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                return false;
            }
            $current = $current[$k];
        }

        return true;
    }

    /**
     * 添加翻译
     */
    protected function addTranslation($translations, $key, $value)
    {
        $keys = explode('.', $key);
        $current = &$translations;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
        return $translations;
    }

    /**
     * 生成默认翻译
     */
    protected function generateDefaultTranslation($key)
    {
        // 简单的默认翻译生成
        $parts = explode('.', $key);
        $lastPart = end($parts);
        
        return ucfirst(str_replace('_', ' ', $lastPart));
    }

    /**
     * 保存翻译文件
     */
    protected function saveTranslations($filePath, $translations)
    {
        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        File::put($filePath, $content);
    }
} 