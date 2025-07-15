<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\PriceSeries;
use Illuminate\Http\Request;

class InventoryAlertController extends Controller
{
    /**
     * 显示库存预警页面
     */
    public function index()
    {
        // 获取库存预警阈值
        $alertThreshold = config('inventory.alert_threshold', 10);
        $maxThreshold = config('inventory.max_threshold', 100);

        // 获取库存数据
        $inventory = DB::table('inventory')->first();
        if (!$inventory) {
            // 为每个价格系列创建库存记录
            $priceSeries = DB::table('price_series')->select('code')->get();
            foreach ($priceSeries as $series) {
                DB::table('inventory')->insert([
                    'series_code' => $series->code,
                    'quantity' => 0,
                    'min_quantity' => $alertThreshold,
                    'max_quantity' => $maxThreshold,
                ]);
            }
            $inventory = DB::table('inventory')->first();
        }

        // 计算各系列库存状态
        $stockStatus = [];
        $inventories = DB::table('inventory')->select('series_code', 'quantity', 'min_quantity', 'max_quantity')->get();
        
        foreach ($inventories as $inventory) {
            $stockStatus['price_' . $inventory->series_code] = [
                'current' => $inventory->quantity,
                'status' => $this->getStockStatus($inventory->quantity, $inventory->min_quantity, $inventory->max_quantity),
                'suggestion' => $this->getStockSuggestion($inventory->quantity, $inventory->min_quantity, $inventory->max_quantity)
            ];
        }

        return view('inventory.alerts', compact('stockStatus', 'alertThreshold', 'maxThreshold'));
    }

    /**
     * 获取库存状态
     */
    private function getStockStatus($quantity, $alertThreshold, $maxThreshold)
    {
        if ($quantity <= $alertThreshold) {
            return 'danger';
        } elseif ($quantity >= $maxThreshold) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    /**
     * 获取库存建议
     */
    private function getStockSuggestion($quantity, $alertThreshold, $maxThreshold)
    {
        if ($quantity <= $alertThreshold) {
            return '库存不足，建议及时补货';
        } elseif ($quantity >= $maxThreshold) {
            return '库存积压，建议适当促销';
        } else {
            return '库存正常';
        }
    }
} 