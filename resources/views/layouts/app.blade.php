<!DOCTYPE html>
<html lang="zh-CN" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '越南盲袋库存管理系统')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- 使用CDN加载Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563EB',
                        secondary: '#059669',
                    }
                }
            }
        }
    </script>
    <link href="{{ asset('assets/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/alpine.min.js') }}" defer></script>
    <style>
        /* 自定义样式 */
        .card {
            @apply bg-white rounded-xl shadow-sm transition-all duration-200;
        }
        .card:hover {
            @apply shadow-md;
        }
        .btn-primary {
            @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-lg transition-all duration-200;
        }
        .btn-primary:hover {
            @apply -translate-y-0.5 shadow-lg shadow-blue-500/20;
        }
        .btn-secondary {
            @apply bg-gray-100 text-gray-700 px-4 py-2 rounded-lg transition-all duration-200;
        }
        .btn-secondary:hover {
            @apply bg-gray-200;
        }
        .table {
            @apply w-full border-collapse;
        }
        .table th {
            @apply bg-gray-50 text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider;
        }
        .table td {
            @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900;
        }
        .badge {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }
        .badge-success {
            @apply bg-green-100 text-green-800;
        }
        .badge-warning {
            @apply bg-yellow-100 text-yellow-800;
        }
        .badge-danger {
            @apply bg-red-100 text-red-800;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full" x-data="{ sidebarOpen: false, currentStore: '李佳琦直播间' }">
    <div class="flex h-full">
        <!-- 移动端侧边栏背景遮罩 -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 lg:hidden">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false"></div>
        </div>

        <!-- 侧边栏 -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 sidebar-compact bg-gradient-to-b from-slate-50 to-white shadow-2xl border-r border-gray-200/50 transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 overflow-hidden flex flex-col"
             :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
             x-show="sidebarOpen || window.innerWidth >= 1024">
            
            <!-- 侧边栏头部 -->
            <div class="flex items-center justify-between h-16 px-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 relative overflow-hidden flex-shrink-0">
                <!-- 背景装饰 -->
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full -translate-x-16 -translate-y-16 animate-pulse"></div>
                    <div class="absolute bottom-0 right-0 w-24 h-24 bg-gradient-to-tr from-white to-transparent rounded-full translate-x-12 translate-y-12"></div>
                </div>
                
                <div class="flex items-center relative z-10">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-lg rounded-xl flex items-center justify-center border border-white/30 shadow-lg">
                        <i class="bi bi-gem text-white text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <span class="text-white font-bold text-base tracking-wide drop-shadow-sm">{{ __("app.system_title") }}</span>
                        <p class="text-white/90 text-xs font-medium">{{ __("app.system_subtitle") }}</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <!-- 仓库切换器 -->
            <div class="p-4 border-b border-gray-100/80 flex-shrink-0">
                <div class="relative" x-data="{ storeDropdownOpen: false }">
                    <button @click="storeDropdownOpen = !storeDropdownOpen" class="w-full flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-xl border border-blue-200/50 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-blue-500 rounded-lg flex items-center justify-center shadow-md">
                                <i class="bi bi-shop text-white text-sm"></i>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-semibold text-gray-800 truncate max-w-32">
                                    {{ isset($currentStore) ? $currentStore->name : __('messages.stores.please_select') }}
                                </p>
                                <p class="text-xs text-gray-500 font-medium"><x-lang key="messages.stores.current_store"/></p>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-gray-400 text-sm transform transition-transform duration-200" :class="{ 'rotate-180': storeDropdownOpen }"></i>
                    </button>
                    <!-- 仓库下拉菜单 -->
                    <div x-show="storeDropdownOpen" x-cloak x-transition class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200/80 rounded-xl shadow-xl z-10 backdrop-blur-sm" style="display: none;">
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('switch-store', 0) }}"
                               class="flex items-center p-3 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 {{ empty($currentStoreId) || $currentStoreId == 0 ? 'bg-blue-50' : '' }}">
                                <div class="w-6 h-6 bg-gradient-to-br from-blue-400 to-purple-500 rounded-lg flex items-center justify-center shadow-sm">
                                    <i class="bi bi-globe text-white text-xs"></i>
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-700">{{ __('messages.stores.all_stores') }}</span>
                            </a>
                        @endif
                        @if(isset($userStores) && $userStores->count())
                            @foreach($userStores as $store)
                                <a href="{{ route('switch-store', $store->id) }}"
                                   class="flex items-center p-3 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 {{ isset($currentStore) && $currentStore && $currentStore->id == $store->id ? 'bg-blue-50' : '' }}">
                                    <div class="w-6 h-6 bg-gradient-to-br from-blue-400 to-purple-500 rounded-lg flex items-center justify-center shadow-sm">
                                        <i class="bi bi-broadcast text-white text-xs"></i>
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-700">{{ $store->name }}</span>
                                </a>
                            @endforeach
                        @else
                            <div class="p-3 text-gray-400 text-sm"><x-lang key="messages.stores.no_available_stores"/></div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 导航菜单 -->
            <nav class="flex-1 p-4 overflow-y-auto scroll-smooth custom-scrollbar" style="max-height: calc(100vh - 140px);">
                @include('layouts.navigation')
            </nav>
        </div>

        <!-- 主内容区域 -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- 顶部导航栏 -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                    <!-- 移动端菜单按钮 -->
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="bi bi-list text-xl"></i>
                    </button>

                    <!-- 页面标题 -->
                    <div class="flex-1 lg:flex-none">
                        <h1 class="text-xl font-semibold text-gray-900">@yield('header', __('messages.dashboard.title'))</h1>
                    </div>

                    <!-- 右侧工具栏 -->
                    <div class="flex items-center space-x-4">
                        <!-- 移动端入口卡片（右上角，target=_blank） -->
                        <a href="{{ route('mobile.dashboard') }}" target="_blank" class="flex items-center px-4 py-2 rounded-xl bg-gradient-to-r from-orange-100 to-red-100 text-orange-700 font-semibold shadow hover:from-orange-200 hover:to-red-200 transition-all duration-200">
                            <i class="bi bi-phone text-xl mr-2"></i>
                            {{ __('mobile.title') }}
                            <i class="bi bi-phone-fill text-lg ml-2"></i>
                        </a>
                        <!-- 语言切换器 -->
                        @include('components.language-switcher')

                        <!-- 通知 -->
                        <div class="relative" x-data="{ notificationOpen: false }">
                            <button @click="notificationOpen = !notificationOpen" class="relative p-2 text-gray-500 hover:text-gray-700 transition-colors">
                                <i class="bi bi-bell text-lg"></i>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                            
                            <!-- 通知下拉菜单 -->
                            <div x-show="notificationOpen" x-cloak x-transition @click.away="notificationOpen = false" class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-20" style="display: none;">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-sm font-medium text-gray-900"><x-lang key="messages.notifications.latest_notifications"/></h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <a href="#" class="block p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start">
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <i class="bi bi-exclamation-triangle text-red-600 text-sm"></i>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm text-gray-900"><x-lang key="messages.inventory.stock_alert"/></p>
                                                <p class="text-xs text-gray-500">盲袋A库存不足，仅剩5个</p>
                                                <p class="text-xs text-gray-400 mt-1">2分钟前</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- 用户菜单 -->
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">{{ substr(auth()->user()->real_name ?? '管', 0, 1) }}</span>
                                </div>
                                <i class="bi bi-chevron-down text-gray-400 text-sm"></i>
                            </button>
                            
                            <!-- 用户下拉菜单 -->
                            <div x-show="userMenuOpen" x-cloak x-transition @click.away="userMenuOpen = false" class="absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-xl z-20 overflow-hidden" style="display: none;">
                                <a href="{{ route('profile.edit') }}" class="flex items-center p-4 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                    <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-100 group-hover:bg-blue-200 transition-colors">
                                        <i class="bi bi-person text-blue-600 text-sm"></i>
                                    </div>
                                    <span class="ml-3 font-medium text-gray-700 group-hover:text-blue-700"><x-lang key="messages.profile.personal_info"/></span>
                                </a>
                                <a href="#" class="flex items-center p-4 hover:bg-gradient-to-r hover:from-slate-50 hover:to-gray-50 transition-all duration-200 group">
                                    <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 group-hover:bg-slate-200 transition-colors">
                                        <i class="bi bi-gear text-slate-600 text-sm"></i>
                                    </div>
                                    <span class="ml-3 font-medium text-gray-700 group-hover:text-slate-700"><x-lang key="messages.system_config.title"/></span>
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center p-4 hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 transition-all duration-200 text-left group">
                                        <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-100 group-hover:bg-red-200 transition-colors">
                                            <i class="bi bi-box-arrow-right text-red-600 text-sm"></i>
                                        </div>
                                        <span class="ml-3 font-medium text-red-600 group-hover:text-red-700">{{ __("messages.logout") }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- 主要内容 -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <!-- 页面内容 -->
                <div class="p-4 lg:p-6">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="bi bi-check-circle text-green-600 mr-3"></i>
                                <span class="text-green-800">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="bi bi-exclamation-triangle text-red-600 mr-3"></i>
                                <span class="text-red-800">{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
    
    <style>
        /* Alpine.js cloak - 防止页面加载时闪烁 */
        [x-cloak] { 
            display: none !important; 
        }
        
        /* 自定义滚动条 */
        ::-webkit-scrollbar {
            width: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 2px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* 侧边栏滚动条样式 */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
            margin: 8px 0;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #e5e7eb 0%, #d1d5db 100%);
            border-radius: 4px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #d1d5db 0%, #9ca3af 100%);
            opacity: 1;
        }
        
        /* 菜单项动画和效果 */
        .menu-glow {
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }
        
        /* 玻璃态效果 */
        .glass-effect {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        
        /* 渐变边框动画 */
        @keyframes gradient-border {
            0% { border-image: linear-gradient(45deg, #f59e0b, #ef4444, #8b5cf6, #06b6d4) 1; }
            25% { border-image: linear-gradient(45deg, #ef4444, #8b5cf6, #06b6d4, #f59e0b) 1; }
            50% { border-image: linear-gradient(45deg, #8b5cf6, #06b6d4, #f59e0b, #ef4444) 1; }
            75% { border-image: linear-gradient(45deg, #06b6d4, #f59e0b, #ef4444, #8b5cf6) 1; }
            100% { border-image: linear-gradient(45deg, #f59e0b, #ef4444, #8b5cf6, #06b6d4) 1; }
        }
        
        /* 脉冲动画 */
        @keyframes soft-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* 响应式调整 */
        @media (max-width: 1024px) {
            .sidebar-compact {
                width: 16rem !important; /* 64 的替代 */
            }
        }
        
        /* 悬停阴影效果 */
        .hover-shadow {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-shadow:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</body>
</html> 