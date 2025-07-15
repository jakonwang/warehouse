# 多语言组件使用指南

## 组件介绍

`<x-lang>` 是一个统一的多语言调用组件，用于替代传统的 `{{ __('messages.xxx.xxx') }}` 写法。

## 基本用法

### 1. 简单翻译
```blade
<!-- 旧写法 -->
{{ __('messages.dashboard.title') }}

<!-- 新写法 -->
<x-lang key="messages.dashboard.title"/>
```

### 2. 带参数的翻译
```blade
<!-- 旧写法 -->
{{ __('messages.stock_ins.total_records', ['count' => $stockIns->total()]) }}

<!-- 新写法 -->
<x-lang key="messages.stock_ins.total_records" :params="['count' => $stockIns->total()]"/>
```

### 3. 在属性中使用
```blade
<!-- 旧写法 -->
<h1>{{ __('messages.dashboard.title') }}</h1>

<!-- 新写法 -->
<h1><x-lang key="messages.dashboard.title"/></h1>
```

### 4. 在条件语句中使用
```blade
<!-- 旧写法 -->
@if($condition)
    {{ __('messages.dashboard.success') }}
@else
    {{ __('messages.dashboard.error') }}
@endif

<!-- 新写法 -->
@if($condition)
    <x-lang key="messages.dashboard.success"/>
@else
    <x-lang key="messages.dashboard.error"/>
@endif
```

## 批量替换脚本

### 运行批量替换
```bash
php scripts/replace_translations.php
```

此脚本会自动扫描 `resources/views` 目录下的所有 `.blade.php` 文件，将：
- `{{ __('messages.xxx.xxx') }}` 替换为 `<x-lang key="messages.xxx.xxx"/>`
- `{{ __('messages.xxx.xxx', ['param' => 'value']) }}` 替换为 `<x-lang key="messages.xxx.xxx" :params="['param' => 'value']"/>`

### 检测未翻译内容
```bash
php scripts/check_untranslated.php
```

此脚本会扫描所有 Blade 文件，检测硬编码的中文文本，帮助发现未翻译的内容。

## 优势

1. **统一性**：所有多语言调用都使用相同的组件
2. **可维护性**：便于后续扩展和修改
3. **IDE 支持**：更好的代码提示和自动完成
4. **批量处理**：支持脚本批量替换
5. **参数支持**：支持带参数的翻译调用

## 注意事项

1. 确保语言文件中存在对应的翻译键
2. 参数传递时使用 `:params` 属性
3. 组件会自动处理空参数的情况
4. 建议在开发环境中定期运行检测脚本

## 扩展功能

如需更多功能（如自动生成翻译键、在线编辑等），可以进一步扩展组件。 