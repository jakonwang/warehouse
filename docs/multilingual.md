# 多语言支持文档

## 概述

本系统已实现完整的多语言支持，支持中文、英语和越南语三种语言。

## 支持的语言

- **中文 (zh_CN)** - 默认语言
- **英语 (en)** - 英语
- **越南语 (vi)** - 越南语

## 功能特性

### 1. 语言切换
- 在页面右上角有语言切换器
- 支持实时切换语言
- 语言选择会保存在Session中

### 2. 翻译文件结构
```
resources/lang/
├── zh_CN/
│   └── messages.php    # 中文翻译
├── en/
│   └── messages.php    # 英语翻译
└── vi/
    └── messages.php    # 越南语翻译
```

### 3. 翻译键分类
- **通用翻译**: success, error, confirm, cancel, save, edit, delete 等
- **导航翻译**: nav.dashboard, nav.products, nav.inventory 等
- **用户管理**: users.title, users.add, users.edit 等
- **仓库管理**: stores.title, stores.add, stores.edit 等
- **统计功能**: statistics.title, statistics.sales 等
- **销售管理**: sale.title, sale.add_sales, sale.today_sales 等
- **销售创建**: sales.create.title, sales.create.select_store 等

## 使用方法

### 1. 在Blade模板中使用翻译

```php
// 简单翻译
{{ __('success') }}

// 带参数的翻译
{{ __('users.welcome', ['name' => $user->name]) }}

// 嵌套翻译
{{ __('users.role_super_admin') }}
```

### 2. 在控制器中使用翻译

```php
// 获取翻译文本
$message = __('users.created');

// 在重定向中使用
return redirect()->route('users.index')->with('success', __('users.created'));
```

### 3. 添加新的翻译

1. 在 `resources/lang/zh_CN/messages.php` 中添加中文翻译
2. 在 `resources/lang/en/messages.php` 中添加英语翻译
3. 在 `resources/lang/vi/messages.php` 中添加越南语翻译

示例：
```php
// 在中文文件中添加
'new_feature' => [
    'title' => '新功能',
    'description' => '功能描述',
],

// 在英语文件中添加
'new_feature' => [
    'title' => 'New Feature',
    'description' => 'Feature description',
],

// 在越南语文件中添加
'new_feature' => [
    'title' => 'Tính năng mới',
    'description' => 'Mô tả tính năng',
],
```

### 4. 语言切换

用户可以通过以下方式切换语言：

1. **页面右上角的语言切换器**
2. **直接访问URL**: `/language/{locale}`
   - `/language/zh_CN` - 切换到中文
   - `/language/en` - 切换到英语
   - `/language/vi` - 切换到越南语

## 技术实现

### 1. 中间件
- `SetLocale` 中间件自动设置用户选择的语言
- 在 `app/Http/Kernel.php` 中注册到web中间件组

### 2. 控制器
- `LanguageController` 处理语言切换逻辑
- 支持的语言列表: ['en', 'zh_CN', 'vi']

### 3. 组件
- `language-switcher.blade.php` 语言切换组件
- 使用Alpine.js实现下拉菜单交互

### 4. 路由
```php
// 语言切换路由
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/language', [LanguageController::class, 'getCurrentLocale'])->name('language.current');
```

## 测试

访问 `/test-language` 页面可以测试多语言功能：

1. 查看当前语言设置
2. 测试语言切换功能
3. 验证翻译是否正确显示

## 已支持多语言的模块

### 1. 仪表板 (Dashboard)
- **路径**: `/dashboard`
- **状态**: ✅ 已完成
- **翻译键**: `messages.dashboard.*`
- **支持语言**: 中文、英文、越南语

### 2. 库存管理 (Inventory)
- **路径**: `/inventory`
- **状态**: ✅ 已完成
- **翻译键**: `messages.inventory.*`
- **支持语言**: 中文、英文、越南语

### 3. 销售管理 (Sales)
- **路径**: `/sales`
- **状态**: ✅ 已完成
- **翻译键**: `messages.sales.*`
- **支持语言**: 中文、英文、越南语

#### 3.1 销售列表页面
- **路径**: `/sales`
- **状态**: ✅ 已完成
- **翻译键**: `messages.sales.index.*`

#### 3.2 销售详情页面
- **路径**: `/sales/{id}`
- **状态**: ✅ 已完成
- **翻译键**: `messages.sales.show.*`

#### 3.3 销售创建页面
- **路径**: `/sales/create`
- **状态**: ✅ 已完成
- **翻译键**: `messages.sales.create.*`
- **功能特性**:
  - 标品销售模式
  - 盲袋销售模式
  - 客户信息录入
  - 销售统计显示
  - 注意事项提示

### 4. 分类管理 (Categories)
- **路径**: `/categories`
- **状态**: ✅ 已完成
- **翻译键**: `messages.categories.*`
- **支持语言**: 中文、英文、越南语

### 5. 退货管理 (Returns)
- **路径**: `/returns`
- **状态**: ✅ 已完成
- **翻译键**: `messages.returns.*`
- **支持语言**: 中文、英文、越南语

### 6. 销售统计 (Statistics)
- **路径**: `/statistics/sales`
- **状态**: ✅ 已完成
- **翻译键**: `messages.statistics.sales.*`
- **支持语言**: 中文、英文、越南语

### 7. 仓库管理 (Stores)
- **路径**: `/stores`
- **状态**: ✅ 已完成
- **翻译键**: `messages.stores.*`
- **支持语言**: 中文、英文、越南语

### 8. 用户管理 (Users)
- **路径**: `/users`
- **状态**: ✅ 已完成
- **翻译键**: `messages.users.*`
- **支持语言**: 中文、英文、越南语

### 9. 系统配置 (System Config)
- **路径**: `/system-config`
- **状态**: ✅ 已完成
- **翻译键**: `messages.system_config.*`
- **支持语言**: 中文、英文、越南语

## 配置

### 默认语言设置
在 `config/app.php` 中：
```php
'locale' => 'zh_CN',        // 默认语言
'fallback_locale' => 'zh_CN', // 备用语言
```

### 支持的语言
在 `LanguageController` 中定义：
```php
$supportedLocales = ['en', 'zh_CN', 'vi'];
```

## 使用方法

### 在 Blade 视图中使用
```php
<!-- 使用 x-lang 组件 -->
<x-lang key="messages.sales.create.customer_info"/>

<!-- 使用 __() 函数 -->
{{ __('messages.sales.create.customer_name') }}

<!-- 在 Alpine.js 中使用 -->
x-text="'{{ __('messages.sales.create.sales_price') }}' + parseFloat(product.price).toFixed(2)"
```

### 在控制器中使用
```php
use App\Services\LanguageManager;

class SalesController extends Controller
{
    public function index()
    {
        $languageManager = app(LanguageManager::class);
        $title = $languageManager->getTranslation('messages.sales.index.title');
        
        return view('sales.index', compact('title'));
    }
}
```

## 最佳实践

1. **翻译键命名**: 使用点号分隔的层次结构，如 `sales.create.customer_info`
2. **翻译文件组织**: 按功能模块组织翻译键
3. **一致性**: 确保所有语言文件包含相同的翻译键
4. **测试**: 定期测试所有语言的翻译是否正确

## 扩展

如需添加新语言：

1. 在 `resources/lang/` 下创建新的语言文件夹
2. 复制现有语言文件并翻译内容
3. 在 `LanguageController` 中添加新语言代码
4. 在语言切换组件中添加新语言选项

## 注意事项

1. 翻译文件修改后需要清除缓存: `php artisan cache:clear`
2. 确保所有硬编码的文本都使用翻译函数
3. 定期更新翻译内容以保持一致性
4. 在 Alpine.js 中使用翻译时，注意字符串拼接的语法

## 更新记录

### 2024年12月 - 多语言功能完善
- **问题**: 销售创建页面和其他多个页面的中文和越南语翻译显示为键值而不是翻译文本
- **原因**: 翻译文件结构被错误地改为扁平结构，导致翻译键无法正确匹配
- **解决方案**: 
  1. 修复了中文和越南语翻译文件的结构，恢复为正确的嵌套数组结构
  2. 补充了缺失的翻译键，包括：
     - 分类管理 (Categories)
     - 退货管理 (Returns) 
     - 销售统计 (Statistics)
     - 仓库管理 (Stores)
     - 用户管理 (Users)
     - 系统配置 (System Config)
  3. 确保所有三种语言（中文、英文、越南语）的翻译键完整一致
  4. 清除了翻译缓存以确保更改生效
- **影响范围**: 所有页面的多语言显示现在都能正常工作
- **测试建议**: 访问各个页面并切换语言，验证翻译是否正确显示

### 2024年12月 - 补充详细翻译键
- **问题**: `/stores`、`/users`、`/system-config` 等页面的创建、编辑、详情页面还有硬编码的中文文本
- **解决方案**: 
  1. 补充了仓库管理页面的详细翻译键：
     - 创建和编辑页面的表单字段
     - 创建说明和帮助文本
     - 仓库信息卡片
     - 快捷操作按钮
  2. 补充了用户管理页面的详细翻译键：
     - 创建和编辑页面的表单字段
     - 用户详情页面的信息显示
     - 状态和角色相关文本
  3. 补充了系统配置页面的详细翻译键：
     - 保存按钮和状态消息
  4. 同时更新了中文和越南语翻译文件
- **影响范围**: 所有页面的表单、详情、操作按钮等现在都支持多语言
- **测试建议**: 访问各个页面的创建、编辑、详情功能，验证所有文本都能正确翻译