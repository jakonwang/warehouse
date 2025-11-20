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
            // 如果是完整URL，提取路径
            if (str_starts_with($path, 'http')) {
                // 从URL中提取路径
                $domain = config('filesystems.disks.qiniu.domain');
                if ($domain && str_contains($path, $domain)) {
                    $path = str_replace(rtrim($domain, '/') . '/', '', $path);
                } else {
                    // 如果无法提取，尝试从URL解析
                    $parsed = parse_url($path);
                    $path = ltrim($parsed['path'] ?? '', '/');
                }
            }

            return Storage::disk('qiniu')->delete($path);
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

        return Storage::disk('qiniu')->url($path);
    }
}

