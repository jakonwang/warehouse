@extends('layouts.app')

@section('title', __('activity-logs.details.title'))
@section('header', __('activity-logs.details.title'))

@section('content')
<div class="space-y-6">
    {{-- 页面标题 --}}
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ __('activity-logs.details.title') }}</h1>
                <p class="text-purple-100">操作日志详细信息</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-center">
                    <div class="text-2xl font-bold">#{{ $activityLog->id }}</div>
                    <div class="text-sm text-purple-200">日志ID</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $activityLog->created_at->format('m-d') }}</div>
                    <div class="text-sm text-purple-200">创建日期</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- 基本信息 --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('activity-logs.details.basic_info') }}</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">{{ __('activity-logs.table.user') }}</span>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-purple-600">{{ substr($activityLog->user->real_name ?? 'U', 0, 1) }}</span>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-900">{{ $activityLog->user->real_name ?? __('activity-logs.table.unknown') }}</span>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">{{ __('activity-logs.table.action') }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($activityLog->action === 'create') bg-green-100 text-green-800
                        @elseif($activityLog->action === 'update') bg-blue-100 text-blue-800
                        @elseif($activityLog->action === 'delete') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ $activityLog->action_name }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">{{ __('activity-logs.table.model') }}</span>
                    <span class="text-sm text-gray-900">{{ $activityLog->model_type_name }}</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">{{ __('activity-logs.table.model_id') }}</span>
                    <span class="text-sm text-gray-900">{{ $activityLog->model_id }}</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">{{ __('activity-logs.table.ip') }}</span>
                    <span class="text-sm font-mono text-gray-900">{{ $activityLog->ip_address }}</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">{{ __('activity-logs.table.status_code') }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($activityLog->status_code >= 200 && $activityLog->status_code < 300) bg-green-100 text-green-800
                        @elseif($activityLog->status_code >= 400) bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ $activityLog->status_code_text }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">{{ __('activity-logs.table.execution_time') }}</span>
                    <span class="text-sm text-gray-900">{{ $activityLog->execution_time_text }}</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">{{ __('activity-logs.table.response_size') }}</span>
                    <span class="text-sm text-gray-900">{{ $activityLog->response_size_text }}</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">{{ __('activity-logs.table.time') }}</span>
                    <span class="text-sm text-gray-900">{{ $activityLog->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </div>

        {{-- 请求信息 --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('activity-logs.details.request_info') }}</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <span class="text-sm font-medium text-gray-600 block mb-2">请求方法</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $activityLog->method }}
                    </span>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-600 block mb-2">请求URL</span>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <code class="text-xs text-gray-700 break-all">{{ $activityLog->url }}</code>
                    </div>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-600 block mb-2">User Agent</span>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <code class="text-xs text-gray-700 break-all">{{ $activityLog->user_agent }}</code>
                    </div>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-600 block mb-2">请求数据</span>
                    <div class="bg-gray-50 rounded-lg p-3 max-h-40 overflow-y-auto">
                        <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ json_encode($activityLog->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 操作按钮 --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
            <i class="bi bi-arrow-left mr-2"></i>{{ __('activity-logs.details.back_to_list') }}
        </a>
        
        <div class="flex space-x-2">
            <a href="{{ route('activity-logs.export') }}?id={{ $activityLog->id }}" class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                <i class="bi bi-download mr-2"></i>导出详情
            </a>
        </div>
    </div>
</div>
@endsection 