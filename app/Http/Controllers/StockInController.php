<?php

namespace App\Http\Controllers;

use App\Models\StockInRecord;
use App\Models\StockInDetail;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StockInController extends Controller
{
    /**
     * 显示入库记录列表
     */
    public function index()
    {
        // 优先用 request('store_id')，否则用 session('current_store_id')
        $storeId = request('store_id', session('current_store_id'));
        $userStoreIds = auth()->user()->getAccessibleStores()->pluck('id')->toArray();
        
        // 使用 Eloquent 模型查询，但优化关系加载
        $query = StockInRecord::with(['user', 'store', 'stockInDetails.product'])
            ->whereIn('store_id', $userStoreIds);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $stockIns = $query->orderBy('created_at', 'desc')->paginate(10);

        $stores = auth()->user()->getAccessibleStores()->where('is_active', true);

        return view('stock-ins.index', compact('stockIns', 'stores'));
    }

    /**
     * 显示入库表单
     */
    public function create()
    {
        // 只显示标准商品，因为入库管理不需要对盲袋商品进行操作
        $products = Product::active()->where('type', 'standard')->get();
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true);
        return view('stock-ins.create', compact('products', 'stores'));
    }

    /**
     * 保存入库记录
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'remark' => 'nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // 校验用户是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($request->store_id)) {
            return back()->withErrors(['store_id' => '无权限操作该仓库'])->withInput();
        }

        // 校验是否有有效的入库数量
        $hasValidQuantity = false;
        foreach ($request->products as $item) {
            if (isset($item['quantity']) && $item['quantity'] > 0) {
                $hasValidQuantity = true;
                break;
            }
        }

        if (!$hasValidQuantity) {
            return back()->withErrors(['products' => '请至少输入一个商品的入库数量'])->withInput();
        }

        try {
            DB::beginTransaction();

            $record = new StockInRecord();
            $record->store_id = $request->store_id;
            $record->supplier = $request->supplier ?? '';
            $record->remark = $request->remark;
            $record->user_id = auth()->id();
            $record->save();

            $totalAmount = 0;
            $totalCost = 0;

            foreach ($request->products as $item) {
                if (isset($item['quantity']) && $item['quantity'] > 0) {
                    $product = Product::find($item['id']);
                    
                    if (!$product) {
                        throw new \Exception("商品 ID {$item['id']} 不存在");
                    }
                    
                    // 检查并处理成本价为null的情况
                    if ($product->cost_price === null) {
                        throw new \Exception("商品「{$product->name}」(ID: {$item['id']}) 的成本价未设置，请先在商品管理中设置成本价");
                    }
                    
                    $costPrice = $product->cost_price ?? 0;
                    
                    // 创建入库明细（使用商品成本价作为入库价格）
                    $detail = new StockInDetail();
                    $detail->stock_in_record_id = $record->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $item['quantity'];
                    $detail->unit_price = $costPrice;  // 使用商品成本价
                    $detail->unit_cost = $costPrice;   // 成本价
                    $detail->total_amount = $item['quantity'] * $costPrice;
                    $detail->total_cost = $item['quantity'] * $costPrice;
                    $detail->save();

                    // 更新库存（多仓库支持）
                    $inventory = Inventory::firstOrNew([
                        'store_id' => $request->store_id, 
                        'product_id' => $item['id']
                    ]);
                    $inventory->quantity = ($inventory->quantity ?? 0) + $item['quantity'];
                    $inventory->save();

                    // 创建库存变动记录
                    if (class_exists('App\Models\InventoryRecord')) {
                        \App\Models\InventoryRecord::create([
                            'inventory_id' => $inventory->id,
                            'quantity' => $item['quantity'],
                            'unit_price' => $costPrice,
                            'total_amount' => $item['quantity'] * $costPrice,
                            'type' => 'in',
                            'reference_type' => 'stock_in',
                            'reference_id' => $record->id,
                            'note' => "入库记录 #{$record->id} - {$product->name}",
                        ]);
                    }

                    $totalAmount += $detail->total_amount;
                    $totalCost += $detail->total_cost;
                }
            }

            $record->total_amount = $totalAmount;
            $record->total_cost = $totalCost;
            $record->save();

            DB::commit();

            return redirect()->route('stock-ins.show', $record)
                ->with('success', '入库记录创建成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '入库记录创建失败：' . $e->getMessage())->withInput();
        }
    }

    /**
     * 显示入库记录详情
     */
    public function show(StockInRecord $stockInRecord)
    {
        $stockInRecord->load(['user', 'store', 'stockInDetails.product']);

        return view('stock-ins.show', compact('stockInRecord'));
    }

    public function destroy(StockInRecord $stockInRecord)
    {
        // 检查权限
        if (!$stockInRecord->canDelete()) {
            return back()->with('error', '无权删除此入库记录');
        }

        try {
            DB::beginTransaction();

            // 恢复库存（多仓库支持）
            foreach ($stockInRecord->stockInDetails as $detail) {
                $inventory = Inventory::where('store_id', $stockInRecord->store_id)
                    ->where('product_id', $detail->product_id)
                    ->first();
                
                if ($inventory) {
                    // 检查库存是否足够回退
                    if ($inventory->quantity < $detail->quantity) {
                        throw new \Exception("商品「{$detail->product->name}」的库存不足，无法回退 {$detail->quantity} 个。当前库存: {$inventory->quantity} 个");
                    }
                    
                    $inventory->quantity -= $detail->quantity;
                    $inventory->save();

                    // 创建库存回退记录
                    if (class_exists('App\Models\InventoryRecord')) {
                        \App\Models\InventoryRecord::create([
                            'inventory_id' => $inventory->id,
                            'quantity' => -$detail->quantity, // 负数表示减少
                            'unit_price' => $detail->unit_cost ?? 0,
                            'total_amount' => -($detail->quantity * ($detail->unit_cost ?? 0)),
                            'type' => 'out',
                            'reference_type' => 'stock_in_delete',
                            'reference_id' => $stockInRecord->id,
                            'note' => "删除入库记录 #{$stockInRecord->id} - {$detail->product->name}",
                        ]);
                    }
                } else {
                    throw new \Exception("找不到商品「{$detail->product->name}」在仓库「{$stockInRecord->store->name}」的库存记录");
                }
            }

            if ($stockInRecord->image_path) {
                Storage::disk('public')->delete($stockInRecord->image_path);
            }

            $stockInRecord->stockInDetails()->delete();
            $stockInRecord->delete();

            DB::commit();

            return redirect()->route('stock-ins.index')
                ->with('success', '入库记录删除成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '入库记录删除失败：' . $e->getMessage());
        }
    }

    /**
     * 移动端显示入库界面
     */
    public function mobileIndex()
    {
        // 获取当前选择的仓库ID
        $currentStoreId = session('current_store_id');
        $user = auth()->user();
        
        // 获取可访问的仓库
        $stores = $user->getAccessibleStores()->where('is_active', true);
        
        // 获取商品数据 - 根据仓库权限筛选
        $productsQuery = Product::where('is_active', true)
            ->where('type', 'standard');
            
        // 如果选择了特定仓库，只显示该仓库的商品
        if ($currentStoreId && $currentStoreId != 0) {
            // 获取该仓库的商品（通过库存表关联）
            $productsQuery->whereHas('inventories', function($query) use ($currentStoreId) {
                $query->where('store_id', $currentStoreId);
            });
        } elseif (!$user->isSuperAdmin()) {
            // 非超级管理员，只显示有权限的仓库的商品
            $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
            $productsQuery->whereHas('inventories', function($query) use ($userStoreIds) {
                $query->whereIn('store_id', $userStoreIds);
            });
        }
        
        // 根据用户权限返回不同的字段
        if ($user->isSuperAdmin()) {
            $products = $productsQuery->orderBy('sort_order')->get();
        } else {
            $products = $productsQuery->orderBy('sort_order')->get(['id', 'name', 'code', 'type', 'is_active', 'sort_order']);
            // 为非超级管理员隐藏成本信息
            $products->each(function($product) {
                $product->cost_price = null;
            });
        }
        
        // 获取最近的入库记录
        $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
        $recentRecords = StockInRecord::with(['user', 'store', 'stockInDetails.product'])
            ->whereIn('store_id', $userStoreIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('mobile.stock-in.index', compact('products', 'stores', 'recentRecords'));
    }

    /**
     * 获取指定仓库的商品列表（AJAX）
     */
    public function getStoreProducts(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id'
        ]);

        $storeId = $request->store_id;
        $user = auth()->user();

        // 检查用户是否有权限访问该仓库
        if (!$user->canAccessStore($storeId)) {
            return response()->json(['error' => '无权限访问该仓库'], 403);
        }

        // 获取该仓库的商品
        $productsQuery = Product::where('is_active', true)
            ->where('type', 'standard')
            ->whereHas('inventories', function($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->orderBy('sort_order');
            
        // 根据用户权限返回不同的字段
        if ($user->isSuperAdmin()) {
            $products = $productsQuery->get(['id', 'name', 'code', 'cost_price']);
        } else {
            $products = $productsQuery->get(['id', 'name', 'code']);
            // 为非超级管理员隐藏成本信息
            $products->each(function($product) {
                $product->cost_price = null;
            });
        }

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    /**
     * 移动端保存入库记录
     */
    public function mobileStore(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'supplier' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // 校验用户是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($request->store_id)) {
            return back()->withErrors(['store_id' => '无权限操作该仓库'])->withInput();
        }

        // 校验是否有有效的入库数量
        $hasValidQuantity = false;
        foreach ($request->products as $item) {
            if (isset($item['quantity']) && $item['quantity'] > 0) {
                $hasValidQuantity = true;
                break;
            }
        }

        if (!$hasValidQuantity) {
            return back()->withErrors(['products' => '请至少输入一个商品的入库数量'])->withInput();
        }

        try {
            DB::beginTransaction();

            $record = new StockInRecord();
            $record->store_id = $request->store_id;
            $record->supplier = $request->supplier ?? '';
            $record->remark = $request->remark;
            $record->user_id = auth()->id();
            $record->save();

            $totalAmount = 0;
            $totalCost = 0;

            foreach ($request->products as $item) {
                if (isset($item['quantity']) && $item['quantity'] > 0) {
                    $product = Product::find($item['id']);
                    
                    if (!$product) {
                        throw new \Exception("商品 ID {$item['id']} 不存在");
                    }
                    
                    $costPrice = $product->cost_price;
                    
                    // 创建入库明细（使用商品成本价）
                    $detail = new StockInDetail();
                    $detail->stock_in_record_id = $record->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $item['quantity'];
                    $detail->unit_price = $costPrice;
                    $detail->unit_cost = $costPrice;
                    $detail->total_amount = $item['quantity'] * $costPrice;
                    $detail->total_cost = $item['quantity'] * $costPrice;
                    $detail->save();

                    // 更新库存（多仓库支持）
                    $inventory = Inventory::firstOrNew([
                        'store_id' => $request->store_id, 
                        'product_id' => $item['id']
                    ]);
                    $inventory->quantity = ($inventory->quantity ?? 0) + $item['quantity'];
                    $inventory->save();

                    $totalAmount += $detail->total_amount;
                    $totalCost += $detail->total_cost;
                }
            }

            $record->total_amount = $totalAmount;
            $record->total_cost = $totalCost;
            $record->save();

            DB::commit();

            return redirect()
                ->route('mobile.stock-in.index')
                ->with('success', '入库成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '入库失败：' . $e->getMessage())->withInput();
        }
    }
} 