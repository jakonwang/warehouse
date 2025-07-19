<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryRecord;
use App\Models\Product;
use App\Models\Store;
use App\Models\InventoryCheckRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canManageInventory()) {
                abort(403, '您没有权限管理库存');
            }
            return $next($request);
        });
    }
    /**
     * 显示库存列表
     */
    public function index()
    {
        $currentStoreId = session('current_store_id');
        $user = auth()->user();
        $query = Inventory::with(['product:id,name,code,image,cost_price', 'store:id,name'])
            ->whereHas('product', function($query) {
                $query->where('type', 'standard');
            });
        if ($currentStoreId && $currentStoreId != 0) {
            $query->where('store_id', $currentStoreId);
        } elseif (!$user->isSuperAdmin()) {
            $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
            $query->whereIn('store_id', $userStoreIds);
        }
        $inventory = $query->orderBy('product_id')->paginate(10);
        
        // 计算库存周转率（基于过去30天数据）
        $turnoverRate = $this->calculateTurnoverRate($currentStoreId, $user);
        
        return view('inventory.index', compact('inventory', 'turnoverRate'));
    }
    
    /**
     * 计算库存周转率
     */
    private function calculateTurnoverRate($currentStoreId, $user)
    {
        try {
            $thirtyDaysAgo = now()->subDays(30);
            
            // 构建销售查询
            $salesQuery = \App\Models\Sale::where('created_at', '>=', $thirtyDaysAgo);
            
            // 构建库存查询  
            $inventoryQuery = Inventory::whereHas('product', function($query) {
                $query->where('type', 'standard');
            });
            
            // 应用仓库权限
            if ($currentStoreId && $currentStoreId != 0) {
                $salesQuery->where('store_id', $currentStoreId);
                $inventoryQuery->where('store_id', $currentStoreId);
            } elseif (!$user->isSuperAdmin()) {
                $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
                $salesQuery->whereIn('store_id', $userStoreIds);
                $inventoryQuery->whereIn('store_id', $userStoreIds);
            }
            
            // 计算过去30天的销售成本
            $totalSalesCost = $salesQuery->sum('total_cost') ?? 0;
            
            // 计算平均库存成本
            $averageInventoryCost = $inventoryQuery->get()->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            // 计算周转率（月周转率）
            if ($averageInventoryCost > 0) {
                $turnoverRate = ($totalSalesCost / $averageInventoryCost);
                return round($turnoverRate, 1);
            }
            
            return 0;
        } catch (\Exception $e) {
            \Log::error('库存周转率计算失败: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * 显示创建库存表单
     */
    public function create()
    {
        $products = Product::where('is_active', true)
            ->where('type', 'standard')
            ->orderBy('name')
            ->get();
        
        // 使用用户有权限的仓库
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true);
        
        return view('inventory.create', compact('products', 'stores'));
    }

    /**
     * 存储新创建的库存
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'max_quantity' => 'required|integer|min:0|gte:min_quantity',
            'remark' => 'nullable|string',
        ]);

        // 检查是否已存在该商品在该仓库的库存记录
        $existingInventory = Inventory::where('product_id', $request->product_id)
            ->where('store_id', $request->store_id)
            ->first();

        if ($existingInventory) {
            return back()->with('error', '该商品在此仓库中已存在库存记录！');
        }

        try {
            DB::beginTransaction();

            // 创建库存记录
            $inventory = Inventory::create([
                'product_id' => $request->product_id,
                'store_id' => $request->store_id,
                'quantity' => $request->quantity,
                'min_quantity' => $request->min_quantity,
                'max_quantity' => $request->max_quantity,
                'remark' => $request->remark,
                'last_check_date' => now(),
            ]);

            // 如果有初始库存，创建入库记录
            if ($request->quantity > 0) {
                $product = Product::find($request->product_id);
                $unitCost = $product->cost_price ?? 0;
                
                InventoryRecord::create([
                    'inventory_id' => $inventory->id,
                    'quantity' => $request->quantity,
                    'unit_price' => $unitCost,
                    'total_amount' => $request->quantity * $unitCost,
                    'type' => 'in',
                    'reference_type' => 'inventory',
                    'reference_id' => $inventory->id,
                    'note' => '初始库存',
                ]);
            }

            DB::commit();

            return redirect()->route('inventory.index')
                ->with('success', '库存记录创建成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '创建库存记录失败：' . $e->getMessage());
        }
    }

    /**
     * 显示指定库存的详细信息
     */
    public function show(Inventory $inventory)
    {
        return view('inventory.show', compact('inventory'));
    }

    /**
     * 显示编辑库存表单
     */
    public function edit(Inventory $inventory)
    {
        $inventory->load(['product', 'store']);
        return view('inventory.edit', compact('inventory'));
    }

    /**
     * 更新库存
     */
    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'min_quantity' => 'required|integer|min:0',
            'max_quantity' => 'required|integer|min:0|gte:min_quantity',
            'remark' => 'nullable|string',
        ]);

        $inventory->update($request->only(['min_quantity', 'max_quantity', 'remark']));

        return redirect()->route('inventory.index')
            ->with('success', '库存设置更新成功！');
    }

    /**
     * 删除指定库存
     */
    public function destroy(Inventory $inventory)
    {
        try {
            DB::beginTransaction();
            
            // 删除相关的库存记录
            $inventory->records()->delete();
            
            // 删除库存记录
            $inventory->delete();
            
            DB::commit();
            
            return redirect()->route('inventory.index')
                ->with('success', '库存记录删除成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '删除库存记录失败：' . $e->getMessage());
        }
    }

    public function mobileIndex()
    {
        // 只获取标准商品
        $products = Product::where('is_active', true)
            ->where('type', 'standard')
            ->orderBy('sort_order')
            ->get();
        
        // 获取用户有权限的仓库的库存数据（只包含标准商品）
        $storeIds = auth()->user()->getAccessibleStores()->pluck('id')->toArray();
        $inventory = Inventory::with(['product:id,name,code,image', 'store:id,name'])
            ->whereIn('store_id', $storeIds)
            ->whereHas('product', function($query) {
                $query->where('type', 'standard');
            })
            ->get();
        
        return view('mobile.inventory.index', compact('inventory', 'products'));
    }

    /**
     * 显示库存盘点页面
     */
    public function check(Request $request)
    {
        $checkDate = $request->input('check_date', now()->format('Y-m-d'));
        $currentStoreId = session('current_store_id');
        $user = auth()->user();
        
        $query = Inventory::with(['product:id,name,code,image'])
            ->whereHas('product', function($query) {
                $query->where('type', 'standard');
            });
            
        // 根据当前选择的仓库筛选
        if ($currentStoreId && $currentStoreId != 0) {
            $query->where('store_id', $currentStoreId);
        } elseif (!$user->isSuperAdmin()) {
            // 如果不是超级管理员，只显示用户有权限的仓库
            $userStoreIds = $user->getAccessibleStores()->pluck('id')->toArray();
            $query->whereIn('store_id', $userStoreIds);
        }
        
        $inventory = $query->orderBy('product_id')->paginate(10);

        return view('inventory.check', compact('inventory', 'checkDate'));
    }

    /**
     * 更新库存盘点结果
     */
    public function updateCheck(Request $request)
    {
        $request->validate([
            'inventory' => 'required|array',
            'inventory.*.id' => 'required|exists:inventory,id',
            'inventory.*.quantity' => 'required|integer|min:0',
            'check_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // 获取当前选择的仓库ID
            $currentStoreId = session('current_store_id');
            if (!$currentStoreId || $currentStoreId == 0) {
                // 如果没有选择仓库，使用用户的第一个仓库
                $userStore = auth()->user()->stores()->first();
                $currentStoreId = $userStore ? $userStore->id : 1;
            }

            // 创建盘点记录
            $checkRecord = InventoryCheckRecord::create([
                'store_id' => $currentStoreId,
                'user_id' => auth()->id(),
                'status' => 'pending',
                'remark' => '库存盘点调整 - ' . $request->check_date,
            ]);

            foreach ($request->inventory as $item) {
                $inventory = Inventory::find($item['id']);
                $oldQuantity = $inventory->quantity;
                $newQuantity = $item['quantity'];
                $difference = $newQuantity - $oldQuantity;
                
                // 更新库存数量
                $inventory->quantity = $newQuantity;
                $inventory->last_check_date = $request->check_date;
                $inventory->save();

                // 创建盘点明细
                $unitCost = $inventory->product->cost_price ?? 0; // 如果成本价为null，使用0
                $checkDetail = new \App\Models\InventoryCheckDetail();
                $checkDetail->inventory_check_record_id = $checkRecord->id;
                $checkDetail->product_id = $inventory->product_id;
                $checkDetail->system_quantity = $oldQuantity;
                $checkDetail->actual_quantity = $newQuantity;
                $checkDetail->difference = $difference;
                $checkDetail->unit_cost = $unitCost;
                $checkDetail->total_cost = $difference * $unitCost;
                $checkDetail->save();

                // 记录库存变动
                if ($oldQuantity != $newQuantity) {
                    InventoryRecord::create([
                        'inventory_id' => $inventory->id,
                        'quantity' => $difference,
                        'unit_price' => $unitCost,
                        'total_amount' => $difference * $unitCost,
                        'type' => 'check',
                        'reference_type' => 'inventory',
                        'reference_id' => $inventory->id,
                        'note' => '库存盘点调整',
                        'created_at' => $request->check_date,
                        'updated_at' => $request->check_date,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('inventory.index')
                ->with('success', '库存盘点完成！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '库存盘点失败：' . $e->getMessage());
        }
    }

    /**
     * 显示库存盘点历史记录
     */
    public function checkHistory(Request $request)
    {
        $checkDate = $request->input('check_date');
        
        $query = InventoryCheckRecord::with(['inventory.product', 'creator'])
            ->when($checkDate, function ($query) use ($checkDate) {
                return $query->whereDate('check_date', $checkDate);
            })
            ->orderBy('check_date', 'desc')
            ->orderBy('created_at', 'desc');

        $records = $query->paginate(10);

        return view('inventory.check-history', compact('records', 'checkDate'));
    }

    /**
     * 显示低库存预警页面
     */
    public function lowStock()
    {
        // 获取用户有权限的仓库
        $userStores = auth()->user()->stores()->pluck('stores.id')->toArray();
        
        // 使用 Eloquent 查询但优化关系加载
        $inventory = Inventory::with(['product:id,name,code,image,cost_price', 'store:id,name'])
            ->whereIn('store_id', $userStores)
            ->where('quantity', '<', DB::raw('min_quantity'))
            ->whereHas('product', function($query) {
                $query->where('type', 'standard');
            })
            ->paginate(15);
            
        // 获取统计数据
        $statsData = Inventory::with('product:id,cost_price')
            ->whereIn('store_id', $userStores)
            ->where('quantity', '<', DB::raw('min_quantity'))
            ->whereHas('product', function($query) {
                $query->where('type', 'standard');
            })
            ->get();
            
        $stats = [
            'total' => $statsData->count(),
            'out_of_stock' => $statsData->where('quantity', 0)->count(),
            'low_stock' => $statsData->where('quantity', '>', 0)->count(),
            'total_value' => $statsData->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            }),
        ];
            
        return view('inventory.low-stock', compact('inventory', 'stats'));
    }

    /**
     * 快速调整库存
     */
    public function quickAdjust(Request $request, Inventory $inventory)
    {
        $request->validate([
            'adjust_quantity' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $change = (int)$request->input('adjust_quantity');
        $reason = $request->input('reason', '快速调整');

        // 变更库存
        $oldQuantity = $inventory->quantity;
        $inventory->quantity += $change;
        if ($inventory->quantity < 0) {
            $inventory->quantity = 0;
        }
        $inventory->save();

        // 记录库存变更
        $inventory->records()->create([
            'quantity' => $change,
            'unit_price' => 0,
            'total_amount' => 0,
            'type' => 'adjust',
            'reference_type' => null,
            'reference_id' => null,
            'note' => $reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => '库存调整成功',
            'old_quantity' => $oldQuantity,
            'new_quantity' => $inventory->quantity,
        ]);
    }

    /**
     * 单个库存盘点
     */
    public function singleCheck(Request $request, Inventory $inventory)
    {
        $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oldQuantity = $inventory->quantity;
            $newQuantity = $request->new_quantity;
            $difference = $newQuantity - $oldQuantity;

            // 1. 创建盘点主表
            $record = \App\Models\InventoryCheckRecord::create([
                'store_id' => $inventory->store_id,
                'user_id' => auth()->id(),
                'status' => 'pending',
                'remark' => $request->reason ?: '单条库存盘点',
            ]);

            // 2. 更新库存数量
            $inventory->quantity = $newQuantity;
            $inventory->last_check_date = now();
            $inventory->save();

            // 3. 创建盘点明细
            $unitCost = $inventory->product->cost_price ?? 0;
            $checkDetail = new \App\Models\InventoryCheckDetail();
            $checkDetail->inventory_check_record_id = $record->id;
            $checkDetail->product_id = $inventory->product_id;
            $checkDetail->system_quantity = $oldQuantity;
            $checkDetail->actual_quantity = $newQuantity;
            $checkDetail->difference = $difference;
            $checkDetail->unit_cost = $unitCost;
            $checkDetail->total_cost = $difference * $unitCost;
            $checkDetail->save();

            // 4. 记录库存变动
            if ($oldQuantity != $newQuantity) {
                \App\Models\InventoryRecord::create([
                    'inventory_id' => $inventory->id,
                    'quantity' => $difference,
                    'unit_price' => $unitCost,
                    'total_amount' => $difference * $unitCost,
                    'type' => 'check',
                    'reference_type' => 'inventory',
                    'reference_id' => $inventory->id,
                    'note' => $request->reason ?: '库存盘点调整',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '库存盘点完成！'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '库存盘点失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 批量操作
     */
    public function batchOperation(Request $request)
    {
        $request->validate([
            'action' => 'required|in:batch_quick_adjust,batch_check,batch_delete',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:inventory,id',
        ]);

        try {
            DB::beginTransaction();

            switch ($request->action) {
                case 'batch_quick_adjust':
                    foreach ($request->items as $item) {
                        $inventory = Inventory::find($item['id']);
                        $oldQuantity = $inventory->quantity;
                        $adjustQuantity = $item['quantity'];
                        $newQuantity = $oldQuantity + $adjustQuantity;
                        
                        if ($newQuantity < 0) {
                            throw new \Exception("库存不能为负数");
                        }
                        
                        $inventory->quantity = $newQuantity;
                        $inventory->save();

                        // 记录库存变动
                        if ($adjustQuantity != 0) {
                            $unitCost = $inventory->product->cost_price ?? 0;
                            InventoryRecord::create([
                                'inventory_id' => $inventory->id,
                                'quantity' => $adjustQuantity,
                                'unit_price' => $unitCost,
                                'total_amount' => $adjustQuantity * $unitCost,
                                'type' => 'adjust',
                                'reference_type' => 'inventory',
                                'reference_id' => $inventory->id,
                                'note' => $item['reason'] ?: '批量快速调整',
                            ]);
                        }
                    }
                    break;

                case 'batch_check':
                    foreach ($request->items as $item) {
                        $inventory = Inventory::find($item['id']);
                        $oldQuantity = $inventory->quantity;
                        $newQuantity = $item['quantity'];
                        $difference = $newQuantity - $oldQuantity;
                        
                        $inventory->quantity = $newQuantity;
                        $inventory->last_check_date = now();
                        $inventory->save();

                        // 记录库存变动
                        if ($oldQuantity != $newQuantity) {
                            $unitCost = $inventory->product->cost_price ?? 0;
                            InventoryRecord::create([
                                'inventory_id' => $inventory->id,
                                'quantity' => $difference,
                                'unit_price' => $unitCost,
                                'total_amount' => $difference * $unitCost,
                                'type' => 'check',
                                'reference_type' => 'inventory',
                                'reference_id' => $inventory->id,
                                'note' => $item['reason'] ?: '批量盘点调整',
                            ]);
                        }
                    }
                    break;

                case 'batch_delete':
                    foreach ($request->items as $item) {
                        $inventory = Inventory::find($item['id']);
                        // 删除相关的库存记录
                        $inventory->records()->delete();
                        // 删除库存记录
                        $inventory->delete();
                    }
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '批量操作完成！'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '批量操作失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 导出库存数据
     */
    public function export(Request $request)
    {
        try {
            // 获取筛选参数
            $keyword = $request->input('keyword');
            $status = $request->input('status');
            $minQuantity = $request->input('min_quantity');
            $maxQuantity = $request->input('max_quantity');
            $storeId = $request->input('store_id');

            // 构建查询
            $query = DB::table('inventory')
                ->join('products', 'inventory.product_id', '=', 'products.id')
                ->join('stores', 'inventory.store_id', '=', 'stores.id')
                ->select(
                    'inventory.*',
                    'products.name as product_name',
                    'products.code as product_code',
                    'products.type as product_type',
                    'stores.name as store_name'
                );

            // 应用筛选条件
            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('products.name', 'like', "%{$keyword}%")
                      ->orWhere('products.code', 'like', "%{$keyword}%");
                });
            }

            if ($status) {
                switch ($status) {
                    case 'low':
                        $query->where('inventory.quantity', '<=', 'inventory.min_quantity');
                        break;
                    case 'out':
                        $query->where('inventory.quantity', 0);
                        break;
                    case 'normal':
                        $query->where('inventory.quantity', '>', 'inventory.min_quantity');
                        break;
                }
            }

            if ($minQuantity !== null) {
                $query->where('inventory.quantity', '>=', $minQuantity);
            }

            if ($maxQuantity !== null) {
                $query->where('inventory.quantity', '<=', $maxQuantity);
            }

            if ($storeId) {
                $query->where('inventory.store_id', $storeId);
            }

            // 获取数据
            $data = $query->orderBy('inventory.quantity', 'asc')->get();

            // 生成CSV内容
            $csvContent = $this->generateInventoryCSV($data);

            // 生成文件名
            $filename = 'inventory_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

            // 返回CSV下载
            return Response::make($csvContent, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            return back()->with('error', '导出失败：' . $e->getMessage());
        }
    }

    /**
     * 生成库存CSV内容
     */
    private function generateInventoryCSV($data)
    {
        // CSV头部
        $headers = [
            '商品名称',
            '商品编码',
            '商品类型',
            '当前库存',
            '最低库存',
            '最高库存',
            '库存状态',
            '最后入库时间',
            '最后出库时间',
            '仓库名称',
            '备注'
        ];

        $csv = $this->arrayToCsv($headers);

        // 数据行
        foreach ($data as $row) {
            $status = '';
            if ($row->quantity <= $row->min_quantity) {
                $status = '库存不足';
            } elseif ($row->quantity == 0) {
                $status = '无库存';
            } else {
                $status = '库存充足';
            }

            $csvRow = [
                $row->product_name ?? '未知商品',
                $row->product_code ?? '未知编码',
                $row->product_type == 'standard' ? '标品' : '盲袋',
                $row->quantity ?? 0,
                $row->min_quantity ?? 0,
                $row->max_quantity ?? 0,
                $status,
                $row->last_stock_in_at ?? '无记录',
                $row->last_stock_out_at ?? '无记录',
                $row->store_name ?? '未知仓库',
                $row->remark ?? ''
            ];

            $csv .= $this->arrayToCsv($csvRow);
        }

        return $csv;
    }

    /**
     * 数组转CSV格式
     */
    private function arrayToCsv($array)
    {
        $csv = '';
        foreach ($array as $value) {
            // 处理包含逗号、引号或换行符的值
            $value = str_replace('"', '""', $value);
            if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
                $value = '"' . $value . '"';
            }
            $csv .= $value . ',';
        }
        return rtrim($csv, ',') . "\n";
    }
} 