<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait HasQiniuUpload
{
    /**
     * 上传文件到七牛云
     * 
     * @param UploadedFile $file 上传的文件
     * @param string $directory 存储目录
     * @return string 文件URL
     */
    protected function uploadToQiniu(UploadedFile $file, string $directory): string
    {
        try {
            // 生成唯一文件名
            $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $directory . '/' . $filename;
            
            // 使用七牛云服务直接上传
            $qiniuService = app(\App\Services\QiniuStorageService::class);
            $url = $qiniuService->put($file, $path);
            
            return $url;
        } catch (\Exception $e) {
            \Log::error('七牛云上传失败', [
                'error' => $e->getMessage(),
                'directory' => $directory,
                'file' => $file->getClientOriginalName()
            ]);
            throw $e;
        }
    }

    /**
     * 删除七牛云文件
     * 
     * @param string $path 文件路径或URL
     * @return bool
     */
    protected function deleteFromQiniu(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        try {
            // 直接使用QiniuStorageService删除
            $qiniuService = app(\App\Services\QiniuStorageService::class);
            $result = $qiniuService->delete($path);
            
            // 确保返回bool类型
            return (bool) $result;
        } catch (\Exception $e) {
            \Log::warning('删除七牛云文件失败', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 获取七牛云文件URL
     * 
     * @param string $path 文件路径
     * @return string
     */
    protected function getQiniuUrl(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // 如果已经是完整URL，直接返回
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        try {
            $qiniuService = app(\App\Services\QiniuStorageService::class);
            return $qiniuService->url($path);
        } catch (\Exception $e) {
            \Log::warning('获取七牛云URL失败', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }
}

