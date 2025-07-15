<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TranslationService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class TranslationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:check {locale?} {--sync : 同步缺失的翻译键}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查翻译键的完整性';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $locale = $this->argument('locale') ?? config('app.locale');
        $sync = $this->option('sync');
        
        $this->info("检查 {$locale} 语言的翻译键...");
        
        // 获取所有语言文件
        $langPath = resource_path('lang');
        $locales = array_diff(scandir($langPath), ['.', '..']);
        
        if (!in_array($locale, $locales)) {
            $this->error("语言 {$locale} 不存在！");
            return 1;
        }
        
        // 获取参考语言（通常是中文）
        $referenceLocale = 'zh_CN';
        $referenceKeys = $this->getTranslationKeys($referenceLocale);
        
        // 获取目标语言的翻译键
        $targetKeys = $this->getTranslationKeys($locale);
        
        // 找出缺失的翻译键
        $missingKeys = array_diff_key($referenceKeys, $targetKeys);
        
        if (empty($missingKeys)) {
            $this->info("✅ {$locale} 语言的翻译键完整！");
            return 0;
        }
        
        $this->warn("❌ 发现 " . count($missingKeys) . " 个缺失的翻译键：");
        
        foreach ($missingKeys as $key => $value) {
            $this->line("  - {$key}");
        }
        
        if ($sync) {
            $this->syncMissingKeys($locale, $missingKeys, $referenceKeys);
        }
        
        return 0;
    }
    
    /**
     * 获取翻译键
     */
    private function getTranslationKeys($locale)
    {
        $filePath = resource_path("lang/{$locale}/messages.php");
        
        if (!File::exists($filePath)) {
            return [];
        }
        
        $messages = include $filePath;
        return $this->flattenArray($messages);
    }
    
    /**
     * 扁平化数组
     */
    private function flattenArray($array, $prefix = '')
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;
            
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * 同步缺失的翻译键
     */
    private function syncMissingKeys($locale, $missingKeys, $referenceKeys)
    {
        $this->info("正在同步缺失的翻译键...");
        
        $filePath = resource_path("lang/{$locale}/messages.php");
        $messages = include $filePath;
        
        foreach ($missingKeys as $key => $value) {
            $this->addTranslationKey($messages, $key, $value);
        }
        
        // 保存文件
        $content = "<?php\n\nreturn " . var_export($messages, true) . ";\n";
        File::put($filePath, $content);
        
        $this->info("✅ 已同步 " . count($missingKeys) . " 个翻译键到 {$locale} 语言文件");
    }
    
    /**
     * 添加翻译键到数组
     */
    private function addTranslationKey(&$array, $key, $value)
    {
        $keys = explode('.', $key);
        $current = &$array;
        
        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }
        
        $current = $value;
    }
} 