<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '盲袋库存管理系统')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap Icons -->
    <link href="{{ asset('assets/bootstrap-icons.css') }}" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="{{ asset('assets/alpine.min.js') }}"></script>

    <style>
        :root {
            --primary: #6366F1;
            --primary-dark: #4F46E5;
            --secondary: #10B981;
            --accent: #F59E0B;
            --background: #F8FAFC;
            --surface: #FFFFFF;
            --text: #1E293B;
            --text-light: #64748B;
            --border: #E2E8F0;
            --success: #10B981;
            --error: #EF4444;
            --warning: #F59E0B;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--text);
            -webkit-tap-highlight-color: transparent;
            font-family: 'Figtree', sans-serif;
        }

        .nav-blur {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .bottom-nav {
            background: transparent;
        }

        .nav-item {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            padding: 8px 12px;
        }

        .nav-item:hover {
            background: rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }

        .nav-item.active {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }

        .nav-icon {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item:hover .nav-icon {
            transform: translateY(-3px) scale(1.1);
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            border-radius: 12px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            border-radius: 12px;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #D97706);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            border-radius: 12px;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }

        .form-input {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .form-label {
            color: var(--text-light);
            font-weight: 500;
        }

        .badge {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            font-weight: 500;
            border-radius: 8px;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        /* 自定义滚动条 */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.3);
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.5);
        }

        /* 移动端优化 */
        @media (max-width: 640px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        /* 动画效果 */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* 浮动效果 */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* 渐变文字 */
        .gradient-text {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* 安全区域支持 */
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* 玻璃拟态效果增强 */
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* 现代按钮样式 */
        .modern-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen">
        <!-- 顶部导航栏 -->
        <nav class="nav-blur fixed top-0 left-0 right-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('mobile.dashboard') }}" class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                <i class="bi bi-box-seam text-white text-lg"></i>
                            </div>
                            <span class="text-lg font-bold gradient-text">{{ config('app.name') }}</span>
                        </a>
                    </div>

                    <!-- 用户菜单 -->
                    <div class="flex items-center space-x-4">
                        <!-- 语言切换按钮 -->
                        <div class="flex items-center space-x-1 mr-2">
                            @php $currentLang = app()->getLocale(); @endphp
                            <a href="?lang=zh_CN" class="px-2 py-1 rounded text-xs font-bold {{ $currentLang=='zh_CN' ? 'bg-indigo-600 text-white' : 'text-gray-500 bg-white' }}">中</a>
                            <a href="?lang=en" class="px-2 py-1 rounded text-xs font-bold {{ $currentLang=='en' ? 'bg-indigo-600 text-white' : 'text-gray-500 bg-white' }}">EN</a>
                            <a href="?lang=vi" class="px-2 py-1 rounded text-xs font-bold {{ $currentLang=='vi' ? 'bg-indigo-600 text-white' : 'text-gray-500 bg-white' }}">VI</a>
                        </div>
                        <!-- 通知图标 -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors">
                            <i class="bi bi-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                        </button>
                        <!-- 用户头像 -->
                        <div class="flex items-center space-x-3">
                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-sm">{{ substr(Auth::user()->real_name ?? Auth::user()->username, 0, 1) }}</span>
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->real_name ?? Auth::user()->username }}</p>
                                <p class="text-xs text-gray-500">在线</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- 页面内容 -->
        <main class="pt-16 pb-80 pb-safe">
            <div class="animate-fade-in-up">
                @yield('content')
            </div>
        </main>

        <!-- 极简底部导航栏 -->
        <nav class="fixed bottom-0 left-0 right-0 z-50 h-16 px-2 pb-safe">
            <div class="max-w-lg mx-auto bg-white rounded-t-2xl shadow-xl flex justify-around items-center h-full border-t border-gray-200">
                <a href="{{ route('mobile.dashboard') }}" class="flex flex-col items-center justify-center flex-1 h-full transition-all {{ request()->routeIs('mobile.dashboard') ? 'text-indigo-600 font-bold' : 'text-gray-400' }}">
                    <i class="bi bi-house text-xl {{ request()->routeIs('mobile.dashboard') ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                    <span class="text-xs mt-0.5 {{ request()->routeIs('mobile.dashboard') ? 'text-indigo-600 font-bold' : 'text-gray-500' }}">首页</span>
                    @if(request()->routeIs('mobile.dashboard'))
                        <span class="block w-1 h-1 bg-indigo-500 rounded-full mt-0.5"></span>
                    @endif
                </a>
                <a href="{{ route('mobile.sales.index') }}" class="flex flex-col items-center justify-center flex-1 h-full transition-all {{ request()->routeIs('mobile.sales.*') && !request()->routeIs('mobile.dashboard') ? 'text-indigo-600 font-bold' : 'text-gray-400' }}">
                    <i class="bi bi-cart3 text-xl {{ request()->routeIs('mobile.sales.*') && !request()->routeIs('mobile.dashboard') ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                    <span class="text-xs mt-0.5 {{ request()->routeIs('mobile.sales.*') && !request()->routeIs('mobile.dashboard') ? 'text-indigo-600 font-bold' : 'text-gray-500' }}">销售</span>
                    @if(request()->routeIs('mobile.sales.*') && !request()->routeIs('mobile.dashboard'))
                        <span class="block w-1 h-1 bg-indigo-500 rounded-full mt-0.5"></span>
                    @endif
                </a>
                <a href="{{ route('mobile.inventory.index') }}" class="flex flex-col items-center justify-center flex-1 h-full transition-all {{ request()->routeIs('mobile.inventory.*') ? 'text-indigo-600 font-bold' : 'text-gray-400' }}">
                    <i class="bi bi-archive text-xl {{ request()->routeIs('mobile.inventory.*') ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                    <span class="text-xs mt-0.5 {{ request()->routeIs('mobile.inventory.*') ? 'text-indigo-600 font-bold' : 'text-gray-500' }}">库存</span>
                    @if(request()->routeIs('mobile.inventory.*'))
                        <span class="block w-1 h-1 bg-indigo-500 rounded-full mt-0.5"></span>
                    @endif
                </a>
                <a href="{{ route('mobile.stock-in.index') }}" class="flex flex-col items-center justify-center flex-1 h-full transition-all {{ request()->routeIs('mobile.stock-in.*') ? 'text-indigo-600 font-bold' : 'text-gray-400' }}">
                    <i class="bi bi-box-arrow-in-down text-xl {{ request()->routeIs('mobile.stock-in.*') ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                    <span class="text-xs mt-0.5 {{ request()->routeIs('mobile.stock-in.*') ? 'text-indigo-600 font-bold' : 'text-gray-500' }}">入库</span>
                    @if(request()->routeIs('mobile.stock-in.*'))
                        <span class="block w-1 h-1 bg-indigo-500 rounded-full mt-0.5"></span>
                    @endif
                </a>
                <a href="{{ route('mobile.returns.index') }}" class="flex flex-col items-center justify-center flex-1 h-full transition-all {{ request()->routeIs('mobile.returns.*') ? 'text-indigo-600 font-bold' : 'text-gray-400' }}">
                    <i class="bi bi-arrow-return-left text-xl {{ request()->routeIs('mobile.returns.*') ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                    <span class="text-xs mt-0.5 {{ request()->routeIs('mobile.returns.*') ? 'text-indigo-600 font-bold' : 'text-gray-500' }}">退货</span>
                    @if(request()->routeIs('mobile.returns.*'))
                        <span class="block w-1 h-1 bg-indigo-500 rounded-full mt-0.5"></span>
                    @endif
                </a>
            </div>
        </nav>
    </div>

    @stack('scripts')
</body>
</html> 