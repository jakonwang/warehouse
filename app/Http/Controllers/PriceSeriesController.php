<?php

namespace App\Http\Controllers;

use App\Models\PriceSeries;
use Illuminate\Http\Request;

class PriceSeriesController extends Controller
{
    public function index()
    {
        $priceSeries = PriceSeries::where('is_active', true)->orderBy('sort_order')->get();
        return view('price-series.index', compact('priceSeries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:price_series,code',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        PriceSeries::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'price' => $validated['price'],
            'description' => $validated['description'],
            'is_active' => true,
            'sort_order' => (int)$validated['price'],
        ]);

        return redirect()->route('price-series.index')
            ->with('success', '价格系列添加成功');
    }

    public function update(Request $request, PriceSeries $priceSeries)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $priceSeries->update($validated);

        return redirect()->route('price-series.index')
            ->with('success', '价格系列更新成功');
    }

    public function destroy(PriceSeries $priceSeries)
    {
        $priceSeries->delete();

        return redirect()->route('price-series.index')
            ->with('success', '价格系列删除成功');
    }
} 