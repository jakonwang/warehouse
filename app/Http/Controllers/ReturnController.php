<?php

namespace App\Http\Controllers;

use App\Models\ReturnRecord;
use App\Models\ReturnDetail;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReturnController extends Controller
{
    /**
     * 显示退货记录列表
     */
    public function index()
    {
        $storeId = request('store_id', session('current_store_id'));
        $userStoreIds = auth()->user()->getAccessibleStores()->pluck('id')->toArray();
        
        // 构建基础查询
        $query = ReturnRecord::with(['user', 'store', 'returnDetails.product'])
            ->whereIn('store_id', $userStoreIds);
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        
        // 获取分页数据
        $records = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // 计算统计数据
        $statsQuery = ReturnRecord::whereIn('store_id', $userStoreIds);
        if ($storeId) {
            $statsQuery->where('store_id', $storeId);
        }
        
        // 今日退货数量
        $todayCount = (clone $statsQuery)->whereDate('created_at', today())->count();
        
        // 今日退货总金额
        $totalAmount = (clone $statsQuery)->whereDate('created_at', today())->sum('total_amount') ?? 0;
        
        // 待处理数量（假设没有状态字段，暂时设为0）
        $pendingCount = 0;
        
        // 计算退货率（基于本月数据）
        $currentMonth = now()->startOfMonth();
        $monthlyReturnAmount = (clone $statsQuery)->where('created_at', '>=', $currentMonth)->sum('total_amount') ?? 0;
        
        // 获取本月销售金额（同样的仓库权限范围）
        $monthlySalesAmount = \App\Models\Sale::whereIn('store_id', $userStoreIds);
        if ($storeId) {
            $monthlySalesAmount->where('store_id', $storeId);
        }
        $monthlySalesAmount = $monthlySalesAmount->where('created_at', '>=', $currentMonth)->sum('total_amount') ?? 0;
        
        // 计算退货率：(退货金额 / 销售金额) * 100%
        $returnRate = $monthlySalesAmount > 0 ? ($monthlyReturnAmount / $monthlySalesAmount) * 100 : 0;
        $returnRate = round($returnRate, 1); // 保留1位小数
        
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true);
        
        return view('returns.index', compact('records', 'stores', 'todayCount', 'totalAmount', 'pendingCount', 'returnRate'));
    }

    /**
     * 显示退货表单
     */
    public function create()
    {
        $products = Product::active()->where('type', 'standard')->get();
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true);
        $storeId = request('store_id', session('current_store_id'));
        return view('returns.create', compact('products', 'stores', 'storeId'));
    }

    /**
     * 保存退货记录
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'customer' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:0',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        // 校验用户是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($request->store_id)) {
            return back()->withErrors(['store_id' => '无权限操作该仓库'])->withInput();
        }

        try {
            DB::beginTransaction();

            $record = new ReturnRecord();
            $record->store_id = $request->store_id;
            $record->customer = $request->customer;
            $record->remark = $request->remark;
            $record->user_id = auth()->id();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = 'return_' . time() . '_' . uniqid() . '.' . $extension;
                $record->image_path = $file->storeAs('returns', $filename, 'public_direct');
            }

            $record->save();

            $totalAmount = 0;
            $totalCost = 0;

            foreach ($request->products as $item) {
                if ($item['quantity'] > 0) {
                    $product = Product::find($item['id']);
                    
                    // 创建退货明细
                    $detail = new ReturnDetail();
                    $detail->return_record_id = $record->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $item['quantity'];
                    $detail->unit_price = $item['unit_price'];
                    $detail->total_amount = $item['quantity'] * $item['unit_price'];
                    $detail->total_cost = $item['quantity'] * $product->cost_price;
                    $detail->save();

                    // 更新库存
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

            return redirect()->route('returns.show', $record)
                ->with('success', '退货记录创建成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '退货记录创建失败：' . $e->getMessage());
        }
    }

    /**
     * 显示退货记录详情
     */
    public function show($id)
    {
        $returnRecord = ReturnRecord::with(['user', 'store', 'returnDetails.product'])
            ->findOrFail($id);

        return view('returns.show', compact('returnRecord'));
    }

    /**
     * 显示编辑表单
     */
    public function edit($id)
    {
        $returnRecord = ReturnRecord::findOrFail($id);
        $products = Product::where('is_active', true)
            ->where('type', Product::TYPE_STANDARD)
            ->orderBy('sort_order')
            ->get();
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true)->values();
        return view('returns.edit', compact('returnRecord', 'products', 'stores'));
    }

    /**
     * 更新退货记录
     */
    public function update(Request $request, $id)
    {
        $returnRecord = ReturnRecord::findOrFail($id);
        
        // 验证和更新逻辑
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'customer' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
        ]);

        $returnRecord->update($request->only(['store_id', 'customer', 'remark']));

        return redirect()->route('returns.show', $returnRecord->id)
            ->with('success', '退货记录更新成功！');
    }

    /**
     * 删除退货记录
     */
    public function destroy($id)
    {
        $returnRecord = ReturnRecord::findOrFail($id);
        
        // 检查权限
        if (!$returnRecord->canDelete()) {
            return back()->with('error', '无权删除此退货记录');
        }

        try {
            DB::beginTransaction();

            // 恢复库存
            foreach ($returnRecord->returnDetails as $detail) {
                $inventory = Inventory::where('store_id', $returnRecord->store_id)
                    ->where('product_id', $detail->product_id)
                    ->first();
                
                if ($inventory) {
                    $inventory->quantity -= $detail->quantity;
                    $inventory->save();
                }
            }

            if ($returnRecord->image_path) {
                Storage::disk('public')->delete($returnRecord->image_path);
            }

            $returnRecord->returnDetails()->delete();
            $returnRecord->delete();

            DB::commit();

            return redirect()->route('returns.index')
                ->with('success', '退货记录删除成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '退货记录删除失败：' . $e->getMessage());
        }
    }

    /**
     * 移动端显示退货创建界面
     */
    public function mobileCreate()
    {
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true)->values();
        $products = Product::where('type', 'standard')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        return view('mobile.returns.create', compact('stores', 'products'));
    }

    /**
     * 移动端显示退货界面
     */
    public function mobileIndex()
    {
        $storeId = request('store_id') ?? session('current_store_id');
        $user = auth()->user();
        $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
        
        // 获取用户可访问的仓库
        $stores = $user->getAccessibleStores()->where('is_active', true)->values();
        
        // 获取标准商品（非盲袋）
        $products = Product::where('type', 'standard')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        // 获取最近的退货记录
        $query = ReturnRecord::with(['user', 'store', 'returnDetails.product'])
            ->whereIn('store_id', $userStoreIds);
            
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        
        $recentRecords = $query->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('mobile.returns.index', compact('stores', 'products', 'recentRecords', 'storeId'));
    }

    /**
     * 移动端保存退货记录
     */
    public function mobileStore(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'customer' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'nullable|numeric|min:0',
        ]);

        // 校验用户是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($request->store_id)) {
            return back()->withErrors(['store_id' => '无权限操作该仓库'])->withInput();
        }

        // 校验是否有有效的退货数量
        $hasValidQuantity = false;
        foreach ($request->products as $item) {
            if (isset($item['quantity']) && is_numeric($item['quantity']) && $item['quantity'] > 0) {
                $hasValidQuantity = true;
                break;
            }
        }

        if (!$hasValidQuantity) {
            return back()->withErrors(['products' => '请至少输入一个商品的退货数量'])->withInput();
        }

        try {
            DB::beginTransaction();

            $record = new ReturnRecord();
            $record->store_id = $request->store_id;
            $record->customer = $request->customer ?? '';
            $record->remark = $request->remark;
            $record->user_id = auth()->id();

            if ($request->hasFile('image')) {
                $record->image_path = $request->file('image')->store('returns', 'public');
            }

            $record->save();

            $totalAmount = 0;
            $totalCost = 0;

            foreach ($request->products as $item) {
                $quantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 0;
                if ($quantity > 0) {
                    $product = Product::find($item['id']);
                    
                    if (!$product) {
                        throw new \Exception("商品 ID {$item['id']} 不存在");
                    }
                    
                    // 创建退货明细
                    $detail = new ReturnDetail();
                    $detail->return_record_id = $record->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $quantity;
                    $detail->unit_price = $product->price; // 使用商品售价作为单价
                    $detail->total_amount = $quantity * $detail->unit_price;
                    $detail->total_cost = $quantity * $product->cost_price;
                    $detail->save();

                    // 更新库存
                    $inventory = Inventory::firstOrNew([
                        'store_id' => $request->store_id,
                        'product_id' => $item['id']
                    ]);
                    $inventory->quantity = ($inventory->quantity ?? 0) + $quantity;
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
                ->route('mobile.returns.index')
                ->with('success', '退货处理成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '退货处理失败：' . $e->getMessage())->withInput();
        }
    }

    /**
     * 移动端显示退货编辑表单
     */
    public function mobileEdit($id)
    {
        $returnRecord = ReturnRecord::findOrFail($id);
        // 只允许有权限的用户编辑
        if (!auth()->user()->canAccessStore($returnRecord->store_id)) {
            abort(403, '无权限操作该仓库');
        }
        $products = Product::where('is_active', true)
            ->where('type', Product::TYPE_STANDARD)
            ->orderBy('sort_order')
            ->get();
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true)->values();
        return view('mobile.returns.edit', compact('returnRecord', 'products', 'stores'));
    }

    /**
     * 移动端提交退货编辑
     */
    public function mobileUpdate(Request $request, $id)
    {
        $returnRecord = ReturnRecord::findOrFail($id);
        if (!auth()->user()->canAccessStore($returnRecord->store_id)) {
            abort(403, '无权限操作该仓库');
        }
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'customer' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'nullable|numeric|min:0',
        ]);
        // 校验是否有有效的退货数量
        $hasValidQuantity = false;
        foreach ($request->products as $item) {
            if (isset($item['quantity']) && is_numeric($item['quantity']) && $item['quantity'] > 0) {
                $hasValidQuantity = true;
                break;
            }
        }
        if (!$hasValidQuantity) {
            return back()->withErrors(['products' => '请至少输入一个商品的退货数量'])->withInput();
        }
        try {
            DB::beginTransaction();
            $returnRecord->store_id = $request->store_id;
            $returnRecord->customer = $request->customer ?? '';
            $returnRecord->remark = $request->remark;
            $returnRecord->user_id = auth()->id();
            if ($request->hasFile('image')) {
                $returnRecord->image_path = $request->file('image')->store('returns', 'public');
            }
            $returnRecord->save();
            // 删除原明细
            $returnRecord->returnDetails()->delete();
            $totalAmount = 0;
            $totalCost = 0;
            foreach ($request->products as $item) {
                $quantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 0;
                if ($quantity > 0) {
                    $product = Product::find($item['id']);
                    if (!$product) {
                        throw new \Exception("商品 ID {$item['id']} 不存在");
                    }
                    $detail = new ReturnDetail();
                    $detail->return_record_id = $returnRecord->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $quantity;
                    $detail->unit_price = $product->price;
                    $detail->total_amount = $quantity * $detail->unit_price;
                    $detail->total_cost = $quantity * $product->cost_price;
                    $detail->save();
                    $totalAmount += $detail->total_amount;
                    $totalCost += $detail->total_cost;
                }
            }
            $returnRecord->total_amount = $totalAmount;
            $returnRecord->total_cost = $totalCost;
            $returnRecord->save();
            DB::commit();
            return redirect()->route('mobile.returns.index')->with('success', '退货记录修改成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '退货记录修改失败：' . $e->getMessage())->withInput();
        }
    }

    /**
     * 移动端删除退货记录
     */
    public function mobileDestroy($id)
    {
        $returnRecord = ReturnRecord::findOrFail($id);
        
        // 检查权限
        if (!$returnRecord->canDelete()) {
            return back()->with('error', '无权删除此退货记录');
        }

        try {
            DB::beginTransaction();

            // 恢复库存
            foreach ($returnRecord->returnDetails as $detail) {
                $inventory = Inventory::where('store_id', $returnRecord->store_id)
                    ->where('product_id', $detail->product_id)
                    ->first();
                
                if ($inventory) {
                    $inventory->quantity -= $detail->quantity;
                    $inventory->save();
                }
            }

            if ($returnRecord->image_path) {
                Storage::disk('public')->delete($returnRecord->image_path);
            }

            $returnRecord->returnDetails()->delete();
            $returnRecord->delete();

            DB::commit();

            return redirect()->route('mobile.returns.index')
                ->with('success', '退货记录删除成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '退货记录删除失败：' . $e->getMessage());
        }
    }
} 