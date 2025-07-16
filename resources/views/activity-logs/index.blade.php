@extends('layouts.app')

@section('title', __('activity-logs.title'))
@section('header', __('activity-logs.header'))

@section('content')
<div class="space-y-6">
    {{-- 页面标题和统计卡片 --}}
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ __('activity-logs.title') }}</h1>
                <p class="text-purple-100">系统操作记录和监控</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $stats['total_logs'] ?? 0 }}</div>
                    <div class="text-sm text-purple-200">总记录</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $stats['today_logs'] ?? 0 }}</div>
                    <div class="text-sm text-purple-200">今日</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $stats['unique_users'] ?? 0 }}</div>
                    <div class="text-sm text-purple-200">活跃用户</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 筛选表单 --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">筛选条件</h2>
            <div class="flex space-x-2">
                <a href="{{ route('activity-logs.index') }}" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="bi bi-arrow-clockwise mr-1"></i>重置
                </a>
                <a href="{{ route('activity-logs.export') }}" class="px-4 py-2 text-sm bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                    <i class="bi bi-download mr-1"></i>导出
                </a>
            </div>
        </div>
        
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">{{ __('activity-logs.table.user') }}</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">{{ __('activity-logs.filter.all_users') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @if(request('user_id') == $user->id) selected @endif>{{ $user->real_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">{{ __('activity-logs.table.action') }}</label>
                <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">{{ __('activity-logs.filter.all_actions') }}</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" @if(request('action') == $action) selected @endif>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">{{ __('activity-logs.table.ip') }}</label>
                <select name="ip_address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">{{ __('activity-logs.filter.all_ips') }}</option>
                    @foreach($ipAddresses as $ip)
                        <option value="{{ $ip }}" @if(request('ip_address') == $ip) selected @endif>{{ $ip }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="bi bi-search mr-1"></i>{{ __('activity-logs.filter.filter') }}
                </button>
            </div>
        </form>
    </div>

    {{-- 日志列表 --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">操作记录</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('activity-logs.table.id') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('activity-logs.table.user') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('activity-logs.table.action') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('activity-logs.table.model') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('activity-logs.table.ip') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('activity-logs.table.status_code') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('activity-logs.table.execution_time') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('activity-logs.table.time') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('activity-logs.table.details') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $log->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-purple-600">{{ substr($log->user->real_name ?? 'U', 0, 1) }}</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $log->user->real_name ?? __('activity-logs.table.unknown') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($log->action === 'create') bg-green-100 text-green-800
                                @elseif($log->action === 'update') bg-blue-100 text-blue-800
                                @elseif($log->action === 'delete') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $log->action_name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->model_type_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $log->ip_address }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($log->status_code >= 200 && $log->status_code < 300) bg-green-100 text-green-800
                                @elseif($log->status_code >= 400) bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ $log->status_code_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->execution_time_text }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('m-d H:i:s') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('activity-logs.show', $log) }}" class="text-purple-600 hover:text-purple-900 transition-colors">
                                <i class="bi bi-eye mr-1"></i>{{ __('activity-logs.table.view') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="bi bi-journal-text text-4xl text-gray-300 mb-2"></i>
                                <p class="text-lg font-medium">暂无操作记录</p>
                                <p class="text-sm">当前筛选条件下没有找到任何操作记录</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- 分页 --}}
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $logs->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection 