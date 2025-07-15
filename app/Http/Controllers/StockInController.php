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
        $userStoreIds = auth()->user()->stores()->pluck('stores.id')->toArray();
        
        // 使用 Eloquent 模型查询，但优化关系加载
        $query = StockInRecord::with(['user', 'store', 'stockInDetails.product'])
            ->whereIn('store_id', $userStoreIds);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $stockIns = $query->orderBy('created_at', 'desc')->paginate(10);

        $stores = auth()->user()->stores()->where('is_active', true)->get();

        return view('stock-ins.index', compact('stockIns', 'stores'));
    }

    /**
     * 显示入库表单
     */
    public function create()
    {
        // 只显示标准商品，因为入库管理不需要对盲袋商品进行操作
        $products = Product::active()->where('type', 'standard')->get();
        $stores = auth()->user()->stores()->where('is_active', true)->get();
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
                    $inventory->quantity -= $detail->quantity;
                    $inventory->save();
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
        // 获取商品数据
        $products = Product::where('is_active', true)
            ->where('type', 'standard')
            ->orderBy('sort_order')
            ->get();
        $stores = auth()->user()->stores()->where('is_active', true)->get();
        
        // 获取最近的入库记录
        $userStoreIds = auth()->user()->stores()->pluck('stores.id')->toArray();
        $recentRecords = StockInRecord::with(['user', 'store', 'stockInDetails.product'])
            ->whereIn('store_id', $userStoreIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('mobile.stock-in.index', compact('products', 'stores', 'recentRecords'));
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