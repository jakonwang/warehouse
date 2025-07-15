<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LanguageManager;
use Illuminate\Support\Facades\App;

class SetLocale
{
    protected $languageManager;
    
    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // 获取当前语言
            $currentLanguage = $this->languageManager->getCurrentLanguage();
            
            // 设置应用语言
            App::setLocale($currentLanguage);
            
            // 将语言信息传递给视图
            view()->share('currentLanguage', $currentLanguage);
            view()->share('supportedLanguages', $this->languageManager->getSupportedLanguages());
            
        } catch (\Exception $e) {
            // 记录错误但不中断请求
            \Log::error("SetLocale middleware error", [
                'error' => $e->getMessage(),
                'url' => $request->url()
            ]);
            
            // 使用默认语言
            App::setLocale('zh_CN');
        }
        
        return $next($request);
    }
} 