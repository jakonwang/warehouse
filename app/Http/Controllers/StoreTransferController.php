<?php

namespace App\Http\Controllers;

use App\Models\StoreTransfer;
use App\Models\Store;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StoreTransferController extends Controller
{
    /**
     * 显示调拨列表
     */
    public function index(Request $request)
    {
        $query = StoreTransfer::with(['sourceStore', 'targetStore', 'product', 'requester', 'approver']);

        // 权限过滤
        if (!Auth::user()->isSuperAdmin()) {
            $userStores = Auth::user()->stores->pluck('id');
            $query->where(function ($q) use ($userStores) {
                $q->whereIn('source_store_id', $userStores)
                  ->orWhereIn('target_store_id', $userStores);
            });
        }

        // 筛选条件
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source_store_id')) {
            $query->where('source_store_id', $request->source_store_id);
        }

        if ($request->filled('target_store_id')) {
            $query->where('target_store_id', $request->target_store_id);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', $dates);
            }
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(15);
        $stores = Store::where('is_active', true)->get();

        return view('store-transfers.index', compact('transfers', 'stores'));
    }

    /**
     * 显示创建调拨页面
     */
    public function create()
    {
        // 获取用户有权限的仓库
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            $stores = Store::where('is_active', true)->get();
        } else {
            $stores = $user->stores()->where('is_active', true)->get();
        }

        return view('store-transfers.create', compact('stores'));
    }

    /**
     * 获取源仓库的商品库存
     */
    public function getSourceStoreProducts(Request $request)
    {
        $sourceStoreId = $request->source_store_id;
        
        if (!$sourceStoreId) {
            return response()->json(['products' => []]);
        }

        // 获取源仓库有库存的商品
        $products = Inventory::with(['product:id,name,code,cost_price'])
            ->where('store_id', $sourceStoreId)
            ->where('quantity', '>', 0)
            ->whereHas('product', function($query) {
                $query->where('is_active', true)
                      ->where('type', 'standard');
            })
            ->get()
            ->map(function($inventory) {
                return [
                    'id' => $inventory->product->id,
                    'name' => $inventory->product->name,
                    'code' => $inventory->product->code,
                    'quantity' => $inventory->quantity,
                    'unit_cost' => $inventory->product->cost_price ?? 0,
                    'total_cost' => ($inventory->product->cost_price ?? 0) * $inventory->quantity
                ];
            });

        return response()->json(['products' => $products]);
    }

    /**
     * 获取目标仓库的商品库存
     */
    public function getTargetStoreProducts(Request $request)
    {
        $targetStoreId = $request->target_store_id;
        
        if (!$targetStoreId) {
            return response()->json(['products' => []]);
        }

        // 获取目标仓库的商品库存
        $products = Inventory::with(['product:id,name,code,cost_price'])
            ->where('store_id', $targetStoreId)
            ->whereHas('product', function($query) {
                $query->where('is_active', true)
                      ->where('type', 'standard');
            })
            ->get()
            ->map(function($inventory) {
                return [
                    'id' => $inventory->product->id,
                    'name' => $inventory->product->name,
                    'code' => $inventory->product->code,
                    'quantity' => $inventory->quantity,
                    'unit_cost' => $inventory->product->cost_price ?? 0,
                    'total_cost' => ($inventory->product->cost_price ?? 0) * $inventory->quantity
                ];
            });

        return response()->json(['products' => $products]);
    }

    /**
     * 获取商品在两个仓库的库存对比
     */
    public function getProductComparison(Request $request)
    {
        $productId = $request->product_id;
        $sourceStoreId = $request->source_store_id;
        $targetStoreId = $request->target_store_id;

        if (!$productId || !$sourceStoreId || !$targetStoreId) {
            return response()->json(['comparison' => null]);
        }

        // 获取源仓库库存
        $sourceInventory = Inventory::with(['product:id,name,code,cost_price'])
            ->where('product_id', $productId)
            ->where('store_id', $sourceStoreId)
            ->first();

        // 获取目标仓库库存
        $targetInventory = Inventory::with(['product:id,name,code,cost_price'])
            ->where('product_id', $productId)
            ->where('store_id', $targetStoreId)
            ->first();

        $comparison = [
            'product' => [
                'id' => $productId,
                'name' => $sourceInventory ? $sourceInventory->product->name : '未知商品',
                'code' => $sourceInventory ? $sourceInventory->product->code : '',
            ],
            'source_store' => [
                'id' => $sourceStoreId,
                'quantity' => $sourceInventory ? $sourceInventory->quantity : 0,
                'unit_cost' => $sourceInventory ? ($sourceInventory->product->cost_price ?? 0) : 0,
            ],
            'target_store' => [
                'id' => $targetStoreId,
                'quantity' => $targetInventory ? $targetInventory->quantity : 0,
                'unit_cost' => $targetInventory ? ($targetInventory->product->cost_price ?? 0) : 0,
            ]
        ];

        return response()->json(['comparison' => $comparison]);
    }

    /**
     * 保存调拨申请
     */
    public function store(Request $request)
    {
        $request->validate([
            'source_store_id' => 'required|exists:stores,id',
            'target_store_id' => 'required|exists:stores,id|different:source_store_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'remark' => 'nullable|string|max:500',
        ], [
            'source_store_id.required' => '请选择源仓库',
            'source_store_id.exists' => '源仓库不存在',
            'target_store_id.required' => '请选择目标仓库',
            'target_store_id.exists' => '目标仓库不存在',
            'target_store_id.different' => '源仓库和目标仓库不能相同',
            'product_id.required' => '请选择调拨商品',
            'product_id.exists' => '商品不存在',
            'quantity.required' => '请输入调拨数量',
            'quantity.integer' => '调拨数量必须是整数',
            'quantity.min' => '调拨数量必须大于0',
            'reason.required' => '请输入调拨原因',
            'reason.max' => '调拨原因不能超过500个字符',
            'remark.max' => '备注不能超过500个字符',
        ]);

        // 检查源仓库库存
        $sourceInventory = Inventory::where('product_id', $request->product_id)
                                  ->where('store_id', $request->source_store_id)
                                  ->first();

        if (!$sourceInventory || $sourceInventory->quantity < $request->quantity) {
            return back()->withErrors(['quantity' => '源仓库库存不足，当前库存：' . ($sourceInventory ? $sourceInventory->quantity : 0)]);
        }

        // 检查权限
        if (!Auth::user()->isSuperAdmin()) {
            $userStores = Auth::user()->stores->pluck('id');
            if (!in_array($request->source_store_id, $userStores->toArray())) {
                return back()->withErrors(['source_store_id' => '无权操作该仓库']);
            }
        }

        try {
            DB::beginTransaction();

            $unitCost = $sourceInventory->product->cost_price ?? 0;
            $totalCost = $unitCost * $request->quantity;

            $transfer = StoreTransfer::create([
                'transfer_no' => StoreTransfer::generateTransferNo(),
                'source_store_id' => $request->source_store_id,
                'target_store_id' => $request->target_store_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'status' => StoreTransfer::STATUS_PENDING,
                'reason' => $request->reason,
                'remark' => $request->remark,
                'requested_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('store-transfers.index')
                           ->with('success', '调拨申请已提交，等待审批');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors(['error' => '创建调拨申请失败：' . $e->getMessage()]);
        }
    }

    /**
     * 显示调拨详情
     */
    public function show(StoreTransfer $storeTransfer)
    {
        $storeTransfer->load(['sourceStore', 'targetStore', 'product', 'requester', 'approver']);

        return view('store-transfers.show', compact('storeTransfer'));
    }

    /**
     * 审批调拨申请
     */
    public function approve(Request $request, StoreTransfer $storeTransfer)
    {
        if (!$storeTransfer->canBeApproved()) {
            return back()->withErrors(['error' => '该调拨申请无法审批']);
        }

        // 检查权限
        if (!Auth::user()->isSuperAdmin()) {
            $userStores = Auth::user()->stores->pluck('id');
            if (!in_array($storeTransfer->source_store_id, $userStores->toArray())) {
                return back()->withErrors(['error' => '无权审批该调拨申请']);
            }
        }

        try {
            DB::beginTransaction();

            $storeTransfer->update([
                'status' => StoreTransfer::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', '调拨申请已审批通过');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '审批失败：' . $e->getMessage()]);
        }
    }

    /**
     * 拒绝调拨申请
     */
    public function reject(Request $request, StoreTransfer $storeTransfer)
    {
        if (!$storeTransfer->canBeApproved()) {
            return back()->withErrors(['error' => '该调拨申请无法拒绝']);
        }

        // 检查权限
        if (!Auth::user()->isSuperAdmin()) {
            $userStores = Auth::user()->stores->pluck('id');
            if (!in_array($storeTransfer->source_store_id, $userStores->toArray())) {
                return back()->withErrors(['error' => '无权拒绝该调拨申请']);
            }
        }

        try {
            DB::beginTransaction();

            $storeTransfer->update([
                'status' => StoreTransfer::STATUS_REJECTED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', '调拨申请已拒绝');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '拒绝失败：' . $e->getMessage()]);
        }
    }

    /**
     * 完成调拨
     */
    public function complete(Request $request, StoreTransfer $storeTransfer)
    {
        if (!$storeTransfer->canBeCompleted()) {
            return back()->withErrors(['error' => '该调拨申请无法完成']);
        }

        // 检查权限
        if (!Auth::user()->isSuperAdmin()) {
            $userStores = Auth::user()->stores->pluck('id');
            if (!in_array($storeTransfer->source_store_id, $userStores->toArray())) {
                return back()->withErrors(['error' => '无权完成该调拨申请']);
            }
        }

        try {
            DB::beginTransaction();

            // 检查源仓库库存是否足够
            $sourceInventory = Inventory::where('product_id', $storeTransfer->product_id)
                                      ->where('store_id', $storeTransfer->source_store_id)
                                      ->first();

            if (!$sourceInventory || $sourceInventory->quantity < $storeTransfer->quantity) {
                throw new \Exception('源仓库库存不足，无法完成调拨');
            }

            // 减少源仓库库存
            $sourceInventory->quantity -= $storeTransfer->quantity;
            $sourceInventory->save();

            // 增加目标仓库库存
            $targetInventory = Inventory::firstOrNew([
                'product_id' => $storeTransfer->product_id,
                'store_id' => $storeTransfer->target_store_id
            ]);
            $targetInventory->quantity = ($targetInventory->quantity ?? 0) + $storeTransfer->quantity;
            $targetInventory->save();

            // 记录源仓库库存变动
            InventoryRecord::create([
                'inventory_id' => $sourceInventory->id,
                'quantity' => -$storeTransfer->quantity,
                'unit_price' => $storeTransfer->unit_cost,
                'total_amount' => -$storeTransfer->total_cost,
                'type' => 'transfer_out',
                'reference_type' => 'store_transfer',
                'reference_id' => $storeTransfer->id,
                'note' => '调拨出库',
            ]);

            // 记录目标仓库库存变动
            InventoryRecord::create([
                'inventory_id' => $targetInventory->id,
                'quantity' => $storeTransfer->quantity,
                'unit_price' => $storeTransfer->unit_cost,
                'total_amount' => $storeTransfer->total_cost,
                'type' => 'transfer_in',
                'reference_type' => 'store_transfer',
                'reference_id' => $storeTransfer->id,
                'note' => '调拨入库',
            ]);

            // 更新调拨状态
            $storeTransfer->update([
                'status' => StoreTransfer::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', '调拨已完成');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '完成调拨失败：' . $e->getMessage()]);
        }
    }

    /**
     * 取消调拨申请
     */
    public function cancel(Request $request, StoreTransfer $storeTransfer)
    {
        if (!$storeTransfer->canBeCancelled()) {
            return back()->withErrors(['error' => '该调拨申请无法取消']);
        }

        // 检查权限
        if (!Auth::user()->isSuperAdmin()) {
            $userStores = Auth::user()->stores->pluck('id');
            if (!in_array($storeTransfer->source_store_id, $userStores->toArray())) {
                return back()->withErrors(['error' => '无权取消该调拨申请']);
            }
        }

        try {
            DB::beginTransaction();

            $storeTransfer->update([
                'status' => StoreTransfer::STATUS_CANCELLED,
            ]);

            DB::commit();

            return back()->with('success', '调拨申请已取消');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '取消失败：' . $e->getMessage()]);
        }
    }

    /**
     * 删除调拨申请
     */
    public function destroy(StoreTransfer $storeTransfer)
    {
        // 检查权限
        if (!Auth::user()->isSuperAdmin()) {
            $userStores = Auth::user()->stores->pluck('id');
            if (!in_array($storeTransfer->source_store_id, $userStores->toArray())) {
                return back()->withErrors(['error' => '无权删除该调拨申请']);
            }
        }

        // 只能删除待审批或已取消的调拨
        if (!in_array($storeTransfer->status, [StoreTransfer::STATUS_PENDING, StoreTransfer::STATUS_CANCELLED])) {
            return back()->withErrors(['error' => '只能删除待审批或已取消的调拨申请']);
        }

        try {
            $storeTransfer->delete();
            return back()->with('success', '调拨申请已删除');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '删除失败：' . $e->getMessage()]);
        }
    }
} 