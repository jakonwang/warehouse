@extends('layouts.app')

@section('title', __('messages.system_config.title'))
@section('header', __('messages.system_config.title'))

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'basic',
    unsavedChanges: false,
    configs: {
        basic: {
            system_name: '越南盲袋库存管理系统',
            system_logo: '',
            timezone: 'Asia/Ho_Chi_Minh',
            language: 'zh_CN',
            currency: 'VND'
        },
        inventory: {
            low_stock_threshold: 10,
            auto_reorder: false,
            reorder_point: 5,
            backup_frequency: 'daily'
        },
        notification: {
            email_notifications: true,
            sms_notifications: false,
            push_notifications: true,
            notification_email: 'admin@example.com'
        },
        security: {
            session_timeout: 30,
            password_expiry: 90,
            login_attempts: 5,
            two_factor_auth: false
        }
    },
    saveConfig(section) {
        // 模拟保存配置
        this.unsavedChanges = false;
        // 这里应该发送AJAX请求保存配置
        alert('配置已保存');
    }
}">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.system_config.title') }}</h2>
                <p class="text-sm text-gray-600">{{ __('messages.system_config.subtitle') }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <button @click="saveConfig(activeTab)" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-lg font-medium text-white hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200" :class="{ 'opacity-50 cursor-not-allowed': !unsavedChanges }" :disabled="!unsavedChanges">
                    <i class="bi bi-save mr-2"></i>
                    {{ __('messages.system_config.save_configuration') }}
                </button>
                <button class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                    <i class="bi bi-download mr-2"></i>
                    Export Config
                </button>
            </div>
        </div>
    </div>

    <!-- 配置选项卡 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- 选项卡导航 -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'basic'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200" :class="activeTab === 'basic' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <i class="bi bi-gear mr-2"></i>
                    {{ __('messages.system_config.system_params') }}
                </button>
                <button @click="activeTab = 'inventory'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200" :class="activeTab === 'inventory' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <i class="bi bi-archive mr-2"></i>
                    {{ __('messages.system_config.inventory_management') }}
                </button>
                <button @click="activeTab = 'notification'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200" :class="activeTab === 'notification' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <i class="bi bi-bell mr-2"></i>
                    {{ __('messages.system_config.notification_settings') }}
                </button>
                <button @click="activeTab = 'security'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200" :class="activeTab === 'security' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <i class="bi bi-shield-check mr-2"></i>
                    Security Config
                </button>
            </nav>
        </div>

        <!-- 配置内容 -->
        <div class="p-6">
            <!-- 基础配置 -->
            <div x-show="activeTab === 'basic'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.system_config.system_basic_info') }}</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.system_config.system_name') }}</label>
                                <input type="text" x-model="configs.basic.system_name" @input="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.system_config.timezone_setting') }}</label>
                                <select x-model="configs.basic.timezone" @change="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="Asia/Ho_Chi_Minh">{{ __('messages.system_config.vietnam_time') }}</option>
                                    <option value="Asia/Shanghai">{{ __('messages.system_config.china_time') }}</option>
                                    <option value="UTC">{{ __('messages.system_config.utc_time') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.system_config.system_language') }}</label>
                                <select x-model="configs.basic.language" @change="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="zh_CN">{{ __('messages.system_config.simplified_chinese') }}</option>
                                    <option value="en_US">{{ __('messages.system_config.english') }}</option>
                                    <option value="vi_VN">{{ __('messages.system_config.vietnamese') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.system_config.default_currency') }}</label>
                                <select x-model="configs.basic.currency" @change="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="VND">{{ __('messages.system_config.vnd') }}</option>
                                    <option value="CNY">{{ __('messages.system_config.cny_currency') }}</option>
                                    <option value="USD">{{ __('messages.system_config.usd_currency') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.system_config.system_appearance') }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.system_config.system_logo') }}</label>
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                        <i class="bi bi-box-seam text-white text-2xl"></i>
                                    </div>
                                    <div>
                                        <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="bi bi-upload mr-2"></i>
                                            {{ __('messages.system_config.upload_logo') }}
                                        </button>
                                        <p class="text-xs text-gray-500 mt-1">{{ __('messages.system_config.logo_format_tip') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 库存配置 -->
            <div x-show="activeTab === 'inventory'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.system_config.inventory_warning_settings') }}</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.system_config.low_stock_threshold') }}</label>
                                <div class="relative">
                                    <input type="number" x-model="configs.inventory.low_stock_threshold" @input="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">件</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">当库存低于此数量时发出预警</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">自动补货点</label>
                                <div class="relative">
                                    <input type="number" x-model="configs.inventory.reorder_point" @input="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">件</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">触发自动补货的库存数量</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">自动化设置</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900">启用自动补货</h4>
                                    <p class="text-sm text-gray-500">当库存达到补货点时自动生成采购订单</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="configs.inventory.auto_reorder" @change="unsavedChanges = true" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                                            </div>
                                        </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">数据备份</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">备份频率</label>
                                <select x-model="configs.inventory.backup_frequency" @change="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="daily">每日备份</option>
                                    <option value="weekly">每周备份</option>
                                    <option value="monthly">每月备份</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                    <i class="bi bi-database mr-2"></i>
                                    立即备份
                                </button>
                            </div>
                                            </div>
                                        </div>
                                            </div>
                                        </div>

            <!-- 通知配置 -->
            <div x-show="activeTab === 'notification'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">通知方式</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-envelope text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">邮件通知</h4>
                                        <p class="text-sm text-gray-500">通过邮件发送重要通知</p>
                                            </div>
                                        </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="configs.notification.email_notifications" @change="unsavedChanges = true" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-phone text-green-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">短信通知</h4>
                                        <p class="text-sm text-gray-500">通过短信发送紧急通知</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="configs.notification.sms_notifications" @change="unsavedChanges = true" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-bell text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">推送通知</h4>
                                        <p class="text-sm text-gray-500">浏览器推送通知</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="configs.notification.push_notifications" @change="unsavedChanges = true" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">通知设置</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">管理员邮箱</label>
                                <input type="email" x-model="configs.notification.notification_email" @input="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="admin@example.com">
                                <p class="text-sm text-gray-500 mt-1">接收系统通知的邮箱地址</p>
                            </div>
                                            </div>
                                        </div>
                                            </div>
                                        </div>

            <!-- 安全配置 -->
            <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">登录安全</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">会话超时时间</label>
                                <div class="relative">
                                    <input type="number" x-model="configs.security.session_timeout" @input="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">分钟</span>
                                            </div>
                                        </div>
                                <p class="text-sm text-gray-500 mt-1">用户无操作自动退出时间</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">密码有效期</label>
                                <div class="relative">
                                    <input type="number" x-model="configs.security.password_expiry" @input="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">天</span>
                                            </div>
                                        </div>
                                <p class="text-sm text-gray-500 mt-1">密码到期后需要更新</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">最大登录尝试次数</label>
                                <div class="relative">
                                    <input type="number" x-model="configs.security.login_attempts" @input="unsavedChanges = true" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">次</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">超过次数将锁定账户</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">高级安全</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900">双因素认证</h4>
                                    <p class="text-sm text-gray-500">为管理员账户启用双因素认证</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="configs.security.two_factor_auth" @change="unsavedChanges = true" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                                </div>
                            </div>
                        </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-triangle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">安全提醒</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>修改安全配置可能影响系统正常运行，请谨慎操作并确保在维护时间内进行。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 保存提示 -->
    <div x-show="unsavedChanges" x-transition class="fixed bottom-6 right-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 shadow-lg">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="bi bi-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-yellow-800">有未保存的更改</p>
                <p class="text-sm text-yellow-700">请记得保存您的配置更改</p>
            </div>
            <div class="ml-4">
                <button @click="saveConfig(activeTab)" class="text-sm bg-yellow-600 text-white px-3 py-1 rounded-lg hover:bg-yellow-700 transition-colors">
                    立即保存
                </button>
            </div>
        </div>
    </div>
</div>
@endsection 