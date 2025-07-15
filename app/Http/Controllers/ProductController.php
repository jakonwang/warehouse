<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 显示商品列表
     */
    public function index(Request $request)
    {
        // 构建查询
        $query = DB::table('products');

        // 搜索过滤
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // 分类过滤
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // 类型过滤
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 状态过滤
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // 排序
        switch ($request->sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();
        
        // 直接查询分类，不使用缓存
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * 显示创建商品表单
     */
    public function create()
    {
        // 直接查询分类，不使用缓存
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        return view('products.create', compact('categories'));
    }

    /**
     * 保存新商品
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:products',
            'type' => 'required|in:standard,blind_bag',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            DB::beginTransaction();

            // 自动生成商品编码（如果未提供）
            if (empty($validated['code'])) {
                $validated['code'] = $this->generateProductCode();
            }

            // 处理图片上传
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $validated['image'] = $imagePath;
            }

            // 创建商品
            $product = Product::create($validated);

            // 清除相关缓存（暂时注释掉）
            // Cache::forget('active_categories');
            // Cache::forget('dashboard_data_' . auth()->id());

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', '商品创建成功！');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('商品创建失败: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', '商品创建失败，请重试');
        }
    }

    /**
     * 生成商品编码
     */
    private function generateProductCode()
    {
        $prefix = 'P';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        
        return $prefix . $date . $random;
    }

    /**
     * 显示商品详情
     */
    public function show(Product $product)
    {
        // 获取商品库存信息
        $inventoryData = Cache::remember('product_inventory_' . $product->id, 300, function () use ($product) {
            return DB::table('inventory')
                ->leftJoin('stores', 'inventory.store_id', '=', 'stores.id')
                ->select(
                    'inventory.*',
                    'stores.name as store_name'
                )
                ->where('inventory.product_id', $product->id)
                ->get();
        });

        return view('products.show', compact('product', 'inventoryData'));
    }

    /**
     * 显示编辑商品表单
     */
    public function edit(Product $product)
    {
        // 直接查询分类，不使用缓存
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * 更新商品
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code,' . $product->id,
            'type' => 'required|in:standard,blind_bag',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            DB::beginTransaction();

            // 处理图片上传
            if ($request->hasFile('image')) {
                // 删除旧图片
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                
                $imagePath = $request->file('image')->store('products', 'public');
                $validated['image'] = $imagePath;
            }

            // 更新商品
            $product->update($validated);

            // 清除相关缓存（暂时注释掉）
            // Cache::forget('active_categories');
            // Cache::forget('dashboard_data_' . auth()->id());
            // Cache::forget('product_inventory_' . $product->id);

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', '商品更新成功！');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('商品更新失败: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', '商品更新失败，请重试');
        }
    }

    /**
     * 删除商品
     */
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            // 检查商品是否有关联数据
            $hasInventory = DB::table('inventory')->where('product_id', $product->id)->exists();
            $hasSales = DB::table('sale_details')->where('product_id', $product->id)->exists();

            if ($hasInventory || $hasSales) {
                return back()->with('error', '该商品有关联数据，无法删除！');
            }

            // 删除商品图片
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // 删除商品
            $product->delete();

            // 清除相关缓存（暂时注释掉）
            // Cache::forget('active_categories');
            // Cache::forget('dashboard_data_' . auth()->id());

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', '商品删除成功！');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('商品删除失败: ' . $e->getMessage());
            
            return back()->with('error', '商品删除失败，请重试');
        }
    }
} 