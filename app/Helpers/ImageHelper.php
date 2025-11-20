<?php

if (!function_exists('get_image_url')) {
    /**
     * 获取图片URL（支持七牛云和本地存储）
     * 
     * @param string|null $path 图片路径
     * @return string
     */
    function get_image_url(?string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // 如果已经是完整URL（七牛云URL），直接返回
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // 检查是否是七牛云路径（以products/、sales/等开头）
        if (preg_match('/^(products|sales|returns|stock-out|blind-bag-sales)\//', $path)) {
            try {
                return \Illuminate\Support\Facades\Storage::disk('qiniu')->url($path);
            } catch (\Exception $e) {
                // 如果七牛云配置有问题，回退到本地存储
                return \Illuminate\Support\Facades\Storage::url($path);
            }
        }

        // 兼容旧数据：检查图片是否在uploads目录
        if (str_contains($path, 'uploads/')) {
            return asset($path);
        }

        // 兼容旧数据：检查图片是否在storage目录
        if (str_contains($path, 'storage/')) {
            return asset($path);
        }

        // 默认尝试使用七牛云，如果失败则使用本地存储
        try {
            return \Illuminate\Support\Facades\Storage::disk('qiniu')->url($path);
        } catch (\Exception $e) {
            return \Illuminate\Support\Facades\Storage::url($path);
        }
    }
}

