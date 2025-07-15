<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\StoreProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreProductController extends Controller
{
    /**
     * 显示仓库商品分配管理页面
     */
    public function index()
    {
        $stores = Store::withCount(['storeProducts', 'availableProducts'])->get();
        $products = Product::withCount('availableStores')->get();
        
        return view('store-products.index', compact('stores', 'products'));
    }

    /**
     * 显示仓库的商品分配详情
     */
    public function show(Store $store)
    {
        $store->load(['storeProducts.product', 'availableProducts']);
        
        // 获取所有可分配的商品
        $allProducts = Product::where('is_active', true)
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get();
        
        return view('store-products.show', compact('store', 'allProducts'));
    }

    /**
     * 为仓库分配商品
     */
    public function assign(Request $request, Store $store)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        try {
            DB::beginTransaction();

            // 只添加新分配，不删除原有
            foreach ($request->product_ids as $productId) {
                StoreProduct::firstOrCreate([
                    'store_id' => $store->id,
                    'product_id' => $productId,
                ], [
                    'is_active' => true,
                    'sort_order' => 0
                ]);
            }

            DB::commit();

            return back()->with('success', '商品分配成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '商品分配失败：' . $e->getMessage());
        }
    }

    /**
     * 批量分配商品到多个仓库
     */
    public function batchAssign(Request $request)
    {
        $request->validate([
            'store_ids' => 'required|array',
            'store_ids.*' => 'exists:stores,id',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->store_ids as $storeId) {
                $store = Store::find($storeId);
                
                // 为每个仓库分配商品
                foreach ($request->product_ids as $productId) {
                    StoreProduct::firstOrCreate([
                        'store_id' => $storeId,
                        'product_id' => $productId
                    ], [
                        'is_active' => true,
                        'sort_order' => 0
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', '批量分配成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '批量分配失败：' . $e->getMessage());
        }
    }

    /**
     * 移除仓库的商品分配
     */
    public function remove(Request $request, Store $store)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        try {
            $store->storeProducts()
                ->whereIn('product_id', $request->product_ids)
                ->delete();

            return back()->with('success', '商品移除成功！');
        } catch (\Exception $e) {
            return back()->with('error', '商品移除失败：' . $e->getMessage());
        }
    }

    /**
     * 更新商品分配状态
     */
    public function updateStatus(Request $request, StoreProduct $storeProduct)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $storeProduct->update([
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => '状态更新成功'
        ]);
    }

    /**
     * 更新商品分配排序
     */
    public function updateSort(Request $request, Store $store)
    {
        $request->validate([
            'sort_data' => 'required|array',
            'sort_data.*.id' => 'required|exists:store_products,id',
            'sort_data.*.sort_order' => 'required|integer|min:0'
        ]);

        try {
            foreach ($request->sort_data as $item) {
                StoreProduct::where('id', $item['id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => '排序更新成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '排序更新失败：' . $e->getMessage()
            ], 500);
        }
    }
} 