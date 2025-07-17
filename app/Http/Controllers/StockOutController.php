<?php

namespace App\Http\Controllers;

use App\Models\StockOutRecord;
use App\Models\StockOutDetail;
use App\Models\Inventory;
use App\Models\PriceSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StockOutController extends Controller
{
    /**
     * 显示出库记录列表
     */
    public function index()
    {
        $storeId = request('store_id', session('current_store_id'));
        $userStoreIds = auth()->user()->getAccessibleStores()->pluck('id')->toArray();
        $query = StockOutRecord::with(['user', 'store', 'stockOutDetails.product'])
            ->whereIn('store_id', $userStoreIds);
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        $stockOuts = $query->orderBy('created_at', 'desc')->paginate(10);
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true);
        return view('stock-out.index', compact('stockOuts', 'stores'));
    }

    /**
     * 显示出库表单
     */
    public function create()
    {
        $priceSeries = PriceSeries::all();
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true)->get();
        return view('stock-out.create', compact('priceSeries', 'stores'));
    }

    /**
     * 保存出库记录
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'customer' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'price_series' => 'required|array',
            'price_series.*.code' => 'required|exists:price_series,code',
            'price_series.*.quantity' => 'required|integer|min:0',
            'price_series.*.unit_price' => 'required|numeric|min:0',
        ]);

        // 校验用户是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($request->store_id)) {
            return back()->withErrors(['store_id' => '无权限操作该仓库'])->withInput();
        }

        try {
            DB::beginTransaction();

            $record = new StockOutRecord();
            $record->store_id = $request->store_id;
            $record->customer = $request->customer;
            $record->remark = $request->remark;
            $record->user_id = auth()->id();

            if ($request->hasFile('image')) {
                $record->image_path = $request->file('image')->store('stock-out', 'public');
            }

            $record->save();

            $totalAmount = 0;
            $totalCost = 0;

            foreach ($request->price_series as $item) {
                if ($item['quantity'] > 0) {
                    $priceSeries = PriceSeries::where('code', $item['code'])->first();
                    
                    // 检查库存是否足够
                    $inventory = Inventory::where('store_id', $request->store_id)
                        ->where('series_code', $item['code'])
                        ->first();
                    
                    if (!$inventory || $inventory->quantity < $item['quantity']) {
                        throw new \Exception("{$priceSeries->code} 库存不足");
                    }
                    
                    // 创建出库明细
                    $detail = new StockOutDetail();
                    $detail->stock_out_record_id = $record->id;
                    $detail->series_code = $item['code'];
                    $detail->quantity = $item['quantity'];
                    $detail->unit_price = $item['unit_price'];
                    $detail->total_amount = $item['quantity'] * $item['unit_price'];
                    $detail->total_cost = $item['quantity'] * $priceSeries->cost;
                    $detail->save();

                    // 更新库存
                    $inventory->quantity -= $item['quantity'];
                    $inventory->save();

                    $totalAmount += $detail->total_amount;
                    $totalCost += $detail->total_cost;
                }
            }

            $record->total_amount = $totalAmount;
            $record->total_cost = $totalCost;
            $record->save();

            DB::commit();

            return redirect()->route('stock-outs.show', $record)
                ->with('success', '出库记录创建成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '出库记录创建失败：' . $e->getMessage());
        }
    }

    /**
     * 显示出库记录详情
     */
    public function show(StockOutRecord $stockOutRecord)
    {
        // 使用 DB 查询替代 Eloquent 关系查询
        $record = DB::table('stock_out_records')
            ->leftJoin('users', 'stock_out_records.user_id', '=', 'users.id')
            ->leftJoin('stores', 'stock_out_records.store_id', '=', 'stores.id')
            ->select(
                'stock_out_records.*',
                'users.real_name as user_name',
                'stores.name as store_name'
            )
            ->where('stock_out_records.id', $stockOutRecord->id)
            ->first();

        // 获取出库详情
        $stockOutDetails = DB::table('stock_out_details')
            ->leftJoin('price_series', 'stock_out_details.series_code', '=', 'price_series.code')
            ->select(
                'stock_out_details.*',
                'price_series.name as series_name',
                'price_series.code as series_code'
            )
            ->where('stock_out_details.stock_out_record_id', $stockOutRecord->id)
            ->get();

        $record->stock_out_details = $stockOutDetails;

        return view('stock-out.show', compact('record'));
    }

    /**
     * 删除出库记录
     */
    public function destroy(StockOutRecord $stockOutRecord)
    {
        // 检查权限
        if (!$stockOutRecord->canDelete()) {
            return back()->with('error', '无权删除此出库记录');
        }

        try {
            DB::beginTransaction();

            // 恢复库存
            foreach ($stockOutRecord->stockOutDetails as $detail) {
                $inventory = Inventory::where('store_id', $stockOutRecord->store_id)
                    ->where('series_code', $detail->series_code)
                    ->first();
                
                if ($inventory) {
                    $inventory->quantity += $detail->quantity;
                    $inventory->save();
                }
            }

            if ($stockOutRecord->image_path) {
                Storage::disk('public')->delete($stockOutRecord->image_path);
            }

            $stockOutRecord->stockOutDetails()->delete();
            $stockOutRecord->delete();

            DB::commit();

            return redirect()->route('stock-outs.index')
                ->with('success', '出库记录删除成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '出库记录删除失败：' . $e->getMessage());
        }
    }
} 