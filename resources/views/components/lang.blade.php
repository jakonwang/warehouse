@props(['key', 'parameters' => [], 'default' => null])

@php
    // 使用 LanguageManager 服务
    $languageManager = app(\App\Services\LanguageManager::class);
    $translation = $languageManager->getTranslation($key, $parameters);
    
    // 如果翻译失败且提供了默认值，使用默认值
    if (str_starts_with($translation, '[') && str_ends_with($translation, ']') && $default !== null) {
        $translation = $default;
    }
@endphp

{{ $translation }}