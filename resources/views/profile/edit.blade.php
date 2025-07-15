@extends('layouts.app')

@section('title', '个人资料')
@section('header', '个人资料')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- 页面标题 -->
        <div class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="bi bi-person-gear text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-bold text-gray-900">个人资料管理</h2>
                    <p class="text-gray-600">管理您的账户信息和安全设置</p>
                </div>
            </div>
        </div>

        <!-- 个人信息 -->
        <div class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-person text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">基本信息</h3>
                        <p class="text-green-100 text-sm">更新您的账户基本信息</p>
                    </div>
                </div>
            </div>
            <div class="p-8">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- 密码修改 -->
        <div class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-500 to-orange-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-shield-lock text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">安全设置</h3>
                        <p class="text-orange-100 text-sm">修改您的登录密码</p>
                    </div>
                </div>
            </div>
            <div class="p-8">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- 账户删除 -->
        <div class="bg-white/70 backdrop-blur-lg border border-white/20 rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-red-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-exclamation-triangle text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">危险操作</h3>
                        <p class="text-red-100 text-sm">永久删除您的账户</p>
                    </div>
                </div>
            </div>
            <div class="p-8">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection 