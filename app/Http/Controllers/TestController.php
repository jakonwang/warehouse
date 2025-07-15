<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class TestController extends Controller
{
    public function testLanguage()
    {
        return view('debug-language');
    }
    
    public function testInventory()
    {
        // 使用简化的查询，避免复杂的关系加载
        $inventory = \App\Models\Inventory::with(['product:id,name,code', 'store:id,name'])
            ->whereHas('product', function($query) {
                $query->where('type', 'standard');
            })
            ->take(5)
            ->get();
            
        return view('test-inventory', compact('inventory'));
    }
} 