<?php

namespace App\Http\Controllers;

use App\Models\StoreTransfer;
use App\Models\Store;
use App\Models\Product;
use App\Models\Inventory;
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
        $stores = Store::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('store-transfers.create', compact('stores', 'products'));
    }

    /**
     * 获取可调拨的商品
     */
    public function getAvailableProducts(Request $request)
    {
        $sourceStoreId = $request->source_store_id;
        $targetStoreId = $request->target_store_id;

        if (!$sourceStoreId || !$targetStoreId) {
            return response()->json(['products' => []]);
        }

        $products = StoreTransfer::getAvailableProducts($sourceStoreId, $targetStoreId);

        return response()->json(['products' => $products]);
    }

    /**
     * 获取商品库存信息
     */
    public function getProductStock(Request $request)
    {
        $productId = $request->product_id;
        $storeId = $request->store_id;

        $inventory = Inventory::where('product_id', $productId)
                            ->where('store_id', $storeId)
                            ->first();

        return response()->json([
            'quantity' => $inventory ? $inventory->quantity : 0,
            'unit_cost' => $inventory ? $inventory->product->cost_price : 0
        ]);
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
        ]);

        // 检查源仓库库存
        $sourceInventory = Inventory::where('product_id', $request->product_id)
                                  ->where('store_id', $request->source_store_id)
                                  ->first();

        if (!$sourceInventory || $sourceInventory->quantity < $request->quantity) {
            return back()->withErrors(['quantity' => '源仓库库存不足']);
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

            $transfer = StoreTransfer::create([
                'transfer_no' => StoreTransfer::generateTransferNo(),
                'source_store_id' => $request->source_store_id,
                'target_store_id' => $request->target_store_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'unit_cost' => $sourceInventory->product->cost_price,
                'total_cost' => $sourceInventory->product->cost_price * $request->quantity,
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
            if (!in_array($storeTransfer->target_store_id, $userStores->toArray())) {
                return back()->withErrors(['error' => '无权完成该调拨']);
            }
        }

        try {
            DB::beginTransaction();

            // 减少源仓库库存
            $sourceInventory = Inventory::where('product_id', $storeTransfer->product_id)
                                      ->where('store_id', $storeTransfer->source_store_id)
                                      ->first();

            if (!$sourceInventory || $sourceInventory->quantity < $storeTransfer->quantity) {
                throw new \Exception('源仓库库存不足');
            }

            $sourceInventory->decrement('quantity', $storeTransfer->quantity);

            // 增加目标仓库库存
            $targetInventory = Inventory::firstOrCreate([
                'product_id' => $storeTransfer->product_id,
                'store_id' => $storeTransfer->target_store_id,
            ], [
                'quantity' => 0,
                'min_quantity' => 10,
                'max_quantity' => 1000,
            ]);

            $targetInventory->increment('quantity', $storeTransfer->quantity);

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
     * 取消调拨
     */
    public function cancel(Request $request, StoreTransfer $storeTransfer)
    {
        if (!$storeTransfer->canBeCancelled()) {
            return back()->withErrors(['error' => '该调拨申请无法取消']);
        }

        // 检查权限
        if (!Auth::user()->isSuperAdmin()) {
            $userStores = Auth::user()->stores->pluck('id');
            if (!in_array($storeTransfer->source_store_id, $userStores->toArray()) &&
                !in_array($storeTransfer->target_store_id, $userStores->toArray())) {
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
     * 删除调拨记录
     */
    public function destroy(StoreTransfer $storeTransfer)
    {
        if (!in_array($storeTransfer->status, [StoreTransfer::STATUS_REJECTED, StoreTransfer::STATUS_CANCELLED])) {
            return back()->withErrors(['error' => '只能删除已拒绝或已取消的调拨记录']);
        }

        $storeTransfer->delete();

        return back()->with('success', '调拨记录已删除');
    }
} 