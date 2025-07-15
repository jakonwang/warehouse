<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\BlindBagSale;
use App\Models\BlindBagDetail;
use App\Models\BlindBagDelivery;
use App\Models\Inventory;
use App\Models\PriceSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BlindBagSaleController extends Controller
{
    /**
     * 显示盲袋销售页面
     */
    public function create()
    {
        // 获取所有盲袋商品
        $blindBagProducts = Product::where('type', Product::TYPE_BLIND_BAG)
            ->where('is_active', true)
            ->get();

        // 获取所有可发货的标准商品（有库存的）
        $availableProducts = Product::where('type', Product::TYPE_STANDARD)
            ->where('is_active', true)
            ->get()
            ->map(function ($product) {
                $product->stock = $product->getStockInStore(session('current_store_id'));
                return $product;
            })
            ->filter(function ($product) {
                return $product->stock > 0; // 只显示有库存的
            });

        return view('mobile.blind-bag.create', compact('blindBagProducts', 'availableProducts'));
    }

    /**
     * 处理盲袋销售
     */
    public function store(Request $request)
    {
        $request->validate([
            'blind_bag_product_id' => 'required|exists:products,id',
            'delivery_content' => 'required|array',
            'delivery_content.*' => 'integer|min:0',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'sale_amount' => 'required|numeric|min:0',
            'total_cost' => 'required|numeric|min:0',
            'profit' => 'required|numeric'
        ]);

        try {
            DB::beginTransaction();

            // 验证发货内容不为空
            $deliveryItems = array_filter($request->delivery_content, function($quantity) {
                return $quantity > 0;
            });

            if (empty($deliveryItems)) {
                throw new \Exception('请选择至少一种发货商品');
            }

            // 验证库存是否充足
            foreach ($deliveryItems as $productId => $quantity) {
                $product = Product::find($productId);
                if (!$product || !$product->hasEnoughStock($quantity, session('current_store_id'))) {
                    $currentStock = $product ? $product->getStockInStore(session('current_store_id')) : 0;
                    throw new \Exception("{$product->name} 库存不足，当前库存: {$currentStock}");
                }
            }

            // 处理照片上传
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('blind-bag-sales', 'public');
            }

            // 创建主销售记录
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'sale_type' => Sale::SALE_TYPE_BLIND_BAG,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'image_path' => $photoPath,
                'total_amount' => $request->sale_amount,
                'total_cost' => $request->total_cost,
                'total_profit' => $request->profit,
                'profit_rate' => $request->sale_amount > 0 ? ($request->profit / $request->sale_amount) * 100 : 0,
                'remark' => '盲袋销售'
            ]);

            // 创建盲袋销售记录
            $blindBagProduct = Product::find($request->blind_bag_product_id);
            $blindBagSale = BlindBagSale::create([
                'sale_id' => $sale->id,
                'product_id' => $blindBagProduct->id,
                'quantity' => 1, // 假设一次销售一个盲袋
                'unit_price' => $blindBagProduct->price,
                'total_amount' => $blindBagProduct->price,
                'total_cost' => $request->total_cost,
                'profit' => $request->profit,
                'profit_rate' => $request->sale_amount > 0 ? ($request->profit / $request->sale_amount) * 100 : 0,
                'remark' => '主播手动选择发货内容'
            ]);

            // 记录实际发货明细并扣减库存
            foreach ($deliveryItems as $productId => $quantity) {
                $product = Product::find($productId);
                
                // 创建发货明细（使用新的BlindBagDelivery模型）
                BlindBagDelivery::create([
                    'sale_id' => $sale->id,
                    'blind_bag_product_id' => $blindBagProduct->id,
                    'delivery_product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $product->cost_price,
                    'total_cost' => $quantity * $product->cost_price,
                    'remark' => '主播手动选择发货内容'
                ]);

                // 扣减库存
                $product->reduceStock($quantity, session('current_store_id'));
            }

            DB::commit();

            return redirect()
                ->route('mobile.sales.index')
                ->with('success', '盲袋销售记录已创建，发货内容已记录');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', '盲袋销售失败：' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * 获取商品库存信息（AJAX）
     */
    public function getProductStock(Request $request)
    {
        $productId = $request->get('product_id');
        $product = Product::find($productId);
        
        return response()->json([
            'stock' => $product ? $product->getStockInStore(session('current_store_id')) : 0
        ]);
    }

    /**
     * 实时计算盲袋成本和利润（AJAX）
     */
    public function calculateProfit(Request $request)
    {
        $blindBagPrice = floatval($request->get('blind_bag_price', 0));
        $deliveryContent = $request->get('delivery_content', []);
        
        $totalCost = 0;
        $deliverySummary = [];
        
        foreach ($deliveryContent as $productId => $quantity) {
            if ($quantity > 0) {
                $product = Product::find($productId);
                if ($product) {
                    $itemCost = $quantity * $product->cost_price;
                    $totalCost += $itemCost;
                    $deliverySummary[] = "{$product->name} x{$quantity}";
                }
            }
        }
        
        $profit = $blindBagPrice - $totalCost;
        $profitRate = $blindBagPrice > 0 ? ($profit / $blindBagPrice) * 100 : 0;
        
        return response()->json([
            'sale_amount' => number_format($blindBagPrice, 2),
            'total_cost' => number_format($totalCost, 2),
            'profit' => number_format($profit, 2),
            'profit_rate' => number_format($profitRate, 2),
            'delivery_summary' => implode(', ', $deliverySummary),
            'can_submit' => !empty($deliverySummary) && $blindBagPrice > 0
        ]);
    }
}
