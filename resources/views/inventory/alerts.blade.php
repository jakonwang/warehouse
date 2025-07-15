@extends('layouts.app')

@section('title', __('messages.inventory.stock_alert'))
@section('header', __('messages.inventory.stock_alert'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 via-orange-50 to-yellow-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 页面标题 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2"><x-lang key="messages.inventory.smart_stock_alert"/></h1>
            <p class="text-gray-600"><x-lang key="messages.inventory.stock_alert_description"/></p>
        </div>
    </div>
</div>
@endsection 