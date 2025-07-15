# 脚本文件清单

## 概述
本目录包含项目开发和维护过程中使用的各种脚本工具。

## 脚本文件说明

### 1. 性能分析脚本

#### `performance_monitor.php`
- **用途**: 系统性能监控脚本
- **功能**: 监控系统运行状态，收集性能数据
- **使用方式**: `php scripts/performance_monitor.php`

### 2. 开发辅助脚本

#### `replace_translations.php`
- **用途**: 批量替换翻译文本
- **功能**: 将硬编码的中文文本替换为多语言翻译键
- **使用方式**: `php scripts/replace_translations.php`

#### `check_untranslated.php`
- **用途**: 检查未翻译的文本
- **功能**: 扫描视图文件，找出未使用翻译键的硬编码文本
- **使用方式**: `php scripts/check_untranslated.php`

### 3. 性能优化脚本

#### `optimize_cdn_resources.php`
- **用途**: CDN资源优化
- **功能**: 优化静态资源加载，提升页面性能
- **使用方式**: `php scripts/optimize_cdn_resources.php`

#### `analyze_view_performance.php`
- **用途**: 视图性能分析
- **功能**: 分析视图文件渲染性能
- **使用方式**: `php scripts/analyze_view_performance.php`

#### `analyze_dashboard_performance.php`
- **用途**: 仪表盘性能分析
- **功能**: 专门分析仪表盘页面性能
- **使用方式**: `php scripts/analyze_dashboard_performance.php`

#### `check_all_pages_performance.php`
- **用途**: 全页面性能检查
- **功能**: 检查所有页面的性能指标
- **使用方式**: `php scripts/check_all_pages_performance.php`

### 4. 深度分析脚本

#### `deep_performance_analysis.php`
- **用途**: 深度性能分析
- **功能**: 进行详细的系统性能分析
- **使用方式**: `php scripts/deep_performance_analysis.php`

#### `simple_performance_analysis.php`
- **用途**: 简单性能分析
- **功能**: 快速进行基础性能检查
- **使用方式**: `php scripts/simple_performance_analysis.php`

## 使用说明

### 运行环境
- PHP 8.1+
- Laravel 10.x
- 项目根目录执行

### 通用参数
- `--help`: 显示帮助信息
- `--verbose`: 详细输出模式
- `--output`: 指定输出文件

### 注意事项
1. 运行脚本前请确保数据库连接正常
2. 性能分析脚本可能需要较长时间运行
3. 建议在开发环境中使用这些脚本
4. 某些脚本可能需要管理员权限

## 维护说明

### 定期维护
- 每月运行性能分析脚本
- 定期检查未翻译文本
- 监控系统性能变化

### 故障排除
- 脚本执行失败时检查PHP错误日志
- 确保脚本文件权限正确
- 验证数据库连接配置

## 开发建议

### 脚本开发规范
- 使用清晰的注释说明功能
- 提供详细的参数说明
- 包含错误处理机制
- 支持日志记录功能

### 性能优化建议
- 定期运行性能分析脚本
- 根据分析结果优化代码
- 监控关键性能指标
- 及时处理性能问题 