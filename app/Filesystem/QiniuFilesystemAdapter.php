<?php

namespace App\Filesystem;

use App\Services\QiniuStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToCheckExistence;

class QiniuFilesystemAdapter implements FilesystemAdapter
{
    protected $qiniuService;

    public function __construct(QiniuStorageService $qiniuService)
    {
        $this->qiniuService = $qiniuService;
    }

    public function fileExists(string $path): bool
    {
        try {
            return $this->qiniuService->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function directoryExists(string $path): bool
    {
        // 七牛云没有目录概念，返回true
        return true;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        try {
            // 创建临时文件
            $tempFile = tempnam(sys_get_temp_dir(), 'qiniu_');
            file_put_contents($tempFile, $contents);
            
            $url = $this->qiniuService->put($tempFile, $path);
            
            // 清理临时文件
            @unlink($tempFile);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        try {
            // 创建临时文件
            $tempFile = tempnam(sys_get_temp_dir(), 'qiniu_');
            $handle = fopen($tempFile, 'w');
            stream_copy_to_stream($contents, $handle);
            fclose($handle);
            
            $url = $this->qiniuService->put($tempFile, $path);
            
            // 清理临时文件
            @unlink($tempFile);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function read(string $path): string
    {
        try {
            $url = $this->qiniuService->url($path);
            $contents = file_get_contents($url);
            
            if ($contents === false) {
                throw new \Exception("无法读取文件: {$path}");
            }
            
            return $contents;
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function readStream(string $path)
    {
        try {
            $url = $this->qiniuService->url($path);
            $stream = fopen($url, 'r');
            
            if ($stream === false) {
                throw new \Exception("无法读取文件流: {$path}");
            }
            
            return $stream;
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function delete(string $path): void
    {
        try {
            if (!$this->qiniuService->delete($path)) {
                throw new \Exception("删除文件失败: {$path}");
            }
        } catch (\Exception $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage());
        }
    }

    public function deleteDirectory(string $path): void
    {
        // 七牛云没有目录概念，不需要实现
    }

    public function createDirectory(string $path, Config $config): void
    {
        // 七牛云没有目录概念，不需要实现
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // 七牛云文件默认公开，不需要实现
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'public');
    }

    public function mimeType(string $path): FileAttributes
    {
        // 简化实现，返回基本属性
        return new FileAttributes($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        // 简化实现，返回基本属性
        return new FileAttributes($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        // 简化实现，返回基本属性
        return new FileAttributes($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        // 七牛云需要特殊API来列出文件，这里简化实现
        return [];
    }

    public function move(string $source, string $destination, Config $config): void
    {
        // 先复制，再删除原文件
        $contents = $this->read($source);
        $this->write($destination, $contents, $config);
        $this->delete($source);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $contents = $this->read($source);
        $this->write($destination, $contents, $config);
    }
}

