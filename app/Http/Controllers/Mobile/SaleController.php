<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\BlindBagDelivery;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canAccessMobile()) {
                abort(403, '您没有权限访问移动端功能');
            }
            return $next($request);
        });
    }
    /**
     * 显示销售记录列表
     */
    public function index()
    {
        $user = auth()->user();
        $userStoreIds = $user->stores()->pluck('stores.id')->toArray();
        
        // 使用 DB 查询替代 Eloquent 关系查询
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

        // 处理每个销售记录的created_at字段
        $items = $salesData->items();
        foreach ($items as $item) {
            // 确保 created_at 是 Carbon 实例
            $item->created_at = Carbon::parse($item->created_at);
        }

        // 转换为对象以保持视图兼容性
        $sales = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
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
     * 显示创建销售记录表单
     */
    public function create()
    {
        $user = auth()->user();
        $stores = $user->stores()->where('is_active', true)->get();
        
        // 获取标准商品（非盲袋）
        $standardProducts = Product::where('type', 'standard')
            ->where('is_active', true)
            ->get();
            
        // 获取盲袋商品
        $blindBagProducts = Product::where('type', 'blind_bag')
            ->where('is_active', true)
            ->get();

        return view('mobile.sales.create', compact('stores', 'standardProducts', 'blindBagProducts'));
    }

    /**
     * 保存新的销售记录
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'sales_mode' => 'required|in:standard,blind_bag',
            'standard_products' => 'array',
            'standard_products.*.id' => 'required|exists:products,id',
            'standard_products.*.quantity' => 'required|integer|min:1',
            'blind_bag_products' => 'array',
            'blind_bag_products.*.id' => 'required|exists:products,id',
            'blind_bag_products.*.quantity' => 'required|integer|min:1',
            'blind_bag_delivery' => 'array',
            'blind_bag_delivery.*.product_id' => 'required|exists:products,id',
            'blind_bag_delivery.*.quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 校验用户是否有权限操作该仓库
        if (!auth()->user()->canAccessStore($request->store_id)) {
            return back()->withErrors(['store_id' => '无权限操作该仓库'])->withInput();
        }

        try {
            DB::beginTransaction();

            // 处理图片上传
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('sales', 'public');
            }

            $sale = new Sale();
            $sale->store_id = $request->store_id;
            $sale->customer_name = $request->customer_name;
            $sale->customer_phone = $request->customer_phone;
            $sale->sale_type = $request->sales_mode;
            $sale->image_path = $imagePath;
            $sale->user_id = auth()->id();
            $sale->save();

            $totalAmount = 0;
            $totalCost = 0;

            if ($request->sales_mode === 'standard') {
                // 标品销售模式
                foreach ($request->standard_products as $item) {
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
                    $totalCost += $detail->total_cost;

                    // 扣减库存
                    $product->reduceStock($item['quantity'], $request->store_id);
                }
            } else {
                // 盲袋销售模式
                foreach ($request->blind_bag_products as $item) {
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

                // 保存盲袋发货明细
                foreach ($request->blind_bag_delivery as $delivery) {
                    if ($delivery['quantity'] > 0) {
                        $deliveryProduct = Product::find($delivery['product_id']);
                        $blindBagDeliveryModel = new BlindBagDelivery();
                        $blindBagDeliveryModel->sale_id = $sale->id;
                        $blindBagDeliveryModel->blind_bag_product_id = $request->blind_bag_products[0]['id']; // 取第一个盲袋商品ID
                        $blindBagDeliveryModel->delivery_product_id = $delivery['product_id'];
                        $blindBagDeliveryModel->quantity = $delivery['quantity'];
                        $blindBagDeliveryModel->unit_cost = $deliveryProduct->cost_price;
                        $blindBagDeliveryModel->total_cost = $delivery['quantity'] * $deliveryProduct->cost_price;
                        $blindBagDeliveryModel->save();

                        $totalCost += $blindBagDeliveryModel->total_cost;

                        // 扣减发货商品库存
                        $deliveryProduct->reduceStock($delivery['quantity'], $request->store_id);
                    }
                }
            }

            $sale->total_amount = $totalAmount;
            $sale->total_cost = $totalCost;
            $sale->total_profit = $totalAmount - $totalCost;
            $sale->profit_rate = $totalAmount > 0 ? ($sale->total_profit / $totalAmount) * 100 : 0;
            $sale->save();

            DB::commit();

            return redirect()
                ->route('mobile.sales.index')
                ->with('success', '销售记录创建成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '销售记录创建失败：' . $e->getMessage())->withInput();
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

        return view('mobile.sales.show', compact('sale'));
    }

    /**
     * 显示编辑销售记录表单
     */
    public function edit(Sale $sale)
    {
        $user = auth()->user();
        $stores = $user->stores()->where('is_active', true)->get();
        
        // 获取标准商品（非盲袋）
        $standardProducts = Product::where('type', 'standard')
            ->where('is_active', true)
            ->get();
            
        // 获取盲袋商品
        $blindBagProducts = Product::where('type', 'blind_bag')
            ->where('is_active', true)
            ->get();

        $sale->load(['saleDetails.product', 'blindBagDeliveries.deliveryProduct']);
        
        return view('mobile.sales.edit', compact('sale', 'stores', 'standardProducts', 'blindBagProducts'));
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
                'standard_products' => 'required|array',
                'standard_products.*.id' => 'required|exists:products,id',
                'standard_products.*.quantity' => 'nullable|integer|min:0',
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
                $products = collect($request->input('standard_products', []))->filter(function($item) {
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

            return redirect()->route('mobile.sales.show', $sale)
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
        // 检查权限
        if (!$sale->canDelete()) {
            return back()->with('error', '您没有权限删除此销售记录');
        }

        try {
            DB::beginTransaction();

            // 删除销售明细
            $sale->saleDetails()->delete();
            
            // 删除盲袋发货明细
            $sale->blindBagDeliveries()->delete();
            
            // 删除销售记录
            $sale->delete();

            DB::commit();

            return redirect()
                ->route('mobile.sales.index')
                ->with('success', '销售记录删除成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '删除失败：' . $e->getMessage());
        }
    }
} 