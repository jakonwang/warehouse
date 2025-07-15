<!DOCTYPE html>
<html lang="zh-CN" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '越南盲袋库存管理系统')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- 本地化的 Tailwind CSS -->
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
    
    <!-- 本地化的 Bootstrap Icons -->
    <link href="{{ asset('assets/bootstrap-icons.css') }}" rel="stylesheet">
    
    <!-- 本地化的 Alpine.js -->
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
    <!-- 简化的布局内容 -->
    <div class="flex h-full">
        <!-- 侧边栏 -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0" 
             :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
            <!-- 侧边栏内容 -->
            <div class="flex items-center justify-between h-16 px-4 bg-gradient-to-r from-indigo-600 to-purple-600">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-gem text-white text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <span class="text-white font-bold text-base">仪表盘</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white p-2 rounded-lg">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>
            
            <!-- 导航菜单 -->
            <nav class="flex-1 p-4 overflow-y-auto">
                @include('layouts.navigation')
            </nav>
        </div>

        <!-- 主内容区域 -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- 顶部导航栏 -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <div class="flex-1 lg:flex-none">
                        <h1 class="text-xl font-semibold text-gray-900">@yield('header', '仪表盘')</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        @include('components.language-switcher')
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">{{ substr(auth()->user()->real_name ?? '管', 0, 1) }}</span>
                                </div>
                                <i class="bi bi-chevron-down text-gray-400 text-sm"></i>
                            </button>
                            <div x-show="userMenuOpen" x-cloak class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-20">
                                <div class="py-1">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-lang key="messages.profile.personal_info"/>
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <x-lang key="messages.auth.logout"/>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- 页面内容 -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>