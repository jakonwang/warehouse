<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>登录 - 盲袋库存管理系统</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('assets/bootstrap-icons.css') }}" rel="stylesheet">
<script src="{{ asset('assets/alpine.min.js') }}" defer></script>
</head>
<body class="min-h-screen w-full bg-gradient-to-br from-violet-700 via-purple-700 to-fuchsia-700 flex items-center justify-center relative overflow-x-hidden" x-data="{ isLoading: false, showPassword: false }">
    <!-- 背景装饰元素 -->
    <div class="fixed inset-0 z-0 pointer-events-none select-none">
        <div class="absolute top-1/4 left-1/4 w-32 h-32 bg-white/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute top-3/4 right-1/4 w-24 h-24 bg-purple-300/20 rounded-full blur-lg animate-pulse delay-1000"></div>
        <div class="absolute bottom-1/4 left-1/3 w-20 h-20 bg-pink-300/15 rounded-full blur-md animate-pulse delay-500"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-40 h-40 bg-indigo-300/10 rounded-full blur-2xl animate-pulse delay-1500"></div>
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.10) 1px, transparent 0); background-size: 20px 20px;"></div>
    </div>

    <!-- 登录卡片 -->
    <div class="relative z-10 w-full max-w-sm mx-auto">
        <div class="backdrop-blur-2xl bg-white/20 border border-white/30 rounded-3xl shadow-2xl overflow-hidden">
            <div class="px-8 pt-10 pb-8 text-center relative">
                <div class="relative mb-8">
                    <div class="w-24 h-24 bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 rounded-3xl mx-auto flex items-center justify-center shadow-2xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                        <div class="absolute top-2 left-2 w-4 h-4 bg-white/30 rounded-full"></div>
                        <div class="absolute bottom-2 right-2 w-3 h-3 bg-white/20 rounded-full"></div>
                        <i class="bi bi-box-seam text-white text-4xl relative z-10"></i>
                    </div>
                    <div class="absolute inset-0 w-24 h-24 rounded-3xl bg-gradient-to-r from-violet-400 to-fuchsia-400 blur-xl opacity-50 animate-pulse"></div>
                </div>
                <h1 class="text-3xl font-bold text-white mb-3 drop-shadow-lg">欢迎回来</h1>
                <p class="text-white/80 text-base">登录盲袋库存管理系统</p>
                <div class="flex justify-center mt-4 space-x-2">
                    <div class="w-2 h-2 bg-white/60 rounded-full animate-pulse"></div>
                    <div class="w-2 h-2 bg-white/40 rounded-full animate-pulse delay-200"></div>
                    <div class="w-2 h-2 bg-white/20 rounded-full animate-pulse delay-400"></div>
                </div>
            </div>
            <div class="px-8 pb-10">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-500/20 border border-red-400/30 rounded-2xl backdrop-blur-sm animate-pulse">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-red-500/20 rounded-full flex items-center justify-center mr-3">
                                <i class="bi bi-exclamation-triangle text-red-300 text-sm"></i>
                            </div>
                            <span class="text-red-100 text-sm font-medium">{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif
                <form method="POST" action="{{ url('/login') }}" x-on:submit="isLoading = true">
                    @csrf
                    <div class="mb-6">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-r from-violet-500/20 to-fuchsia-500/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-300"></div>
                            <input type="text" name="username" value="{{ old('username') }}" class="relative w-full px-6 py-4 bg-white/15 border border-white/30 rounded-2xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent backdrop-blur-sm transition-all duration-300 @error('username') border-red-400 @enderror" placeholder="请输入用户名" required autofocus>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i class="bi bi-person-check text-white/50 group-hover:text-white/70 transition-colors"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mb-8">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-r from-violet-500/20 to-fuchsia-500/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-300"></div>
                            <input :type="showPassword ? 'text' : 'password'" name="password" class="relative w-full px-6 py-4 bg-white/15 border border-white/30 rounded-2xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent backdrop-blur-sm transition-all duration-300 @error('password') border-red-400 @enderror" placeholder="请输入密码" required>
                            <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center" x-on:click="showPassword = !showPassword">
                                <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'" class="text-white/60 hover:text-white/80 transition-colors"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-8">
                        <label class="flex items-center group cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" name="remember" class="sr-only">
                                <div class="w-5 h-5 bg-white/20 border border-white/30 rounded-lg flex items-center justify-center group-hover:bg-white/30 transition-all duration-200">
                                    <i class="bi bi-check text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </div>
                            <span class="ml-3 text-white/90 text-sm font-medium">记住登录状态</span>
                        </label>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 hover:from-violet-700 hover:via-purple-700 hover:to-fuchsia-700 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 focus:ring-offset-transparent disabled:opacity-70 disabled:cursor-not-allowed shadow-xl hover:shadow-2xl relative overflow-hidden group" :disabled="isLoading">
                        <div class="absolute inset-0 bg-gradient-to-r from-violet-500/20 to-fuchsia-500/20 blur-xl group-hover:blur-2xl transition-all duration-300"></div>
                        <div class="relative flex items-center justify-center">
                            <span x-show="!isLoading" x-transition class="flex items-center">
                                <i class="bi bi-box-arrow-in-right mr-2 text-lg"></i>
                                立即登录
                            </span>
                            <span x-show="isLoading" x-transition class="flex items-center">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                                验证登录信息...
                            </span>
                        </div>
                    </button>
                </form>
                <div class="mt-8 text-center">
                    <p class="text-white/60 text-xs mb-3">© 2024 盲袋库存管理系统. 保留所有权利.</p>
                    <a href="{{ route('admin.login') }}" class="inline-flex items-center text-white/70 hover:text-white text-xs font-medium transition-colors duration-200 group">
                        <i class="bi bi-display mr-1 group-hover:scale-110 transition-transform"></i>
                        后台管理登录
                    </a>
                </div>
            </div>
        </div>
    </div>
    <style>
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        .delay-200 { animation-delay: 200ms; }
        .delay-400 { animation-delay: 400ms; }
        .delay-500 { animation-delay: 500ms; }
        .delay-1000 { animation-delay: 1000ms; }
        .delay-1500 { animation-delay: 1500ms; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.1); }
        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.3); border-radius: 3px; }
        input:focus { transform: translateY(-1px); }
        button:hover { transform: translateY(-2px); }
    </style>
</body>
</html> 