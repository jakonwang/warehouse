<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // 只对需要管理权限的方法加 canManageStores
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canManageStores()) {
                abort(403, '您没有权限管理仓库');
            }
            return $next($request);
        })->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy', 'users', 'addUser', 'removeUser']);
    }
    /**
     * 显示仓库列表
     */
    public function index()
    {
        $stores = Store::withCount('users')->paginate(10);
        return view('stores.index', compact('stores'));
    }

    /**
     * 显示仓库详情
     */
    public function show(Store $store)
    {
        $store->load(['availableProducts', 'users']);
        return view('stores.show', compact('store'));
    }

    /**
     * 显示创建仓库表单
     */
    public function create()
    {
        return view('stores.create');
    }

    /**
     * 保存新仓库
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stores',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        Store::create($validated);

        return redirect()->route('stores.index')
            ->with('success', '仓库创建成功！');
    }

    /**
     * 显示编辑仓库表单
     */
    public function edit(Store $store)
    {
        return view('stores.edit', compact('store'));
    }

    /**
     * 更新仓库信息
     */
    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stores,code,' . $store->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $store->update($validated);

        return redirect()->route('stores.index')
            ->with('success', '仓库更新成功！');
    }

    /**
     * 删除仓库
     */
    public function destroy(Store $store)
    {
        try {
            DB::beginTransaction();

            // 检查是否有关联数据
            if ($store->inventories()->exists() || 
                $store->stockIns()->exists() || 
                $store->returns()->exists() || 
                $store->inventoryChecks()->exists()) {
                throw new \Exception('该仓库存在关联数据，无法删除！');
            }

            $store->delete();

            DB::commit();

            return redirect()->route('stores.index')
                ->with('success', '仓库删除成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 显示仓库用户管理页面
     */
    public function users(Store $store)
    {
        $users = $store->users()->paginate(10);
        $availableUsers = \App\Models\User::whereDoesntHave('stores', function ($query) use ($store) {
            $query->where('stores.id', $store->id);
        })->get();

        return view('stores.users', compact('store', 'users', 'availableUsers'));
    }

    /**
     * 添加用户到仓库
     */
    public function addUser(Request $request, Store $store)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $store->users()->attach($validated['user_id']);

        return back()->with('success', '用户添加成功！');
    }

    /**
     * 从仓库移除用户
     */
    public function removeUser(Request $request, Store $store)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $store->users()->detach($validated['user_id']);

        return back()->with('success', '用户移除成功！');
    }

    /**
     * 获取仓库可销售的商品（API）
     */
    public function getProducts(Store $store)
    {
        // 检查用户是否有权限访问该仓库
        if (!auth()->user()->canAccessStore($store->id)) {
            return response()->json(['success' => false, 'message' => '无权限访问该仓库'], 403);
        }

        $standardProducts = $store->availableStandardProducts()->get();
        $blindBagProducts = $store->availableBlindBagProducts()->get();

        return response()->json([
            'success' => true,
            'standard_products' => $standardProducts,
            'blind_bag_products' => $blindBagProducts
        ]);
    }

    /**
     * 切换当前仓库
     */
    public function switchStore($storeId)
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            // 超级管理员可以切换到全部（0）或任意仓库
            if ($storeId == 0) {
                session(['current_store_id' => null]);
            } else {
                session(['current_store_id' => $storeId]);
            }
        } else {
            // 普通用户只能切换到分配的仓库
            if (!$user->stores->contains($storeId)) {
                abort(403, '无权切换到该仓库');
            }
            session(['current_store_id' => $storeId]);
        }
        return back();
    }
} 