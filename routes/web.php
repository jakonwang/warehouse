<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryCheckController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\StoreProductController;
use App\Http\Controllers\PriceSeriesController;
use App\Http\Controllers\SystemConfigController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SystemMonitorController;
use App\Http\Controllers\Mobile\BlindBagSaleController;

// 多语言测试路由
Route::get('/test-sales-language', function () {
    return view('test-sales-language');
})->name('test.sales.language');

// 多语言测试路由
Route::get('/test-sales-create-language', function () {
    return view('test-sales-create-language');
})->name('test.sales.create.language');

// 测试导出路由
Route::get('/test-export', function () {
    return response()->json(['message' => 'Export route is working']);
})->name('test.export');

// Debugbar 测试路由
Route::get('/debug-test', function () {
    // 模拟一些数据库查询来测试 Debugbar
    $users = \App\Models\User::take(5)->get();
    $products = \App\Models\Product::take(3)->get();
    
    return view('debug-test', compact('users', 'products'));
})->name('debug.test');

// 默认路由重定向到移动端登录
Route::get('/', function () {
    return redirect()->route('mobile.login');
});

// 移动端登录路由（默认入口）
Route::get('/login', [AuthController::class, 'showMobileLoginForm'])->name('mobile.login');
Route::post('/login', [AuthController::class, 'login']);

// 后台管理登录路由
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 应用移动端重定向中间件
Route::middleware(['mobile.redirect'])->group(function () {
    Route::get('/admin', function () {
        return redirect()->route('dashboard');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 商品管理路由
    Route::resource('products', ProductController::class);

    // 库存管理相关路由
    Route::prefix('inventory')->group(function () {
        Route::get('alerts', [InventoryController::class, 'alerts'])->name('inventory.alerts');
        Route::get('check', [InventoryController::class, 'check'])->name('inventory.check');
        Route::post('check', [InventoryController::class, 'updateCheck'])->name('inventory.update-check');
        Route::get('check-history', [InventoryController::class, 'checkHistory'])->name('inventory.check-history');
        Route::get('low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
        Route::get('create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/', [InventoryController::class, 'store'])->name('inventory.store');
        Route::post('batch-operation', [InventoryController::class, 'batchOperation'])->name('inventory.batch-operation');
        Route::post('{inventory}/check', [InventoryController::class, 'singleCheck'])->name('inventory.single-check');
        Route::get('{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::get('{inventory}', [InventoryController::class, 'show'])->name('inventory.show');
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/export', [InventoryController::class, 'export'])->name('inventory.export');
    });

    // Sales Management Routes
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    Route::post('/sales/calculate', [SaleController::class, 'calculate'])->name('sales.calculate');

    // User Management Routes
    Route::resource('users', UserController::class);

    // 系统配置
    Route::prefix('system-config')->group(function () {
        Route::get('/', [SystemConfigController::class, 'index'])->name('system-config.index');
        Route::put('/', [SystemConfigController::class, 'update'])->name('system-config.update');
    });

    // 商品管理API接口
    Route::prefix('api/products')->name('api.products.')->group(function () {
        Route::get('/standard', [ProductController::class, 'getStandardProducts'])->name('standard');
        Route::get('/blind-bag', [ProductController::class, 'getBlindBagProducts'])->name('blind-bag');
    });

    // 仓库商品API接口
    Route::prefix('api/stores')->name('api.stores.')->group(function () {
        Route::get('/{store}/products', [StoreController::class, 'getProducts'])->name('products');
    });

    // 仓库管理
    Route::resource('stores', StoreController::class);
    Route::get('/switch-store/{store}', [StoreController::class, 'switchStore'])->name('switch-store');
    Route::get('stores/{store}/users', [StoreController::class, 'users'])->name('stores.users');
    Route::post('stores/{store}/users', [StoreController::class, 'addUser'])->name('stores.add-user');
    Route::delete('stores/{store}/users', [StoreController::class, 'removeUser'])->name('stores.remove-user');

    // 仓库商品分配管理
    Route::prefix('store-products')->name('store-products.')->group(function () {
        Route::get('/', [StoreProductController::class, 'index'])->name('index');
        Route::get('/{store}', [StoreProductController::class, 'show'])->name('show');
        Route::post('/{store}/assign', [StoreProductController::class, 'assign'])->name('assign');
        Route::post('/batch-assign', [StoreProductController::class, 'batchAssign'])->name('batch-assign');
        Route::delete('/{store}/remove', [StoreProductController::class, 'remove'])->name('remove');
        Route::put('/{storeProduct}/status', [StoreProductController::class, 'updateStatus'])->name('update-status');
        Route::put('/{store}/sort', [StoreProductController::class, 'updateSort'])->name('update-sort');
    });

    // 入库管理
    Route::get('/stock-ins', [StockInController::class, 'index'])->name('stock-ins.index');
    Route::get('/stock-ins/create', [StockInController::class, 'create'])->name('stock-ins.create');
    Route::post('/stock-ins', [StockInController::class, 'store'])->name('stock-ins.store');
    Route::get('/stock-ins/{stockInRecord}', [StockInController::class, 'show'])->name('stock-ins.show');
    Route::delete('/stock-ins/{stockInRecord}', [StockInController::class, 'destroy'])->name('stock-ins.destroy');

    // 退货管理
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/create', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
    Route::get('/returns/{id}', [ReturnController::class, 'show'])->name('returns.show');
    Route::get('/returns/{id}/edit', [ReturnController::class, 'edit'])->name('returns.edit');
    Route::put('/returns/{id}', [ReturnController::class, 'update'])->name('returns.update');
    Route::delete('/returns/{id}', [ReturnController::class, 'destroy'])->name('returns.destroy');

    // Category Management Routes
    Route::resource('categories', CategoryController::class);
    Route::post('categories/update-order', [CategoryController::class, 'updateOrder'])->name('categories.update-order');
    Route::get('categories/{category}/products', [CategoryController::class, 'getProducts'])->name('categories.products');

    // 出库管理
    Route::resource('stock-outs', StockOutController::class);

    // 盘点管理
    Route::resource('inventory-check', InventoryCheckController::class);
    Route::post('inventory-check/{inventoryCheckRecord}/confirm', [InventoryCheckController::class, 'confirm'])->name('inventory-check.confirm');

    // 价格系列管理
    Route::resource('price-series', PriceSeriesController::class);

    // 盲袋销售管理
    Route::resource('blind-bag-sales', BlindBagSaleController::class);

    // 报表和统计
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
    });

    Route::post('/inventory/{inventory}/quick-adjust', [App\Http\Controllers\InventoryController::class, 'quickAdjust'])->name('inventory.quick_adjust'); 

    // 仓库调拨管理
    Route::prefix('store-transfers')->name('store-transfers.')->group(function () {
        Route::get('/', [\App\Http\Controllers\StoreTransferController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\StoreTransferController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\StoreTransferController::class, 'store'])->name('store');
        Route::get('/{storeTransfer}', [\App\Http\Controllers\StoreTransferController::class, 'show'])->name('show');
        Route::post('/{storeTransfer}/approve', [\App\Http\Controllers\StoreTransferController::class, 'approve'])->name('approve');
        Route::post('/{storeTransfer}/reject', [\App\Http\Controllers\StoreTransferController::class, 'reject'])->name('reject');
        Route::post('/{storeTransfer}/complete', [\App\Http\Controllers\StoreTransferController::class, 'complete'])->name('complete');
        Route::post('/{storeTransfer}/cancel', [\App\Http\Controllers\StoreTransferController::class, 'cancel'])->name('cancel');
        Route::delete('/{storeTransfer}', [\App\Http\Controllers\StoreTransferController::class, 'destroy'])->name('destroy');
        
        // API接口
        Route::get('/available-products', [\App\Http\Controllers\StoreTransferController::class, 'getAvailableProducts'])->name('available-products');
        Route::get('/product-stock', [\App\Http\Controllers\StoreTransferController::class, 'getProductStock'])->name('product-stock');
    });

    // 库存周转率统计
    Route::get('statistics/inventory-turnover', [\App\Http\Controllers\InventoryTurnoverController::class, 'index'])->name('statistics.inventory-turnover'); 

    // 销售统计
    Route::prefix('statistics')->name('statistics.')->group(function () {
        Route::get('/sales', [\App\Http\Controllers\SaleStatisticsController::class, 'sales'])->name('sales');
        Route::get('/sales/export', [\App\Http\Controllers\SaleStatisticsController::class, 'export'])->name('sales.export');
        Route::get('/sales/test', function() {
            return response()->json(['message' => 'Statistics sales route is working']);
        })->name('sales.test');
        Route::get('/blind-bag-cost-test', [\App\Http\Controllers\SaleStatisticsController::class, 'testBlindBagCost'])->name('blind-bag-cost-test');
        Route::get('/health', [\App\Http\Controllers\StatisticsController::class, 'health'])->name('health');
    });

    // 编辑个人资料
    Route::get('profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit'); 
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // 语言切换路由
    Route::get('/language/switch/{language}', [\App\Http\Controllers\LanguageController::class, 'switchLanguage'])->name('language.switch'); 

    // 系统监控
    Route::get('/system-monitor', [SystemMonitorController::class, 'index'])->name('system-monitor.index');
    
    // 数据备份管理
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BackupController::class, 'index'])->name('index');
        Route::post('/database', [\App\Http\Controllers\BackupController::class, 'createDatabaseBackup'])->name('database');
        Route::post('/files', [\App\Http\Controllers\BackupController::class, 'createFileBackup'])->name('files');
        Route::post('/full', [\App\Http\Controllers\BackupController::class, 'createFullBackup'])->name('full');
        Route::get('/download/{filename}', [\App\Http\Controllers\BackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('destroy');
        Route::post('/restore/{filename}', [\App\Http\Controllers\BackupController::class, 'restoreDatabase'])->name('restore');
    });
});



// 操作日志管理（仅超级管理员）
Route::middleware(['auth'])->group(function () {
    Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [\App\Http\Controllers\ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::get('/activity-logs-export', [\App\Http\Controllers\ActivityLogController::class, 'export'])->name('activity-logs.export');
    Route::post('/activity-logs-cleanup', [\App\Http\Controllers\ActivityLogController::class, 'cleanup'])->name('activity-logs.cleanup');
});

// 移动端路由
Route::middleware(['auth'])->prefix('mobile')->name('mobile.')->group(function () {
    // 移动端首页
    Route::get('/', [\App\Http\Controllers\Mobile\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/quick-actions', [\App\Http\Controllers\Mobile\DashboardController::class, 'quickActions'])->name('quick-actions');
    
    // 入库管理
    Route::get('/stock-in', [StockInController::class, 'mobileIndex'])->name('stock-in.index');
    Route::post('/stock-in', [StockInController::class, 'mobileStore'])->name('stock-in.store');
    
    // 退货管理
    Route::get('/returns', [ReturnController::class, 'mobileIndex'])->name('returns.index');
    Route::get('/returns/create', [ReturnController::class, 'mobileCreate'])->name('returns.create');
    Route::post('/returns', [ReturnController::class, 'mobileStore'])->name('returns.store');
    Route::get('/returns/{id}/edit', [ReturnController::class, 'mobileEdit'])->name('returns.edit');
    Route::put('/returns/{id}', [ReturnController::class, 'mobileUpdate'])->name('returns.update');
    Route::delete('/returns/{id}', [ReturnController::class, 'mobileDestroy'])->name('returns.destroy');
    
    // 库存查询
    Route::get('/inventory', [InventoryController::class, 'mobileIndex'])->name('inventory.index');
    
    // 销售记录
    Route::get('/sales', [SaleController::class, 'mobileIndex'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'mobileCreate'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'mobileStore'])->name('sales.store');
    Route::get('/sales/{sale}', [\App\Http\Controllers\Mobile\SaleController::class, 'show'])->name('sales.show');
    Route::delete('/sales/{sale}', [\App\Http\Controllers\Mobile\SaleController::class, 'destroy'])->name('sales.destroy');
    
    // 盲袋销售
    Route::get('/blind-bag/create', [\App\Http\Controllers\Mobile\BlindBagSaleController::class, 'create'])->name('blind-bag.create');
    Route::post('/blind-bag', [\App\Http\Controllers\Mobile\BlindBagSaleController::class, 'store'])->name('blind-bag.store');
    Route::get('/blind-bag/stock', [\App\Http\Controllers\Mobile\BlindBagSaleController::class, 'getProductStock'])->name('blind-bag.stock');
    Route::post('/blind-bag/calculate', [\App\Http\Controllers\Mobile\BlindBagSaleController::class, 'calculateProfit'])->name('blind-bag.calculate');
}); 

 