<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 越南盲袋库存管理系统</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('assets/bootstrap-icons.css') }}" rel="stylesheet">
<script src="{{ asset('assets/alpine.min.js') }}" defer></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-500 via-purple-600 to-blue-700 flex items-center justify-center p-4">
    <!-- 背景装饰 -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-1/2 -right-1/2 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-1/2 -left-1/2 w-96 h-96 bg-purple-300 opacity-20 rounded-full blur-3xl"></div>
    </div>

    <!-- 登录卡片 -->
    <div class="relative w-full max-w-md" x-data="{ isLoading: false }">
        <!-- 玻璃拟态卡片 -->
        <div class="backdrop-blur-xl bg-white/20 border border-white/30 rounded-2xl shadow-2xl overflow-hidden">
            <!-- 头部 -->
            <div class="px-8 pt-8 pb-6 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                    <i class="bi bi-box-seam text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">欢迎回来</h1>
                <p class="text-blue-100 text-sm">登录越南盲袋库存管理系统</p>
            </div>

            <!-- 登录表单 -->
            <div class="px-8 pb-8">
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-500/20 border border-red-500/30 rounded-lg backdrop-blur-sm">
                        <div class="flex items-center">
                            <i class="bi bi-exclamation-triangle text-red-300 mr-2"></i>
                            <span class="text-red-100 text-sm">{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ url('/admin/login') }}" x-on:submit="isLoading = true">
                    @csrf
                    
                    <!-- 用户名输入框 -->
                    <div class="mb-4">
                        <label class="block text-white/90 text-sm font-medium mb-2">
                            <i class="bi bi-person mr-1"></i>
                            用户名
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="username" 
                                value="{{ old('username') }}"
                                class="w-full px-4 py-3 bg-white/10 border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent backdrop-blur-sm transition-all duration-200 @error('username') border-red-400 @enderror" 
                                placeholder="请输入用户名"
                                required 
                                autofocus
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="bi bi-person-check text-white/40"></i>
                            </div>
                        </div>
                        @error('username')
                            <p class="mt-1 text-red-300 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 密码输入框 -->
                    <div class="mb-6">
                        <label class="block text-white/90 text-sm font-medium mb-2">
                            <i class="bi bi-shield-lock mr-1"></i>
                            密码
                        </label>
                        <div class="relative" x-data="{ showPassword: false }">
                            <input 
                                :type="showPassword ? 'text' : 'password'"
                                name="password" 
                                class="w-full px-4 py-3 bg-white/10 border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent backdrop-blur-sm transition-all duration-200 @error('password') border-red-400 @enderror" 
                                placeholder="请输入密码"
                                required
                            >
                            <button 
                                type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                x-on:click="showPassword = !showPassword"
                            >
                                <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'" class="text-white/60 hover:text-white/80 transition-colors"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-red-300 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 记住我 -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 bg-white/20 border-white/30 rounded focus:ring-blue-500 focus:ring-2">
                            <span class="ml-2 text-white/90 text-sm">记住登录状态</span>
                        </label>
                    </div>

                    <!-- 登录按钮 -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-transparent disabled:opacity-70 disabled:cursor-not-allowed"
                        :disabled="isLoading"
                        x-text="isLoading ? '登录中...' : '立即登录'"
                    >
                        立即登录
                    </button>
                </form>

                <!-- 底部信息 -->
                <div class="mt-6 text-center">
                    <p class="text-white/60 text-xs mb-2">
                        © 2024 越南盲袋库存管理系统. 保留所有权利.
                    </p>
                    <a href="{{ route('mobile.login') }}" class="text-white/70 hover:text-white text-xs underline">
                        移动端登录
                    </a>
                </div>
            </div>
        </div>

        <!-- 加载指示器 -->
        <div x-show="isLoading" x-transition class="absolute inset-0 bg-black/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
            <div class="bg-white/90 rounded-lg p-4 flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-700 font-medium">验证登录信息...</span>
            </div>
        </div>
    </div>

    <!-- 浮动粒子效果 -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute animate-pulse top-1/4 left-1/4 w-2 h-2 bg-white/30 rounded-full"></div>
        <div class="absolute animate-pulse top-3/4 right-1/4 w-1 h-1 bg-white/40 rounded-full animation-delay-1000"></div>
        <div class="absolute animate-pulse top-1/2 left-3/4 w-1.5 h-1.5 bg-white/20 rounded-full animation-delay-2000"></div>
    </div>

    <style>
        .animation-delay-1000 {
            animation-delay: 1s;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        
        /* 自定义滚动条 */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
    </style>
</body>
</html> 