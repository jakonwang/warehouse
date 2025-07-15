<?php

namespace App\Http\Controllers;

use App\Models\SystemConfig;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SystemConfigController extends Controller
{
    public function index()
    {
        $configs = SystemConfig::all()->pluck('value', 'key')->toArray();
        
        return view('system-configs.index', compact('configs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'low_stock_threshold' => 'required|integer|min:1',
            'auto_restock_threshold' => 'required|integer|min:1',
            'enable_notifications' => 'boolean',
            'enable_email_notifications' => 'boolean',
            'enable_sms_notifications' => 'boolean',
            'business_hours_start' => 'required|date_format:H:i',
            'business_hours_end' => 'required|date_format:H:i',
            'default_currency' => 'required|in:CNY,USD,EUR',
            'min_profit_rate_warning' => 'required|numeric|min:0|max:100',
            'allow_negative_stock' => 'boolean',
            'auto_generate_product_code' => 'boolean',
        ]);

        $configs = [
            'low_stock_threshold' => $request->low_stock_threshold,
            'auto_restock_threshold' => $request->auto_restock_threshold,
            'enable_notifications' => $request->has('enable_notifications') ? 1 : 0,
            'enable_email_notifications' => $request->has('enable_email_notifications') ? 1 : 0,
            'enable_sms_notifications' => $request->has('enable_sms_notifications') ? 1 : 0,
            'business_hours_start' => $request->business_hours_start,
            'business_hours_end' => $request->business_hours_end,
            'default_currency' => $request->default_currency,
            'min_profit_rate_warning' => $request->min_profit_rate_warning,
            'allow_negative_stock' => $request->has('allow_negative_stock') ? 1 : 0,
            'auto_generate_product_code' => $request->has('auto_generate_product_code') ? 1 : 0,
        ];

        foreach ($configs as $key => $value) {
            SystemConfig::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // 清除缓存
        Cache::forget('system_configs');

        return redirect()->route('system-config.index')
            ->with('success', '系统配置已更新');
    }
} 