# 产品销售趋势分析功能开发文档

## 功能概述

产品销售趋势分析是一个智能数据分析功能，帮助管理者深入了解各个**标准商品**的销售表现，通过趋势图表和预测算法，优化库存管理和销售策略。

> **注意**：本功能仅分析标准商品（`type = 'standard'`），不包含盲袋商品的销售数据。

## 核心特性

### 📊 数据分析维度
- **产品销售排行**：按销量、金额、趋势等多维度排序
- **时间趋势分析**：每日销售量变化趋势
- **销售频率统计**：产品在时间周期内的活跃天数比例
- **同比趋势计算**：与上一周期的销售数据对比
- **平均日销量**：智能计算每日平均销售数量

### 🔮 智能预测功能
- **线性回归预测**：基于历史数据预测未来7天销量
- **移动平均算法**：平滑数据波动，提供稳定预测基线
- **季节性调整**：考虑周末效应等季节性因素
- **置信度计算**：预测结果的可信度评估

### 📈 可视化图表
- **每日趋势图**：Chart.js 实现的交互式线性图表
- **销售预测图**：实际数据与预测数据对比展示
- **产品详情图**：点击产品可查看详细销售趋势
- **响应式设计**：支持移动端和桌面端访问

## 技术架构

### 后端架构

#### 控制器结构
```php
ProductSalesTrendController
├── index()           # 主页面数据获取
├── export()          # 数据导出功能
├── getProductDetailTrend() # 单个产品详情API
├── buildBaseSalesQuery()   # 构建基础查询
├── getProductSalesTrendData() # 获取趋势数据
├── getDailySalesTrend()    # 获取每日趋势
├── generateSalesPrediction() # 生成预测数据
├── calculateTrend()        # 计算趋势比较
├── calculateMovingAverage() # 移动平均算法
├── calculateTrendSlope()   # 趋势斜率计算
└── getProductsForFilter()  # 筛选产品列表
```

#### 数据查询优化
- **联表查询**：使用 `JOIN` 优化多表关联查询
- **索引利用**：基于创建时间、产品ID、仓库ID的复合索引
- **分页支持**：支持大数据量的分页处理
- **权限过滤**：根据用户权限自动过滤可访问的仓库数据

### 前端架构

#### 技术栈
- **Alpine.js**：响应式数据绑定和交互控制
- **Chart.js**：专业图表库，支持多种图表类型
- **Tailwind CSS**：现代化样式框架
- **Blade模板**：Laravel模板引擎

#### 组件结构
```javascript
productSalesTrend() {
    // 数据管理
    data: {
        trendData,      // 产品趋势数据
        dailyTrend,     // 每日趋势数据
        predictionData  // 预测数据
    },
    
    // 交互控制
    methods: {
        initCharts(),           // 初始化图表
        exportData(),           // 导出数据
        viewProductDetail(),    // 查看产品详情
        setQuickDate(),         // 快速日期选择
        refreshData()           // 刷新数据
    }
}
```

## 核心算法

### 1. 趋势计算算法
```php
/**
 * 计算产品销售趋势（同比增长率）
 */
function calculateTrend($productId, $startDate, $endDate) {
    // 当前周期销量
    $currentSales = getCurrentPeriodSales($productId, $startDate, $endDate);
    
    // 上一周期销量（相同天数）
    $previousSales = getPreviousPeriodSales($productId, $startDate, $endDate);
    
    // 计算增长率
    if ($previousSales == 0) {
        return $currentSales > 0 ? 100 : 0; // 新产品
    }
    
    return round((($currentSales - $previousSales) / $previousSales) * 100, 1);
}
```

### 2. 销售预测算法
```php
/**
 * 线性回归预测算法
 */
function generateSalesPrediction($historicalData) {
    // 计算7日移动平均
    $movingAvg = calculateMovingAverage($data, 7);
    
    // 计算趋势斜率
    $slope = calculateTrendSlope($data);
    
    // 预测未来7天
    for ($i = 1; $i <= 7; $i++) {
        $baseValue = end($movingAvg);
        $trendValue = $baseValue + ($slope * $i);
        
        // 季节性调整（周末效应）
        $seasonalFactor = isWeekend($i) ? 0.8 : 1.1;
        
        $prediction = max(0, round($trendValue * $seasonalFactor));
        
        $predictions[] = [
            'date' => getFutureDate($i),
            'predicted_quantity' => $prediction,
            'confidence' => max(60, 100 - ($i * 5)) // 置信度递减
        ];
    }
    
    return $predictions;
}
```

### 3. 移动平均算法
```php
/**
 * 简单移动平均算法
 */
function calculateMovingAverage($data, $period) {
    $result = [];
    for ($i = $period - 1; $i < count($data); $i++) {
        $sum = array_sum(array_slice($data, $i - $period + 1, $period));
        $result[] = $sum / $period;
    }
    return $result;
}
```

## 数据结构

### 主要数据表
- **sales** - 销售记录主表
- **sale_details** - 标品销售明细表
- **products** - 产品信息表
- **stores** - 仓库信息表

### 关键字段
```sql
-- 产品销售统计视图
SELECT 
    products.id as product_id,
    products.name as product_name,
    products.code as product_code,
    SUM(sale_details.quantity) as total_quantity,
    SUM(sale_details.total) as total_amount,
    COUNT(DISTINCT sales.id) as order_count,
    AVG(sale_details.price) as avg_price,
    COUNT(DISTINCT DATE(sales.created_at)) as active_days
FROM sale_details
JOIN sales ON sale_details.sale_id = sales.id
JOIN products ON sale_details.product_id = products.id
WHERE sales.created_at BETWEEN ? AND ?
GROUP BY products.id, products.name, products.code
ORDER BY total_quantity DESC
```

## 功能模块

### 1. 筛选器模块
- **日期范围选择**：支持自定义日期区间
- **快速日期**：今天、最近7天、最近30天
- **仓库筛选**：超级管理员可选择仓库，普通用户显示当前仓库
- **产品筛选**：按产品名称筛选
- **显示数量**：前20/50/100名产品排行

### 2. 统计卡片模块
- **总销售量**：所有产品销售数量总和
- **总销售额**：所有产品销售金额总和
- **平均日销量**：时间周期内的日均销售量
- **热销产品数**：有销售记录的产品种类数

### 3. 图表模块
- **每日趋势图**：可切换显示数量或金额
- **销售预测图**：历史数据+未来预测
- **产品详情图**：弹窗显示单个产品30天趋势

### 4. 数据表格模块
- **产品排行表**：支持按销量、金额、趋势排序
- **趋势指标**：增长率显示（绿色上升、红色下降）
- **销售频率**：进度条显示活跃天数比例
- **产品详情**：点击查看详细趋势图

## 权限控制

### 访问权限
- **权限要求**：`canViewReports()` 权限
- **角色支持**：超级管理员、管理员、库存管理员、销售人员
- **数据隔离**：非超级管理员只能查看分配仓库的数据

### 数据权限
```php
// 权限控制逻辑
if (!$user->isSuperAdmin()) {
    $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
    $query->whereIn('sales.store_id', $userStoreIds);
}
```

## 导出功能

### CSV导出
- **文件格式**：UTF-8编码的CSV文件
- **文件命名**：`product_sales_trend_YYYY-MM-DD_HH-mm-ss.csv`
- **导出字段**：产品ID、名称、编码、总销量、总金额、订单数、平均单价、平均日销量、销售频率、趋势

### 导出示例
```csv
产品ID,产品名称,产品编码,总销量,总金额,订单数,平均单价,平均日销量,销售频率(%),趋势(%)
1,"盲袋娃娃A","BB001",156,"3120.00",45,"20.00",5.2,65.5%,+12.3%
2,"标品玩具B","ST002",98,"2940.00",32,"30.00",3.3,43.3%,-5.7%
```

## API接口

### 主要路由
```php
// 统计分析页面
GET /statistics/product-sales-trend

// 数据导出
GET /statistics/product-sales-trend/export

// 产品详情API
GET /statistics/product-sales-trend/detail?product_id={id}&days={days}
```

### API响应格式
```json
{
    "date": "2024-01-15",
    "quantity": 12,
    "amount": 360.00
}
```

## 性能优化

### 数据库优化
1. **索引策略**
   ```sql
   -- 销售记录创建时间索引
   INDEX idx_sales_created_at (created_at)
   
   -- 销售详情复合索引
   INDEX idx_sale_details_product_sale (product_id, sale_id)
   
   -- 产品状态索引
   INDEX idx_products_active (is_active)
   ```

2. **查询优化**
   - 使用 `JOIN` 替代 `N+1` 查询
   - 按需加载关联数据
   - 合理使用 `SELECT` 字段限制

### 前端优化
1. **图表性能**
   - 数据点数量限制（最多1000个点）
   - Canvas渲染优化
   - 响应式图表尺寸

2. **交互优化**
   - 防抖处理筛选操作
   - 异步加载图表数据
   - 优雅的加载状态

## 多语言支持

### 翻译文件结构
```php
// resources/lang/zh_CN/navigation.php
'product_sales_trend' => '产品销售趋势',

// resources/lang/en/navigation.php  
'product_sales_trend' => 'Product Sales Trend',

// resources/lang/vi/navigation.php
'product_sales_trend' => 'Xu hướng bán hàng',
```

## 使用说明

### 1. 访问入口
- **导航路径**：数据统计 → 产品销售趋势
- **URL**：`/statistics/product-sales-trend`

### 2. 基本操作
1. **筛选数据**：设置日期范围、仓库、产品等筛选条件
2. **查看趋势**：观察每日销售趋势图和预测曲线
3. **分析排行**：查看产品销售排行表格
4. **详细分析**：点击产品名称查看详细趋势
5. **导出数据**：点击导出按钮下载CSV格式数据

### 3. 数据解读
- **趋势指标**：正值表示增长，负值表示下降
- **销售频率**：活跃天数占总天数的比例
- **预测置信度**：越近期的预测越准确
- **平均日销量**：帮助评估产品的稳定销售表现

## 开发规范

### 代码规范
1. **命名规范**：遵循PSR-4标准
2. **注释规范**：所有方法必须有完整的文档注释
3. **错误处理**：合理的异常处理和用户友好的错误提示

### 测试要求
1. **功能测试**：覆盖所有主要功能点
2. **权限测试**：验证各角色的访问权限
3. **性能测试**：大数据量下的响应时间测试
4. **兼容性测试**：多浏览器和移动端兼容性

## 未来扩展

### 计划功能
1. **高级预测算法**：引入机器学习算法提升预测准确性
2. **销售预警**：自动识别销售异常并发送通知
3. **库存优化建议**：基于销售趋势给出库存调整建议
4. **竞品分析**：同类产品的销售对比分析
5. **季节性分析**：识别产品的季节性销售规律

### 技术改进
1. **实时数据**：WebSocket实现实时数据更新
2. **缓存优化**：Redis缓存提升查询性能
3. **分布式部署**：支持微服务架构
4. **数据可视化**：更丰富的图表类型和交互效果

## 总结

产品销售趋势分析功能是一个综合性的数据分析工具，通过智能算法和可视化图表，为商业决策提供数据支持。该功能具有完善的权限控制、多语言支持和导出功能，能够满足不同角色用户的使用需求。

通过持续的数据积累和算法优化，该功能将为企业的库存管理、销售策略制定和业务发展提供越来越精准的指导。 