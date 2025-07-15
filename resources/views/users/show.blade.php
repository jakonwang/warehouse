@extends('layouts.app')

@section('header')
    <x-lang key="messages.users.user_details"/>
@endsection

@section('content')
<div class="flex justify-center items-center min-h-[70vh] py-8">
    <div class="w-full max-w-2xl">
        <div class="backdrop-blur-xl bg-white/30 border border-white/40 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-8 pt-8 pb-6 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                    <span class="text-white text-2xl font-bold">{{ mb_substr($user->real_name, 0, 1) }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $user->real_name }}</h1>
                <div class="flex justify-center items-center gap-2 mb-2">
                    @if($user->role)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i class="bi bi-person-badge mr-1"></i>{{ $user->role->display_name }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><x-lang key="messages.users.no_role_assigned"/></span>
                    @endif
                    @if($user->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span><x-lang key="messages.users.active_status"/>
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span><x-lang key="messages.users.inactive_status"/>
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap justify-center gap-2 mb-2">
                    @if($user->isSuperAdmin())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><x-lang key="messages.users.all_stores"/></span>
                    @else
                        @foreach($user->stores as $store)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $store->name }}</span>
                        @endforeach
                    @endif
                </div>
                <div class="flex justify-center gap-3 mt-4">
                    <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-lg font-medium text-white hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="bi bi-pencil mr-2"></i> <x-lang key="messages.users.edit"/>
                    </a>
                    <a href="{{ route('users.index') }}" class="inline-flex items-center px-6 py-2 bg-gray-200 border border-transparent rounded-lg font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all duration-200">
                        <i class="bi bi-arrow-left mr-2"></i> <x-lang key="messages.users.back_to_list"/>
                    </a>
                </div>
            </div>
            <div class="px-8 pb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white/80 rounded-xl shadow p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center"><i class="bi bi-person-lines-fill mr-2 text-blue-500"></i><x-lang key="messages.users.basic_information"/></h3>
                        <div class="space-y-3">
                            <div><span class="text-gray-500 text-sm"><x-lang key="messages.users.username"/>：</span><span class="ml-2 text-gray-900 font-medium">{{ $user->username }}</span></div>
                            <div><span class="text-gray-500 text-sm"><x-lang key="messages.users.email"/>：</span><span class="ml-2 text-gray-900 font-medium">{{ $user->email }}</span></div>
                            <div><span class="text-gray-500 text-sm"><x-lang key="messages.users.phone"/>：</span><span class="ml-2 text-gray-900 font-medium">{{ $user->phone ?: '—' }}</span></div>
                        </div>
                    </div>
                    <div class="bg-white/80 rounded-xl shadow p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center"><i class="bi bi-clock-history mr-2 text-purple-500"></i><x-lang key="messages.users.system_information"/></h3>
                        <div class="space-y-3">
                            <div><span class="text-gray-500 text-sm"><x-lang key="messages.users.created_time"/>：</span><span class="ml-2 text-gray-900 font-medium">{{ $user->created_at->format('Y-m-d H:i:s') }}</span></div>
                            <div><span class="text-gray-500 text-sm"><x-lang key="messages.users.last_updated"/>：</span><span class="ml-2 text-gray-900 font-medium">{{ $user->updated_at->format('Y-m-d H:i:s') }}</span></div>
                            <div><span class="text-gray-500 text-sm"><x-lang key="messages.users.last_login"/>：</span><span class="ml-2 text-gray-900 font-medium">{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '—' }}</span></div>
                            <div><span class="text-gray-500 text-sm"><x-lang key="messages.users.login_ip"/>：</span><span class="ml-2 text-gray-900 font-medium">{{ $user->last_login_ip ?: '—' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 