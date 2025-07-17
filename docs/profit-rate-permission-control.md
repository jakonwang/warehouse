# 利润率权限控制功能实现文档

## 功能概述

为了保护敏感的商业财务信息，系统实现了利润率权限控制功能。只有超级管理员可以查看利润率、成本价等敏感财务信息，其他角色用户无法看到这些信息。

## 权限控制范围

### 1. 仪表盘页面
- **位置**: `resources/views/dashboard.blade.php` 和 `resources/views/dashboard-optimized.blade.php`
- **控制内容**: 平均利润率卡片
- **权限检查**: `@if(auth()->user()->canViewProfitAndCost())`

### 2. 销售管理页面
- **销售列表** (`resources/views/sales/index.blade.php`)
  - 平均利润率统计卡片
  - 表格中的利润列和利润率列
  - 空数据时的列数调整

- **销售详情** (`resources/views/sales/show.blade.php`)
  - 销售成本信息
  - 销售利润信息
  - 利润率信息

- **销售创建** (`resources/views/sales/create.blade.php`)
  - 标品销售统计中的成本、利润、利润率
  - 盲袋销售统计中的成本、利润、利润率

### 3. 销售统计页面
- **位置**: `resources/views/statistics/sales.blade.php`
- **控制内容**: 
  - 利润率统计卡片
  - 表格中的成本、利润、利润率列
  - 空数据时的列数调整

### 4. 商品管理页面
- **商品列表** (`resources/views/products/index.blade.php`)
  - 商品卡片中的利润率显示
  - 表格中的利润率列

- **商品详情** (`resources/views/products/show.blade.php`)
  - 销售概览中的总利润和平均利润率
  - 价格信息中的成本价和利润率

### 5. 价格系列页面
- **位置**: `resources/views/price-series/index.blade.php`
- **控制内容**: 利润率、利润金额显示

### 6. 移动端页面
移动端页面已经正确实现了权限控制：
- `resources/views/mobile/sales/create.blade.php`
- `resources/views/mobile/sales/show.blade.php`
- `resources/views/mobile/sales/index.blade.php`

## 技术实现

### 1. 权限检查方法
在 `app/Models/User.php` 中已经定义了权限检查方法：

```php
/**
 * 检查用户是否可以查看利润和成本信息
 * 只有超级管理员可以查看
 */
public function canViewProfitAndCost(): bool
{
    return $this->isSuperAdmin();
}
```

### 2. 超级管理员判断
```php
/**
 * 判断用户是否为超级管理员
 */
public function isSuperAdmin(): bool
{
    // 检查用户名是否为admin，或者角色代码是否为super_admin
    return $this->username === 'admin' || ($this->role && $this->role->code === 'super_admin');
}
```

### 3. 视图中的权限检查
在所有相关视图中使用以下模式：

```blade
@if(auth()->user()->canViewProfitAndCost())
    <!-- 利润率相关显示内容 -->
@endif
```

## 用户体验

### 1. 非超级管理员用户
- 无法看到任何利润率信息
- 无法看到成本价信息
- 无法看到利润金额信息
- 表格中相关列被隐藏
- 统计卡片中相关指标被隐藏

### 2. 超级管理员用户
- 可以看到所有财务信息
- 包括利润率、成本价、利润金额等
- 完整的财务分析功能

## 安全性考虑

1. **前端隐藏**: 通过Blade模板条件渲染隐藏敏感信息
2. **后端验证**: 在控制器层面也进行权限验证
3. **数据隔离**: 确保非授权用户无法通过API获取敏感数据

## 维护说明

### 添加新的利润率显示位置
1. 在视图中添加权限检查：`@if(auth()->user()->canViewProfitAndCost())`
2. 确保表格列数在权限检查后正确调整
3. 测试不同角色用户的访问效果

### 修改权限逻辑
1. 修改 `User.php` 中的 `canViewProfitAndCost()` 方法
2. 更新相关文档
3. 测试所有相关页面的显示效果

## 测试建议

1. **超级管理员测试**：
   - 登录超级管理员账户
   - 访问所有相关页面
   - 确认可以看到所有财务信息

2. **普通用户测试**：
   - 登录其他角色账户
   - 访问所有相关页面
   - 确认无法看到财务信息

3. **边界测试**：
   - 测试未登录用户访问
   - 测试权限变更后的效果
   - 测试表格列数调整的正确性

## 相关文件列表

### 修改的视图文件
- `resources/views/dashboard.blade.php`
- `resources/views/dashboard-optimized.blade.php`
- `resources/views/sales/index.blade.php`
- `resources/views/sales/show.blade.php`
- `resources/views/sales/create.blade.php`
- `resources/views/statistics/sales.blade.php`
- `resources/views/products/index.blade.php`
- `resources/views/products/show.blade.php`
- `resources/views/price-series/index.blade.php`

### 相关模型文件
- `app/Models/User.php` (已存在权限检查方法)

### 文档文件
- `requirements.md` (已更新权限矩阵)
- `docs/profit-rate-permission-control.md` (本文档) 