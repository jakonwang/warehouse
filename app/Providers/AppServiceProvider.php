<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 注册Blade组件
        Blade::component('layouts.mobile', 'mobile-layout');
        Schema::defaultStringLength(191);
        
        // 注册全局翻译助手函数
        Blade::directive('lang', function ($expression) {
            return "<?php echo __('messages.' . $expression); ?>";
        });

        // 注册权限门面
        Gate::define('isSuperAdmin', function ($user) {
            return $user->isSuperAdmin();
        });

        // 全局注入当前用户可用仓库
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user) {
                if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                    // 超级管理员显示所有启用仓库
                    $stores = \App\Models\Store::where('is_active', true)->get();
                } else {
                    // 普通用户只显示分配的启用仓库
                    $stores = $user->stores()->where('is_active', true)->get();
                }
            } else {
                $stores = collect();
            }
            
            // 只在非用户编辑页面注入全局 userStores
            if (!str_contains($view->getName(), 'users.edit')) {
                $view->with('userStores', $stores);
            }
            
            // 注入当前仓库对象
            $currentStoreId = session('current_store_id');
            $currentStore = ($currentStoreId && $currentStoreId != 0) ? \App\Models\Store::find($currentStoreId) : null;
            $view->with('currentStore', $currentStore);
            $view->with('currentStoreId', $currentStoreId);
        });
    }
} 