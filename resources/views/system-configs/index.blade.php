@extends('layouts.app')

@section('title', __('messages.system_config.title'))

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 页面标题 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><x-lang key="messages.system_config.title"/></h1>
            <p class="mt-2 text-gray-600"><x-lang key="messages.system_config.subtitle"/></p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- 系统参数配置 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900"><x-lang key="messages.system_config.system_params"/></h3>
                <p class="mt-1 text-sm text-gray-600"><x-lang key="messages.system_config.system_params_desc"/></p>
            </div>
            
            <form action="{{ route('system-config.update') }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- 库存管理 -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2"><x-lang key="messages.system_config.inventory_management"/></h4>
                        
                        <!-- 库存预警阈值 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.system_config.low_stock_threshold"/></label>
                            <div class="relative">
                                <input type="number" 
                                       name="low_stock_threshold" 
                                       value="{{ $configs['low_stock_threshold'] ?? 10 }}" 
                                       min="1" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <span class="text-gray-500 text-sm"><x-lang key="messages.system_config.pieces"/></span>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500"><x-lang key="messages.system_config.low_stock_threshold_desc"/></p>
                        </div>

                        <!-- 自动补货阈值 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.system_config.auto_restock_threshold"/></label>
                            <div class="relative">
                                <input type="number" 
                                       name="auto_restock_threshold" 
                                       value="{{ $configs['auto_restock_threshold'] ?? 5 }}" 
                                       min="1" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <span class="text-gray-500 text-sm"><x-lang key="messages.system_config.pieces"/></span>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500"><x-lang key="messages.system_config.auto_restock_threshold_desc"/></p>
                        </div>
                    </div>

                    <!-- 通知设置 -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2"><x-lang key="messages.system_config.notification_settings"/></h4>
                        
                        <!-- 启用通知 -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="enable_notifications" 
                                       value="1"
                                       {{ (isset($configs['enable_notifications']) && $configs['enable_notifications']) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm font-medium text-gray-700"><x-lang key="messages.system_config.enable_notifications"/></span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500"><x-lang key="messages.system_config.enable_notifications_desc"/></p>
                        </div>

                        <!-- 邮件通知 -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="enable_email_notifications" 
                                       value="1"
                                       {{ (isset($configs['enable_email_notifications']) && $configs['enable_email_notifications']) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm font-medium text-gray-700"><x-lang key="messages.system_config.enable_email_notifications"/></span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500"><x-lang key="messages.system_config.enable_email_notifications_desc"/></p>
                        </div>

                        <!-- 短信通知 -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="enable_sms_notifications" 
                                       value="1"
                                       {{ (isset($configs['enable_sms_notifications']) && $configs['enable_sms_notifications']) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm font-medium text-gray-700"><x-lang key="messages.system_config.enable_sms_notifications"/></span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500"><x-lang key="messages.system_config.enable_sms_notifications_desc"/></p>
                        </div>
                    </div>

                    <!-- 营业设置 -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2"><x-lang key="messages.system_config.business_settings"/></h4>
                        
                        <!-- 营业时间 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.system_config.business_hours"/></label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1"><x-lang key="messages.system_config.start_time"/></label>
                                    <input type="time" 
                                           name="business_hours_start" 
                                           value="{{ $configs['business_hours_start'] ?? '09:00' }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1"><x-lang key="messages.system_config.end_time"/></label>
                                    <input type="time" 
                                           name="business_hours_end" 
                                           value="{{ $configs['business_hours_end'] ?? '18:00' }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- 货币设置 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.system_config.default_currency"/></label>
                            <select name="default_currency" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="CNY" {{ (isset($configs['default_currency']) && $configs['default_currency'] === 'CNY') ? 'selected' : '' }}><x-lang key="messages.system_config.cny"/></option>
                                <option value="USD" {{ (isset($configs['default_currency']) && $configs['default_currency'] === 'USD') ? 'selected' : '' }}><x-lang key="messages.system_config.usd"/></option>
                                <option value="EUR" {{ (isset($configs['default_currency']) && $configs['default_currency'] === 'EUR') ? 'selected' : '' }}><x-lang key="messages.system_config.eur"/></option>
                            </select>
                        </div>
                    </div>

                    <!-- 业务规则 -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2"><x-lang key="messages.system_config.business_rules"/></h4>
                        
                        <!-- 最低利润率警告 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.system_config.min_profit_rate_warning"/></label>
                            <div class="relative">
                                <input type="number" 
                                       name="min_profit_rate_warning" 
                                       value="{{ $configs['min_profit_rate_warning'] ?? 20 }}" 
                                       min="0" 
                                       max="100"
                                       step="0.1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <span class="text-gray-500 text-sm">%</span>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500"><x-lang key="messages.system_config.min_profit_rate_warning_desc"/></p>
                        </div>

                        <!-- 允许负库存 -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="allow_negative_stock" 
                                       value="1"
                                       {{ (isset($configs['allow_negative_stock']) && $configs['allow_negative_stock']) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm font-medium text-gray-700"><x-lang key="messages.system_config.allow_negative_stock"/></span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500"><x-lang key="messages.system_config.allow_negative_stock_desc"/></p>
                        </div>

                        <!-- 自动生成商品编码 -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="auto_generate_product_code" 
                                       value="1"
                                       {{ (isset($configs['auto_generate_product_code']) && $configs['auto_generate_product_code']) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm font-medium text-gray-700"><x-lang key="messages.system_config.auto_generate_product_code"/></span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500"><x-lang key="messages.system_config.auto_generate_product_code_desc"/></p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <i class="bi bi-check-circle mr-2"></i><x-lang key="messages.save"/>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 系统信息 -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="messages.system_info"/></h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-2"><x-lang key="messages.laravel_version"/></h4>
                    <p class="text-xl font-bold text-blue-600">{{ app()->version() }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-green-900 mb-2"><x-lang key="messages.php_version"/></h4>
                    <p class="text-xl font-bold text-green-600">{{ PHP_VERSION }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-purple-900 mb-2"><x-lang key="messages.server_time"/></h4>
                    <p class="text-xl font-bold text-purple-600">{{ now()->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>

        <!-- 友情提示 -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="bi bi-info-circle text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800"><x-lang key="messages.system_config.config_tips"/></h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>商品价格和成本现在可以直接在 <a href="{{ route('products.index') }}" class="underline font-medium"><x-lang key="messages.nav.products"/></a> 页面进行设置</li>
                            <li><x-lang key="messages.system_config.low_stock_threshold_affect"/></li>
                            <li><x-lang key="messages.system_config.business_hours_affect"/></li>
                            <li><x-lang key="messages.system_config.config_effective_immediately"/></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 