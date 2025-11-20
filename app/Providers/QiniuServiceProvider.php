<?php

namespace App\Providers;

use App\Services\QiniuStorageService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use App\Filesystem\QiniuFilesystemAdapter;

class QiniuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(QiniuStorageService::class, function ($app) {
            return new QiniuStorageService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Storage::extend('qiniu', function ($app, $config) {
            $qiniuService = new QiniuStorageService();
            $adapter = new QiniuFilesystemAdapter($qiniuService);
            
            return new Filesystem($adapter);
        });
    }
}

