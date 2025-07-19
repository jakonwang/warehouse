<?php

namespace App\Http\Controllers;

use App\Models\InventoryCheckRecord;
use App\Models\InventoryCheckDetail;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryCheckController extends Controller
{
    /**
     * 显示盘点记录列表
     */
    public function index()
    {
        // 简化查询，避免复杂的 Eloquent 关联
        $query = DB::table('inventory_check_records as icr')
            ->leftJoin('users', 'users.id', '=', 'icr.user_id')
            ->leftJoin('stores', 'stores.id', '=', 'icr.store_id')
            ->select(
                'icr.id', 'icr.store_id', 'icr.user_id', 'icr.status', 'icr.remark', 'icr.created_at',
                'users.name as user_name',
                'stores.name as store_name'
            );

        // 按仓库筛选
        if ($storeId = request('store_id')) {
            $query->where('icr.store_id', $storeId);
        }

        // 只显示用户有权限的仓库的记录
        $userStoreIds = auth()->user()->stores()->pluck('stores.id');
        if ($userStoreIds->isNotEmpty()) {
            $query->whereIn('icr.store_id', $userStoreIds);
        }

        $records = $query->orderBy('icr.created_at', 'desc')->paginate(10);
        $stores = DB::table('stores')->where('is_active', true)->select('id', 'name')->get();

        return view('inventory-check.index', compact('records', 'stores'));
    }

    /**
     * 显示盘点表单
     */
    public function create()
    {
        $stores = DB::table('stores')->where('is_active', true)->select('id', 'name')->get();
        // 不传递所有商品，让前端根据选择的仓库动态加载
        return view('inventory-check.create', compact('stores'));
    }

    /**
     * 保存盘点记录
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'remark' => 'nullable|string',
            'details' => 'required|array',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.actual_quantity' => 'required|integer|min:0',
        ]);

        // 校验用户是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($request->store_id)) {
            return back()->withErrors(['store_id' => '无权限操作该仓库'])->withInput();
        }

        try {
            DB::beginTransaction();

            $record = new InventoryCheckRecord();
            $record->store_id = $request->store_id;
            $record->remark = $request->remark;
            $record->user_id = auth()->id();
            $record->save();

            foreach ($request->details as $item) {
                $product = Product::find($item['product_id']);
                $inventory = Inventory::firstOrNew([
                    'store_id' => $request->store_id,
                    'product_id' => $item['product_id']
                ]);

                $detail = new InventoryCheckDetail();
                $detail->inventory_check_record_id = $record->id;
                $detail->product_id = $item['product_id'];
                $detail->system_quantity = $inventory->quantity ?? 0;
                $detail->actual_quantity = $item['actual_quantity'];
                $detail->difference = $item['actual_quantity'] - ($inventory->quantity ?? 0);
                $detail->unit_cost = $product->cost_price;
                $detail->total_cost = $detail->difference * $product->cost_price;
                $detail->save();
            }

            DB::commit();

            return redirect()->route('inventory-check.show', $record)
                ->with('success', '盘点记录创建成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '盘点记录创建失败：' . $e->getMessage());
        }
    }

    /**
     * 显示盘点记录详情
     */
    public function show(InventoryCheckRecord $inventoryCheckRecord)
    {
        $inventoryCheckRecord->load(['user', 'store', 'inventoryCheckDetails.product']);
        return view('inventory-check.show', compact('inventoryCheckRecord'));
    }

    /**
     * 确认盘点记录
     */
    public function confirm(InventoryCheckRecord $inventoryCheckRecord)
    {
        // 检查权限
        if (!$inventoryCheckRecord->canConfirm()) {
            return back()->with('error', '无权确认此盘点记录');
        }

        try {
            DB::beginTransaction();

            // 更新库存
            foreach ($inventoryCheckRecord->inventoryCheckDetails as $detail) {
                $inventory = Inventory::firstOrNew([
                    'store_id' => $inventoryCheckRecord->store_id,
                    'product_id' => $detail->product_id
                ]);
                $inventory->quantity = $detail->actual_quantity;
                $inventory->save();
            }

            $inventoryCheckRecord->status = 'confirmed';
            $inventoryCheckRecord->save();

            DB::commit();

            return redirect()->route('inventory-check.show', $inventoryCheckRecord)
                ->with('success', '盘点记录确认成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '盘点记录确认失败：' . $e->getMessage());
        }
    }

    /**
     * 删除盘点记录
     */
    public function destroy(InventoryCheckRecord $inventoryCheckRecord)
    {
        // 检查权限
        if (!$inventoryCheckRecord->canDelete()) {
            return back()->with('error', '无权删除此盘点记录');
        }

        try {
            DB::beginTransaction();

            $inventoryCheckRecord->inventoryCheckDetails()->delete();
            $inventoryCheckRecord->delete();

            DB::commit();

            return redirect()->route('inventory-check.index')
                ->with('success', '盘点记录删除成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '盘点记录删除失败：' . $e->getMessage());
        }
    }
} 