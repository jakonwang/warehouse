<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TranslationService;

class TranslationMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // 仅在开发环境或调试模式下监控
        if (config('app.debug')) {
            $this->monitorTranslationIssues();
        }
        
        return $response;
    }
    
    /**
     * 监控翻译问题
     */
    private function monitorTranslationIssues()
    {
        $missingTranslations = TranslationService::getMissingTranslations();
        
        if (!empty($missingTranslations)) {
            Log::warning('Translation issues detected', [
                'missing_keys' => $missingTranslations,
                'locale' => app()->getLocale(),
                'url' => request()->url()
            ]);
        }
    }
} 