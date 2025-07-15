<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChartDataController;
use Illuminate\Support\Facades\DB; // Added DB facade import

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 图表数据API
Route::prefix('charts')->group(function () {
    Route::get('sales-trend', [ChartDataController::class, 'salesTrend']);
    Route::get('inventory-distribution', [ChartDataController::class, 'inventoryDistribution']);
    Route::get('return-reasons', [ChartDataController::class, 'returnReasons']);
    Route::get('inventory-check-frequency', [ChartDataController::class, 'inventoryCheckFrequency']);
    Route::get('store-comparison', [ChartDataController::class, 'storeComparison']);
});

// 获取仓库库存数据
Route::get('stores/{store}/inventory', function (App\Models\Store $store) {
    // 使用 DB 查询替代 Eloquent 关系查询
    $inventories = DB::table('inventory')
        ->leftJoin('products', 'inventory.product_id', '=', 'products.id')
        ->select(
            'inventory.product_id',
            'products.code as product_code',
            'products.name as product_name',
            'inventory.quantity'
        )
        ->where('inventory.store_id', $store->id)
        ->get();
    
    return $inventories->map(function ($inventory) {
        return [
            'product_id' => $inventory->product_id,
            'product_code' => $inventory->product_code,
            'product_name' => $inventory->product_name,
            'quantity' => $inventory->quantity,
        ];
    });
}); 