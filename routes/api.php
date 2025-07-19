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

// 获取仓库商品数据
Route::get('stores/{store}/products', function (App\Models\Store $store) {
    // 获取该仓库分配的商品
    $products = DB::table('store_products')
        ->join('products', 'store_products.product_id', '=', 'products.id')
        ->select(
            'products.id',
            'products.name',
            'products.code',
            'products.price',
            'products.cost_price',
            'products.type',
            'products.image',
            'store_products.is_active',
            'store_products.sort_order'
        )
        ->where('store_products.store_id', $store->id)
        ->where('store_products.is_active', true)
        ->where('products.is_active', true)
        ->orderBy('store_products.sort_order')
        ->orderBy('products.sort_order')
        ->get();
    
    // 分离标准商品和盲袋商品
    $standardProducts = $products->where('type', 'standard')->values();
    $blindBagProducts = $products->where('type', 'blind_bag')->values();
    
    return response()->json([
        'success' => true,
        'standard_products' => $standardProducts,
        'blind_bag_products' => $blindBagProducts
    ]);
}); 