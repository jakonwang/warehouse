{{-- 主要导航菜单 --}}
<div class="space-y-1.5">
    {{-- 仪表盘 --}}
    <a href="{{ route('dashboard') }}" class="group flex items-center p-2.5 rounded-lg transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg transform scale-105' : 'text-gray-800 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-indigo-700 hover:shadow-md' }}">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('dashboard') ? 'bg-white/20 backdrop-blur-sm shadow-inner' : 'bg-gradient-to-br from-blue-100 to-indigo-100 group-hover:from-indigo-200 group-hover:to-purple-200' }} transition-all duration-300">
            <i class="bi bi-speedometer2 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-indigo-600 group-hover:text-indigo-700' }} text-base"></i>
        </div>
        <span class="ml-3 font-semibold text-sm">{{ __('navigation.dashboard') }}</span>
        @if(request()->routeIs('dashboard'))
            <div class="ml-auto flex items-center">
                <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></div>
            </div>
        @endif
    </a>

    {{-- 库存管理 --}}
    @if(auth()->user()->canManageInventory() || auth()->user()->canViewReports())
    <div x-data="{ inventoryOpen: {{ request()->routeIs('inventory.*', 'products.*', 'categories.*', 'stock-ins.*', 'stock-outs.*', 'stores.*') ? 'true' : 'false' }} }" class="space-y-1">
        <button @click="inventoryOpen = !inventoryOpen" class="group w-full flex items-center justify-between p-2.5 rounded-lg transition-all duration-300 {{ request()->routeIs('inventory.*', 'products.*', 'categories.*', 'stock-ins.*', 'stock-outs.*', 'stores.*') ? 'bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 text-white shadow-lg transform scale-105' : 'text-gray-800 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 hover:text-emerald-700 hover:shadow-md' }}">
            <div class="flex items-center">
                <div class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('inventory.*', 'products.*', 'categories.*', 'stock-ins.*', 'stock-outs.*', 'stores.*') ? 'bg-white/20 backdrop-blur-sm shadow-inner' : 'bg-gradient-to-br from-emerald-100 to-green-100 group-hover:from-emerald-200 group-hover:to-green-200' }} transition-all duration-300">
                    <i class="bi bi-box-seam {{ request()->routeIs('inventory.*', 'products.*', 'categories.*', 'stock-ins.*', 'stock-outs.*', 'stores.*') ? 'text-white' : 'text-emerald-600 group-hover:text-emerald-700' }} text-base"></i>
                </div>
                <span class="ml-3 font-semibold text-sm">{{ __('navigation.inventory_management') }}</span>
            </div>
            <i class="bi bi-chevron-down transform transition-all duration-300 text-sm" :class="{ 'rotate-180': inventoryOpen }"></i>
        </button>
        <div x-show="inventoryOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="ml-5 mt-1.5 space-y-1 border-l-2 border-emerald-100 pl-3.5" style="display: {{ request()->routeIs('inventory.*', 'products.*', 'categories.*', 'stock-ins.*', 'stock-outs.*', 'stores.*') ? 'block' : 'none' }};">
            @if(auth()->user()->canManageInventory() || auth()->user()->canViewReports())
            <a href="{{ route('inventory.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group {{ request()->routeIs('inventory.index') ? 'bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-700 shadow-md' : 'text-gray-600 hover:text-blue-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('inventory.index') ? '!bg-blue-500 shadow-md' : 'bg-gray-100 group-hover:bg-blue-100' }} transition-all duration-200">
                    <i class="bi bi-search {{ request()->routeIs('inventory.index') ? '!text-white' : 'text-gray-500 group-hover:text-blue-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.inventory_query') }}</span>
            </a>
            @endif
            @if(auth()->user()->canManageInventory())
            <a href="{{ route('stock-ins.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 transition-all duration-200 group {{ request()->routeIs('stock-ins.*') ? 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 shadow-md' : 'text-gray-600 hover:text-green-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('stock-ins.*') ? '!bg-green-500 shadow-md' : 'bg-gray-100 group-hover:bg-green-100' }} transition-all duration-200">
                    <i class="bi bi-box-arrow-in-down {{ request()->routeIs('stock-ins.*') ? '!text-white' : 'text-gray-500 group-hover:text-green-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.stock_in_management') }}</span>
            </a>
            @endif
            @if(auth()->user()->canViewProducts())
            <a href="{{ route('products.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition-all duration-200 group {{ request()->routeIs('products.*') ? 'bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 shadow-md' : 'text-gray-600 hover:text-purple-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('products.*') ? '!bg-purple-500 shadow-md' : 'bg-gray-100 group-hover:bg-purple-100' }} transition-all duration-200">
                    <i class="bi bi-gift {{ request()->routeIs('products.*') ? '!text-white' : 'text-gray-500 group-hover:text-purple-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.product_management') }}</span>
            </a>
            @endif
            <a href="{{ route('categories.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 transition-all duration-200 group {{ request()->routeIs('categories.*') ? 'bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-700 shadow-md' : 'text-gray-600 hover:text-indigo-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('categories.*') ? '!bg-indigo-500 shadow-md' : 'bg-gray-100 group-hover:bg-indigo-100' }} transition-all duration-200">
                    <i class="bi bi-tags {{ request()->routeIs('categories.*') ? '!text-white' : 'text-gray-500 group-hover:text-indigo-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.category_management') }}</span>
            </a>
            @if(auth()->user()->canManageStores())
            <a href="{{ route('stores.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 transition-all duration-200 group {{ request()->routeIs('stores.*') ? 'bg-gradient-to-r from-emerald-100 to-green-100 text-emerald-700 shadow-md' : 'text-gray-600 hover:text-emerald-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('stores.*') ? '!bg-emerald-500 shadow-md' : 'bg-gray-100 group-hover:bg-emerald-100' }} transition-all duration-200">
                    <i class="bi bi-building {{ request()->routeIs('stores.*') ? '!text-white' : 'text-gray-500 group-hover:text-emerald-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.store_management') }}</span>
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- 销售管理 --}}
    @if(auth()->user()->canManageSales() || auth()->user()->canViewReports())
    <div x-data="{ salesOpen: {{ request()->routeIs('sales.*', 'returns.*') ? 'true' : 'false' }} }" class="space-y-1">
        <button @click="salesOpen = !salesOpen" class="group w-full flex items-center justify-between p-2.5 rounded-lg transition-all duration-300 {{ request()->routeIs('sales.*', 'returns.*') ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white !text-white shadow-lg transform scale-105' : 'text-gray-800 hover:bg-gradient-to-r hover:from-violet-50 hover:to-purple-50 hover:text-violet-700 hover:shadow-md' }}">
            <div class="flex items-center">
                <div class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('sales.*', 'returns.*') ? 'bg-white/20 backdrop-blur-sm shadow-inner' : 'bg-gradient-to-br from-violet-100 to-purple-100 group-hover:from-violet-200 group-hover:to-purple-200' }} transition-all duration-300">
                    <i class="bi bi-cart3 {{ request()->routeIs('sales.*', 'returns.*') ? 'text-white' : 'text-violet-600 group-hover:text-violet-700' }} text-base"></i>
                </div>
                <span class="ml-3 font-semibold text-sm">{{ __('navigation.sales_management') }}</span>
            </div>
            <i class="bi bi-chevron-down transform transition-all duration-300 text-sm" :class="{ 'rotate-180': salesOpen }"></i>
        </button>
        <div x-show="salesOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="ml-5 mt-1.5 space-y-1 border-l-2 border-violet-100 pl-3.5" style="display: none;">
            @if(auth()->user()->canManageSales() || auth()->user()->canViewReports())
            <a href="{{ route('sales.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group {{ request()->routeIs('sales.index') ? 'bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-700 shadow-md' : 'text-gray-600 hover:text-blue-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('sales.index') ? '!bg-blue-500 shadow-md' : 'bg-gray-100 group-hover:bg-blue-100' }} transition-all duration-200">
                    <i class="bi bi-receipt {{ request()->routeIs('sales.index') ? '!text-white' : 'text-gray-500 group-hover:text-blue-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.sales_records') }}</span>
            </a>
            @endif
            @if(auth()->user()->canManageSales())
            <a href="{{ route('sales.create') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 transition-all duration-200 group {{ request()->routeIs('sales.create') ? 'bg-gradient-to-r from-emerald-100 to-green-100 text-emerald-700 shadow-md' : 'text-gray-600 hover:text-emerald-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('sales.create') ? '!bg-emerald-500 shadow-md' : 'bg-gray-100 group-hover:bg-emerald-100' }} transition-all duration-200">
                    <i class="bi bi-plus-circle {{ request()->routeIs('sales.create') ? '!text-white' : 'text-gray-500 group-hover:text-emerald-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.new_sale') }}</span>
            </a>
            @endif
            @if(auth()->user()->canManageInventory())
            <a href="{{ route('returns.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-orange-50 hover:to-amber-50 transition-all duration-200 group {{ request()->routeIs('returns.*') ? 'bg-gradient-to-r from-orange-100 to-amber-100 text-orange-700 shadow-md' : 'text-gray-600 hover:text-orange-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('returns.*') ? '!bg-orange-500 shadow-md' : 'bg-gray-100 group-hover:bg-orange-100' }} transition-all duration-200">
                    <i class="bi bi-arrow-return-left {{ request()->routeIs('returns.*') ? '!text-white' : 'text-gray-500 group-hover:text-orange-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.return_management') }}</span>
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- 数据统计 --}}
    @if(auth()->user()->canViewReports())
    <div x-data="{ analyticsOpen: {{ request()->routeIs('statistics.*') ? 'true' : 'false' }} }" class="space-y-1">
        <button @click="analyticsOpen = !analyticsOpen" class="group w-full flex items-center justify-between p-2.5 rounded-lg transition-all duration-300 {{ request()->routeIs('statistics.*') ? 'bg-gradient-to-r from-cyan-500 via-blue-500 to-indigo-500 text-white shadow-lg transform scale-105' : 'text-gray-800 hover:bg-gradient-to-r hover:from-cyan-50 hover:to-blue-50 hover:text-cyan-700 hover:shadow-md' }}">
            <div class="flex items-center">
                <div class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('statistics.*') ? 'bg-white/20 backdrop-blur-sm shadow-inner' : 'bg-gradient-to-br from-cyan-100 to-blue-100 group-hover:from-cyan-200 group-hover:to-blue-200' }} transition-all duration-300">
                    <i class="bi bi-graph-up-arrow {{ request()->routeIs('statistics.*') ? 'text-white' : 'text-cyan-600 group-hover:text-cyan-700' }} text-base"></i>
                </div>
                <span class="ml-3 font-semibold text-sm">{{ __('navigation.data_statistics') }}</span>
            </div>
            <i class="bi bi-chevron-down transform transition-all duration-300 text-sm" :class="{ 'rotate-180': analyticsOpen }"></i>
        </button>
        <div x-show="analyticsOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="ml-5 mt-1.5 space-y-1 border-l-2 border-cyan-100 pl-3.5" style="display: none;">
            <a href="{{ route('statistics.sales') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-cyan-50 hover:to-blue-50 transition-all duration-200 group {{ request()->routeIs('statistics.sales') ? 'bg-gradient-to-r from-cyan-100 to-blue-100 text-cyan-700 shadow-md' : 'text-gray-600 hover:text-cyan-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('statistics.sales') ? '!bg-cyan-500 shadow-md' : 'bg-gray-100 group-hover:bg-cyan-100' }} transition-all duration-200">
                    <i class="bi bi-bar-chart {{ request()->routeIs('statistics.sales') ? '!text-white' : 'text-gray-500 group-hover:text-cyan-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.sales_report') }}</span>
            </a>
            <!-- 新增：仓库健康度评估菜单项 -->
            <a href="{{ route('statistics.health') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 transition-all duration-200 group {{ request()->routeIs('statistics.health') ? 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 shadow-md' : 'text-gray-600 hover:text-green-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('statistics.health') ? '!bg-green-500 shadow-md' : 'bg-gray-100 group-hover:bg-green-100' }} transition-all duration-200">
                    <i class="bi bi-activity {{ request()->routeIs('statistics.health') ? '!text-white' : 'text-gray-500 group-hover:text-green-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.health') }}</span>
            </a>
        </div>
    </div>
    @endif

    {{-- 用户管理 --}}
    @if(auth()->user()->canManageUsers())
    <a href="{{ route('users.index') }}" class="group flex items-center p-2.5 rounded-lg transition-all duration-300 {{ request()->routeIs('users.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg transform scale-105' : 'text-gray-800 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-700 hover:shadow-md' }}">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('users.*') ? 'bg-white/20 backdrop-blur-sm shadow-inner' : 'bg-gradient-to-br from-blue-100 to-indigo-100 group-hover:from-blue-200 group-hover:to-indigo-200' }} transition-all duration-300">
            <i class="bi bi-people-fill {{ request()->routeIs('users.*') ? 'text-white' : 'text-blue-600 group-hover:text-blue-700' }} text-base"></i>
        </div>
        <span class="ml-3 font-semibold text-sm">{{ __('navigation.user_management') }}</span>
        @if(request()->routeIs('users.*'))
            <div class="ml-auto flex items-center">
                <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></div>
            </div>
        @endif
    </a>
    @endif

    {{-- 系统设置 --}}
    @if(auth()->user()->canManageSystemConfig() || auth()->user()->isSuperAdmin())
    <div x-data="{ settingsOpen: {{ request()->routeIs('system-config.*', 'backup.*', 'system-monitor.*', 'activity-logs.*') ? 'true' : 'false' }} }" class="space-y-1">
        <button @click="settingsOpen = !settingsOpen" class="group w-full flex items-center justify-between p-2.5 rounded-lg transition-all duration-300 {{ request()->routeIs('system-config.*', 'backup.*', 'system-monitor.*', 'activity-logs.*') ? 'bg-gradient-to-r from-gray-600 to-gray-700 text-white !text-white shadow-lg transform scale-105' : 'text-gray-800 hover:bg-gradient-to-r hover:from-slate-50 hover:to-gray-50 hover:text-slate-700 hover:shadow-md' }}">
            <div class="flex items-center">
                <div class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('system-config.*', 'backup.*', 'system-monitor.*', 'activity-logs.*') ? 'bg-white/20 backdrop-blur-sm shadow-inner' : 'bg-gradient-to-br from-slate-100 to-gray-100 group-hover:from-slate-200 group-hover:to-gray-200' }} transition-all duration-300">
                    <i class="bi bi-gear-fill {{ request()->routeIs('system-config.*', 'backup.*', 'system-monitor.*', 'activity-logs.*') ? 'text-white' : 'text-slate-600 group-hover:text-slate-700' }} text-base"></i>
                </div>
                <span class="ml-3 font-semibold text-sm">{{ __('navigation.system_settings') }}</span>
            </div>
            <i class="bi bi-chevron-down transform transition-all duration-300 text-sm" :class="{ 'rotate-180': settingsOpen }"></i>
        </button>
        <div x-show="settingsOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="ml-5 mt-1.5 space-y-1 border-l-2 border-slate-100 pl-3.5" style="display: {{ request()->routeIs('system-config.*', 'backup.*', 'system-monitor.*', 'activity-logs.*') ? 'block' : 'none' }};">
            @if(auth()->user()->canManageSystemConfig())
            <a href="{{ route('system-config.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-violet-50 hover:to-purple-50 transition-all duration-200 group {{ request()->routeIs('system-config.*') ? 'bg-gradient-to-r from-violet-100 to-purple-100 text-violet-700 shadow-md' : 'text-gray-600 hover:text-violet-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('system-config.*') ? '!bg-violet-500 shadow-md' : 'bg-gray-100 group-hover:bg-violet-100' }} transition-all duration-200">
                    <i class="bi bi-sliders {{ request()->routeIs('system-config.*') ? '!text-white' : 'text-gray-500 group-hover:text-violet-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.system_config') }}</span>
            </a>
            @endif
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('backup.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-orange-50 hover:to-amber-50 transition-all duration-200 group {{ request()->routeIs('backup.*') ? 'bg-gradient-to-r from-orange-100 to-amber-100 text-orange-700 shadow-md' : 'text-gray-600 hover:text-orange-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('backup.*') ? '!bg-orange-500 shadow-md' : 'bg-gray-100 group-hover:bg-orange-100' }} transition-all duration-200">
                    <i class="bi bi-archive {{ request()->routeIs('backup.*') ? '!text-white' : 'text-gray-500 group-hover:text-orange-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.backup') }}</span>
            </a>
            <a href="{{ route('system-monitor.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 transition-all duration-200 group {{ request()->routeIs('system-monitor.*') ? 'bg-gradient-to-r from-red-100 to-pink-100 text-red-700 shadow-md' : 'text-gray-600 hover:text-red-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('system-monitor.*') ? '!bg-red-500 shadow-md' : 'bg-gray-100 group-hover:bg-red-100' }} transition-all duration-200">
                    <i class="bi bi-cpu {{ request()->routeIs('system-monitor.*') ? '!text-white' : 'text-gray-500 group-hover:text-red-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.monitor') }}</span>
            </a>
            {{-- 活动日志 --}}
            <a href="{{ route('activity-logs.index') }}" class="flex items-center p-2.5 rounded-md hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition-all duration-200 group {{ request()->routeIs('activity-logs.*') ? 'bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 shadow-md' : 'text-gray-600 hover:text-purple-700' }}">
                <div class="w-7 h-7 flex items-center justify-center rounded-md {{ request()->routeIs('activity-logs.*') ? '!bg-purple-500 shadow-md' : 'bg-gray-100 group-hover:bg-purple-100' }} transition-all duration-200">
                    <i class="bi bi-journal-text {{ request()->routeIs('activity-logs.*') ? '!text-white' : 'text-gray-500 group-hover:text-purple-600' }} text-sm"></i>
                </div>
                <span class="ml-2.5 font-semibold text-sm">{{ __('navigation.activity_logs') }}</span>
            </a>
            @endif
        </div>
    </div>
    @endif
</div> 