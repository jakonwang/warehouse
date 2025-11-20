<?php

namespace App\Services;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class QiniuStorageService
{
    protected $auth;
    protected $uploadManager;
    protected $bucketManager;
    protected $bucket;
    protected $domain;

    public function __construct()
    {
        $accessKey = config('filesystems.disks.qiniu.access_key');
        $secretKey = config('filesystems.disks.qiniu.secret_key');
        $this->bucket = config('filesystems.disks.qiniu.bucket');
        $this->domain = config('filesystems.disks.qiniu.domain');

        if (!$accessKey || !$secretKey || !$this->bucket) {
            throw new \Exception('七牛云配置不完整，请检查.env文件中的QINIU配置');
        }

        $this->auth = new Auth($accessKey, $secretKey);
        $this->uploadManager = new UploadManager();
        $this->bucketManager = new BucketManager($this->auth);
    }

    /**
     * 上传文件到七牛云
     * 
     * @param string|UploadedFile $file 文件路径或上传的文件对象
     * @param string $path 存储路径
     * @return string 文件URL
     */
    public function put($file, $path)
    {
        try {
            // 如果是上传的文件对象，获取临时文件路径
            if ($file instanceof UploadedFile) {
                $filePath = $file->getRealPath();
                $fileName = $file->getClientOriginalName();
            } else {
                $filePath = $file;
                $fileName = basename($file);
            }

            if (!file_exists($filePath)) {
                throw new \Exception("文件不存在: {$filePath}");
            }

            // 生成上传token
            $token = $this->auth->uploadToken($this->bucket);

            // 上传文件
            list($ret, $err) = $this->uploadManager->putFile($token, $path, $filePath);

            if ($err !== null) {
                Log::error('七牛云上传失败', [
                    'error' => $err->message(),
                    'code' => $err->code(),
                    'path' => $path
                ]);
                throw new \Exception('七牛云上传失败: ' . $err->message());
            }

            // 返回完整的URL
            return $this->url($ret['key']);

        } catch (\Exception $e) {
            Log::error('七牛云上传异常', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            throw $e;
        }
    }

    /**
     * 删除七牛云文件
     * 
     * @param string $path 文件路径
     * @return bool
     */
    public function delete($path)
    {
        try {
            // 如果路径是完整URL，提取key
            if (str_starts_with($path, 'http')) {
                $path = parse_url($path, PHP_URL_PATH);
                $path = ltrim($path, '/');
            }

            list($ret, $err) = $this->bucketManager->delete($this->bucket, $path);

            if ($err !== null) {
                Log::warning('七牛云删除失败', [
                    'error' => $err->message(),
                    'path' => $path
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('七牛云删除异常', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            return false;
        }
    }

    /**
     * 获取文件URL
     * 
     * @param string $path 文件路径
     * @return string
     */
    public function url($path)
    {
        // 如果已经是完整URL，直接返回
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // 如果路径是完整URL，提取key
        if (str_contains($path, $this->domain)) {
            return $path;
        }

        // 拼接完整URL
        return rtrim($this->domain, '/') . '/' . ltrim($path, '/');
    }

    /**
     * 检查文件是否存在
     * 
     * @param string $path 文件路径
     * @return bool
     */
    public function exists($path)
    {
        try {
            // 如果路径是完整URL，提取key
            if (str_starts_with($path, 'http')) {
                $path = parse_url($path, PHP_URL_PATH);
                $path = ltrim($path, '/');
            }

            list($ret, $err) = $this->bucketManager->stat($this->bucket, $path);

            return $err === null;
        } catch (\Exception $e) {
            return false;
        }
    }
}

