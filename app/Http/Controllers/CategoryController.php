<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * 显示分类列表
     */
    public function index()
    {
        try {
            // 获取分页数据
            $categories = DB::table('categories as c1')
                ->leftJoin('categories as parent', 'parent.id', '=', 'c1.parent_id')
                ->select(
                    'c1.id', 'c1.name', 'c1.description', 'c1.parent_id', 'c1.sort_order', 'c1.is_active',
                    'parent.name as parent_name'
                )
                ->whereNull('c1.parent_id')
                ->orderBy('c1.sort_order')
                ->paginate(15);
            
            // 单独获取统计数据
            $totalCategories = DB::table('categories')->whereNull('parent_id')->count();
            $activeCategories = DB::table('categories')->whereNull('parent_id')->where('is_active', true)->count();
            
            // 为模态框获取所有分类列表（不分页）
            $allCategories = DB::table('categories')
                ->select('id', 'name')
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();
            
            return view('categories.index', compact('categories', 'totalCategories', 'activeCategories', 'allCategories'));
        } catch (\Exception $e) {
            \Log::error('CategoryController index method failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'CategoryController failed: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * 显示创建分类表单
     */
    public function create()
    {
        // 只查一级分类，避免全表 get
        $categories = DB::table('categories')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return view('categories.create', compact('categories'));
    }

    /**
     * 保存新分类
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', '分类创建成功');
    }

    /**
     * 显示编辑分类表单
     */
    public function edit(Category $category)
    {
        // 只查一级分类且排除自己，避免全表 get
        $categories = DB::table('categories')
            ->where('id', '!=', $category->id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return view('categories.edit', compact('category', 'categories'));
    }

    /**
     * 更新分类
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        // 防止将分类设为自己的子分类
        if ($validated['parent_id'] == $category->id) {
            return back()->withErrors(['parent_id' => '不能将分类设为自己的子分类']);
        }

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', '分类更新成功');
    }

    /**
     * 删除分类
     */
    public function destroy(Category $category)
    {
        try {
            // 检查是否有商品使用此分类
            $productsCount = Product::where('category', $category->name)->count();
            
            if ($productsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "无法删除分类 '{$category->name}'，该分类下还有 {$productsCount} 个商品"
                ]);
            }
            
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => '分类删除成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 获取分类下的商品列表
     */
    public function getProducts(Category $category)
    {
        try {
            \Log::info('Getting products for category: ' . $category->name);
            
            $products = Product::where('category', $category->name)
                ->select('id', 'name', 'code', 'price', 'stock_quantity')
                ->get();
            
            \Log::info('Found ' . $products->count() . ' products for category: ' . $category->name);
            
            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting products for category: ' . $category->name . ' - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '获取商品列表失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 更新分类排序
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:categories,id',
            'orders.*.order' => 'required|integer'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['orders'] as $order) {
                Category::where('id', $order['id'])
                    ->update(['sort_order' => $order['order']]);
            }
            DB::commit();
            return response()->json(['message' => '排序更新成功']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => '排序更新失败'], 500);
        }
    }
} 