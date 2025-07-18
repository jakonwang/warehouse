<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
    /**
     * 显示销售记录列表
     */
    public function index()
    {
        // 优先用 request('store_id')，否则用 session('current_store_id')
        $storeId = request('store_id', session('current_store_id'));
        $userStoreIds = auth()->user()->getAccessibleStores()->pluck('id')->toArray();

        $query = \App\Models\Sale::with([
            'user:id,real_name',
            'store:id,name'
        ])->whereIn('store_id', $userStoreIds);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(10);

        // 单独查询当天所有销售统计数据（不受分页影响）
        $todayStatsQuery = \App\Models\Sale::whereIn('store_id', $userStoreIds);
        if ($storeId) {
            $todayStatsQuery->where('store_id', $storeId);
        }
        $todayStats = $todayStatsQuery->where('created_at', '>=', today())->get();

        // 计算当天统计数据
        $todaySales = $todayStats->sum('total_amount');
        $todayProfit = $todayStats->sum('total_profit');
        $todayOrders = $todayStats->count();
        $avgProfitRate = $todayStats->count() > 0 ? $todayStats->avg('profit_rate') : 0;

        return view('sales.index', compact('sales', 'todaySales', 'todayProfit', 'todayOrders', 'avgProfitRate'));
    }

    /**
     * 显示创建销售表单
     */
    public function create()
    {
        // 使用 getAccessibleStores() 方法获取用户可访问的仓库
        $stores = auth()->user()->getAccessibleStores()->where('is_active', true);
        if ($stores->isEmpty()) {
            return back()->with('error', '您没有可操作的仓库权限');
        }

        $currentStoreId = request('store_id', session('current_store_id'));
        // “全部仓库”时 currentStoreId 可能为 null 或 0
        $currentStore = ($currentStoreId && $currentStoreId != 0) ? $stores->where('id', $currentStoreId)->first() : null;

        $standardProducts = $currentStore ? $currentStore->availableStandardProducts()->get() : collect();
        $blindBagProducts = $currentStore ? $currentStore->availableBlindBagProducts()->get() : collect();

        return view('sales.create', compact('stores', 'currentStore', 'standardProducts', 'blindBagProducts', 'currentStoreId'));
    }

    /**
     * 保存销售记录
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'sales_mode' => 'required|in:standard,blind_bag',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'remark' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'standard_products' => 'required_if:sales_mode,standard|array',
            'standard_products.*.id' => 'required_if:sales_mode,standard|exists:products,id',
            'standard_products.*.quantity' => 'nullable|integer|min:1',
            'blind_bag_products' => 'required_if:sales_mode,blind_bag|array',
            'blind_bag_products.*.id' => 'required_if:sales_mode,blind_bag|exists:products,id',
            'blind_bag_products.*.quantity' => 'nullable|integer|min:1',
            'blind_bag_delivery' => 'required_if:sales_mode,blind_bag|array',
            'blind_bag_delivery.*.product_id' => 'required_if:sales_mode,blind_bag|exists:products,id',
            'blind_bag_delivery.*.quantity' => 'nullable|integer|min:1',
        ]);

        // 预处理：只保留填写了数量且大于0的商品
        $standardProducts = collect($request->input('standard_products', []))->filter(function($item) {
            return isset($item['quantity']) && $item['quantity'] > 0;
        })->values()->all();
        $blindBagProducts = collect($request->input('blind_bag_products', []))->filter(function($item) {
            return isset($item['quantity']) && $item['quantity'] > 0;
        })->values()->all();
        $blindBagDelivery = collect($request->input('blind_bag_delivery', []))->filter(function($item) {
            return isset($item['quantity']) && $item['quantity'] > 0;
        })->values()->all();

        // 校验用户是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($request->store_id)) {
            return back()->withErrors(['store_id' => '无权限操作该仓库'])->withInput();
        }

        try {
            DB::beginTransaction();

            $sale = new Sale();
            $sale->store_id = $request->store_id;
            $sale->customer_name = $request->customer_name;
            $sale->customer_phone = $request->customer_phone;
            $sale->remark = $request->remark;
            $sale->user_id = auth()->id();
            $sale->sale_type = $request->sales_mode === 'standard' ? Sale::SALE_TYPE_STANDARD : Sale::SALE_TYPE_BLIND_BAG;

            if ($request->hasFile('image')) {
                $sale->image_path = $request->file('image')->store('sales', 'public');
            }

            $sale->save();

            // 自动生成销售单号（如：S202407120001）
            $sale->order_no = 'S' . date('Ymd') . str_pad($sale->id, 4, '0', STR_PAD_LEFT);
            $sale->save();

            $totalAmount = 0;
            $totalCost = 0;

            if ($request->sales_mode === 'standard') {
                // 标品销售模式
                foreach ($standardProducts as $item) {
                    $product = Product::find($item['id']);
                    $detail = new SaleDetail();
                    $detail->sale_id = $sale->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $item['quantity'];
                    $detail->price = $product->price;
                    $detail->cost = $product->cost_price;
                    $detail->cost_price = $product->cost_price;
                    $detail->total = $item['quantity'] * $product->price;
                    $detail->profit = $item['quantity'] * ($product->price - $product->cost_price);
                    $detail->save();

                    $totalAmount += $detail->total;
                    $totalCost += $detail->cost_price;
                    // 扣减库存
                    $product->reduceStock($item['quantity'], $request->store_id);
                }
            } else {
                // 盲袋销售模式
                foreach ($blindBagProducts as $item) {
                    $blindBagProduct = Product::find($item['id']);
                    $detail = new SaleDetail();
                    $detail->sale_id = $sale->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $item['quantity'];
                    $detail->price = $blindBagProduct->price;
                    $detail->cost = 0; // 盲袋主明细成本为0
                    $detail->cost_price = 0;
                    $detail->total = $item['quantity'] * $blindBagProduct->price;
                    $detail->profit = $item['quantity'] * $blindBagProduct->price;
                    $detail->save();

                    $totalAmount += $detail->total;
                }
                // 只保存一次发货明细，数量直接用输入值
                $totalCost = 0;
                $blindBagProductId = isset($blindBagProducts[0]['id']) ? $blindBagProducts[0]['id'] : null;
                foreach ($blindBagDelivery as $delivery) {
                    $deliveryProduct = Product::find($delivery['product_id']);
                    $blindBagDeliveryModel = new \App\Models\BlindBagDelivery();
                    $blindBagDeliveryModel->sale_id = $sale->id;
                    $blindBagDeliveryModel->blind_bag_product_id = $blindBagProductId;
                    $blindBagDeliveryModel->delivery_product_id = $delivery['product_id'];
                    $blindBagDeliveryModel->quantity = $delivery['quantity']; // 直接用输入值
                    $blindBagDeliveryModel->unit_cost = $deliveryProduct->cost_price;
                    $blindBagDeliveryModel->total_cost = $delivery['quantity'] * $deliveryProduct->cost_price;
                    $blindBagDeliveryModel->save();

                    $totalCost += $blindBagDeliveryModel->total_cost;
                    // 扣减库存
                    $deliveryProduct->reduceStock($delivery['quantity'], $request->store_id);
                }
            }

            $sale->total_amount = $totalAmount;
            $sale->total_cost = $totalCost;
            $sale->total_profit = $totalAmount - $totalCost;
            // 修正利润率精度和范围
            $profitRate = $totalAmount > 0 ? (($sale->total_profit / $totalAmount) * 100) : 0;
            $profitRate = max(min(round($profitRate, 2), 999.99), -999.99);
            $sale->profit_rate = $profitRate;
            $sale->save();

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', '销售记录创建成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '销售记录创建失败：' . $e->getMessage());
        }
    }

    /**
     * 显示销售记录详情
     */
    public function show(Sale $sale)
    {
        // 加载关联数据
        $sale->load([
            'user:id,real_name',
            'store:id,name',
            'saleDetails.product',
            'blindBagDeliveries.deliveryProduct'
        ]);

        return view('sales.show', compact('sale'));
    }

    /**
     * 显示编辑销售记录表单
     */
    public function edit(Sale $sale)
    {
        $products = Product::where('is_active', true)->orderBy('sort_order')->get();
        $sale->load(['saleDetails.product', 'blindBagDeliveries.deliveryProduct']);
        return view('sales.edit', compact('sale', 'products'));
    }

    /**
     * 更新销售记录
     */
    public function update(Request $request, Sale $sale)
    {
        // 根据销售类型验证不同的字段
        if ($sale->sale_type === Sale::SALE_TYPE_STANDARD) {
            $request->validate([
                'customer_name' => 'nullable|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'remark' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
                'products' => 'required|array',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'nullable|integer|min:0',
            ]);
        } else {
            $request->validate([
                'customer_name' => 'nullable|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'remark' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
                'blind_bag_delivery' => 'required|array',
                'blind_bag_delivery.*.product_id' => 'required|exists:products,id',
                'blind_bag_delivery.*.quantity' => 'nullable|integer|min:0',
            ]);
        }

        try {
            DB::beginTransaction();

            // 更新基本信息
            $sale->customer_name = $request->customer_name;
            $sale->customer_phone = $request->customer_phone;
            $sale->remark = $request->remark;

            // 处理图片上传
            if ($request->hasFile('image')) {
                if ($sale->image_path) {
                    Storage::disk('public')->delete($sale->image_path);
                }
                $sale->image_path = $request->file('image')->store('sales', 'public');
            }

            $sale->save();

            $totalAmount = $sale->total_amount; // 保持原销售金额
            $totalCost = 0;

            if ($sale->sale_type === Sale::SALE_TYPE_STANDARD) {
                // 标品销售：更新销售明细
                $sale->saleDetails()->delete();

                // 预处理：只保留填写了数量且大于0的商品
                $products = collect($request->input('products', []))->filter(function($item) {
                    return isset($item['quantity']) && $item['quantity'] > 0;
                })->values()->all();

                $totalAmount = 0; // 标品销售需要重新计算销售金额

                foreach ($products as $item) {
                    $product = Product::find($item['id']);
                    $detail = new SaleDetail();
                    $detail->sale_id = $sale->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $item['quantity'];
                    $detail->price = $product->price;
                    $detail->cost = $product->cost_price;
                    $detail->cost_price = $product->cost_price;
                    $detail->total = $item['quantity'] * $product->price;
                    $detail->profit = $item['quantity'] * ($product->price - $product->cost_price);
                    $detail->save();

                    $totalAmount += $detail->total;
                    $totalCost += $detail->quantity * $detail->cost_price;
                }
            } else {
                // 盲袋销售：只更新发货明细，保持销售金额不变
                $sale->blindBagDeliveries()->delete();

                // 预处理：只保留填写了数量且大于0的发货商品
                $deliveries = collect($request->input('blind_bag_delivery', []))->filter(function($item) {
                    return isset($item['quantity']) && $item['quantity'] > 0;
                })->values()->all();

                foreach ($deliveries as $item) {
                    $product = Product::find($item['product_id']);
                    $delivery = new \App\Models\BlindBagDelivery();
                    $delivery->sale_id = $sale->id;
                    $delivery->blind_bag_product_id = $sale->saleDetails->first()->product_id ?? null; // 取第一个盲袋商品ID
                    $delivery->delivery_product_id = $item['product_id'];
                    $delivery->quantity = $item['quantity'];
                    $delivery->unit_cost = $product->cost_price;
                    $delivery->total_cost = $item['quantity'] * $product->cost_price;
                    $delivery->save();

                    $totalCost += $delivery->total_cost;
                }
            }

            // 更新销售统计
            $sale->total_amount = $totalAmount;
            $sale->total_cost = $totalCost;
            $sale->total_profit = $totalAmount - $totalCost;
            // 修正利润率精度和范围
            $profitRate = $totalAmount > 0 ? (($sale->total_profit / $totalAmount) * 100) : 0;
            $profitRate = max(min(round($profitRate, 2), 999.99), -999.99);
            $sale->profit_rate = $profitRate;
            $sale->save();

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', '销售记录更新成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '销售记录更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除销售记录
     */
    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();

            if ($sale->image_path) {
                Storage::disk('public')->delete($sale->image_path);
            }

            $sale->saleDetails()->delete();
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', '销售记录删除成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '销售记录删除失败：' . $e->getMessage());
        }
    }

    /**
     * 实时计算销售数据
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:0'
        ]);

        $totalAmount = 0;
        $totalCost = 0;

        foreach ($validated['products'] as $item) {
            if ($item['quantity'] > 0) {
                $product = Product::find($item['id']);
                $totalAmount += $item['quantity'] * $product->price;
                $totalCost += $item['quantity'] * $product->cost_price;
            }
        }

        $totalProfit = $totalAmount - $totalCost;
        $profitRate = $totalAmount > 0 ? ($totalProfit / $totalAmount) * 100 : 0;

        return response()->json([
            'total_amount' => number_format($totalAmount, 2),
            'total_cost' => number_format($totalCost, 2),
            'total_profit' => number_format($totalProfit, 2),
            'profit_rate' => number_format($profitRate, 2)
        ]);
    }

    /**
     * 显示移动端销售记录列表
     */
    public function mobileIndex()
    {
        // 使用 DB 查询替代 Eloquent 关系查询
        $userStoreIds = auth()->user()->getAccessibleStores()->pluck('id')->toArray();
        
        $salesData = DB::table('sales')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('stores', 'sales.store_id', '=', 'stores.id')
            ->select(
                'sales.*',
                'users.real_name as user_name',
                'stores.name as store_name'
            )
            ->whereIn('sales.store_id', $userStoreIds)
            ->orderBy('sales.created_at', 'desc')
            ->paginate(10);

        // 获取销售详情和盲袋发货信息
        $saleIds = $salesData->pluck('id')->toArray();
        $saleDetails = [];
        $blindBagDeliveries = [];
        
        if (!empty($saleIds)) {
            // 获取销售详情
            $details = DB::table('sale_details')
                ->leftJoin('products', 'sale_details.product_id', '=', 'products.id')
                ->select(
                    'sale_details.*',
                    'products.name as product_name',
                    'products.code as product_code'
                )
                ->whereIn('sale_details.sale_id', $saleIds)
                ->get();
            
            foreach ($details as $detail) {
                $saleDetails[$detail->sale_id][] = $detail;
            }

            // 获取盲袋发货信息
            $deliveries = DB::table('blind_bag_deliveries')
                ->leftJoin('products', 'blind_bag_deliveries.delivery_product_id', '=', 'products.id')
                ->select(
                    'blind_bag_deliveries.*',
                    'products.name as delivery_product_name'
                )
                ->whereIn('blind_bag_deliveries.sale_id', $saleIds)
                ->get();
            
            foreach ($deliveries as $delivery) {
                $blindBagDeliveries[$delivery->sale_id][] = $delivery;
            }
        }

        // 转换为对象以保持视图兼容性
        $sales = new \Illuminate\Pagination\LengthAwarePaginator(
            $salesData->items(),
            $salesData->total(),
            $salesData->perPage(),
            $salesData->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // 为每个记录添加关系数据
        foreach ($sales as $sale) {
            $sale->sale_details = $saleDetails[$sale->id] ?? [];
            $sale->blind_bag_deliveries = $blindBagDeliveries[$sale->id] ?? [];
        }

        return view('mobile.sales.index', compact('sales'));
    }

    /**
     * 显示移动端创建销售记录表单
     */
    public function mobileCreate()
    {
        // 获取用户有权限的仓库
        $stores = auth()->user()->stores()->where('is_active', true)->get();
        if ($stores->isEmpty()) {
            return back()->with('error', '您没有可操作的仓库权限');
        }
        $currentStore = $stores->first();
        // 获取该仓库可销售的商品
        $standardProducts = $currentStore->availableStandardProducts()->get();
        $blindBagProducts = $currentStore->availableBlindBagProducts()->get();
        
        // 为每个商品设置当前仓库ID，用于获取库存
        $standardProducts->each(function($product) use ($currentStore) {
            $product->currentStoreId = $currentStore->id;
        });
        $blindBagProducts->each(function($product) use ($currentStore) {
            $product->currentStoreId = $currentStore->id;
        });
        
        // 组装前端需要的json数组
        $standardProductsArr = $standardProducts->map(function($product) {
            return [
                'id' => $product->id,
                'quantity' => 0,
                'price' => $product->price,
                'cost' => $product->cost_price,
            ];
        });
        $blindBagProductsArr = $blindBagProducts->map(function($product) {
            return [
                'id' => $product->id,
                'quantity' => 0,
                'price' => $product->price,
            ];
        });
        $deliveryProductsArr = $standardProducts->map(function($product) {
            return [
                'id' => $product->id,
                'quantity' => 0,
                'cost' => $product->cost_price,
            ];
        });
        return view('mobile.sales.create', compact(
            'stores', 'currentStore', 'standardProducts', 'blindBagProducts',
            'standardProductsArr', 'blindBagProductsArr', 'deliveryProductsArr'
        ));
    }

    /**
     * 移动端保存销售记录
     */
    public function mobileStore(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'sales_mode' => 'required|in:standard,blind_bag',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'remark' => 'nullable|string',
            'standard_products' => 'required_if:sales_mode,standard|array',
            'standard_products.*.id' => 'required_if:sales_mode,standard|exists:products,id',
            'standard_products.*.quantity' => 'nullable|integer|min:0',
            'blind_bag_products' => 'required_if:sales_mode,blind_bag|array',
            'blind_bag_products.*.id' => 'required_if:sales_mode,blind_bag|exists:products,id',
            'blind_bag_products.*.quantity' => 'nullable|integer|min:0',
            'blind_bag_delivery' => 'required_if:sales_mode,blind_bag|array',
            'blind_bag_delivery.*.product_id' => 'required_if:sales_mode,blind_bag|exists:products,id',
            'blind_bag_delivery.*.quantity' => 'nullable|integer|min:0',
        ]);

        // 校验用户是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($request->store_id)) {
            return back()->withErrors(['store_id' => '无权限操作该仓库'])->withInput();
        }

        // 预处理：只保留填写了数量且大于0的商品
        $standardProducts = collect($request->input('standard_products', []))->filter(function($item) {
            return isset($item['quantity']) && $item['quantity'] > 0;
        })->values()->all();
        $blindBagProducts = collect($request->input('blind_bag_products', []))->filter(function($item) {
            return isset($item['quantity']) && $item['quantity'] > 0;
        })->values()->all();
        $blindBagDelivery = collect($request->input('blind_bag_delivery', []))->filter(function($item) {
            return isset($item['quantity']) && $item['quantity'] > 0;
        })->values()->all();

        // 校验是否有有效的销售数量
        if ($request->sales_mode === 'standard' && empty($standardProducts)) {
            return back()->withErrors(['standard_products' => '请至少输入一个商品的销售数量'])->withInput();
        }
        if ($request->sales_mode === 'blind_bag' && empty($blindBagProducts)) {
            return back()->withErrors(['blind_bag_products' => '请至少输入一个盲袋商品的销售数量'])->withInput();
        }

        try {
            DB::beginTransaction();

            $sale = new Sale();
            $sale->store_id = $request->store_id;
            $sale->customer_name = $request->customer_name;
            $sale->customer_phone = $request->customer_phone;
            $sale->remark = $request->remark;
            $sale->user_id = auth()->id();
            $sale->sale_type = $request->sales_mode === 'standard' ? Sale::SALE_TYPE_STANDARD : Sale::SALE_TYPE_BLIND_BAG;
            $sale->save();

            // 自动生成销售单号（如：S202407120001）
            $sale->order_no = 'S' . date('Ymd') . str_pad($sale->id, 4, '0', STR_PAD_LEFT);
            $sale->save();

            $totalAmount = 0;
            $totalCost = 0;

            if ($request->sales_mode === 'standard') {
                // 标品销售模式
                foreach ($standardProducts as $item) {
                    $product = Product::find($item['id']);
                    $detail = new SaleDetail();
                    $detail->sale_id = $sale->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $item['quantity'];
                    $detail->price = $product->price;
                    $detail->cost = $product->cost_price;
                    $detail->cost_price = $product->cost_price;
                    $detail->total = $item['quantity'] * $product->price;
                    $detail->profit = $item['quantity'] * ($product->price - $product->cost_price);
                    $detail->save();

                    $totalAmount += $detail->total;
                    $totalCost += $detail->cost_price;
                    // 扣减库存
                    $product->reduceStock($item['quantity'], $request->store_id);
                }
            } else {
                // 盲袋销售模式
                foreach ($blindBagProducts as $item) {
                    $blindBagProduct = Product::find($item['id']);
                    $detail = new SaleDetail();
                    $detail->sale_id = $sale->id;
                    $detail->product_id = $item['id'];
                    $detail->quantity = $item['quantity'];
                    $detail->price = $blindBagProduct->price;
                    $detail->cost = 0; // 盲袋主明细成本为0
                    $detail->cost_price = 0;
                    $detail->total = $item['quantity'] * $blindBagProduct->price;
                    $detail->profit = $item['quantity'] * $blindBagProduct->price;
                    $detail->save();

                    $totalAmount += $detail->total;
                }
                // 保存发货明细
                $blindBagProductId = isset($blindBagProducts[0]['id']) ? $blindBagProducts[0]['id'] : null;
                foreach ($blindBagDelivery as $delivery) {
                    $deliveryProduct = Product::find($delivery['product_id']);
                    $blindBagDeliveryModel = new \App\Models\BlindBagDelivery();
                    $blindBagDeliveryModel->sale_id = $sale->id;
                    $blindBagDeliveryModel->blind_bag_product_id = $blindBagProductId;
                    $blindBagDeliveryModel->delivery_product_id = $delivery['product_id'];
                    $blindBagDeliveryModel->quantity = $delivery['quantity'];
                    $blindBagDeliveryModel->unit_cost = $deliveryProduct->cost_price;
                    $blindBagDeliveryModel->total_cost = $delivery['quantity'] * $deliveryProduct->cost_price;
                    $blindBagDeliveryModel->save();

                    $totalCost += $blindBagDeliveryModel->total_cost;
                    // 扣减库存
                    $deliveryProduct->reduceStock($delivery['quantity'], $request->store_id);
                }
            }

            $sale->total_amount = $totalAmount;
            $sale->total_cost = $totalCost;
            $sale->total_profit = $totalAmount - $totalCost;
            // 修正利润率精度和范围
            $profitRate = $totalAmount > 0 ? (($sale->total_profit / $totalAmount) * 100) : 0;
            $profitRate = max(min(round($profitRate, 2), 999.99), -999.99);
            $sale->profit_rate = $profitRate;
            $sale->save();

            DB::commit();

            return redirect()
                ->route('mobile.sales.index')
                ->with('success', '销售记录创建成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '销售记录创建失败：' . $e->getMessage());
        }
    }
} 