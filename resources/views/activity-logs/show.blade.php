@extends('layouts.app')

@section('title', '操作日志详情')
@section('header', '操作日志详情')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-8 mt-8">
    <h2 class="text-xl font-bold mb-4">操作日志详情</h2>
    <dl class="divide-y divide-gray-100">
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">ID</dt>
            <dd>{{ $activityLog->id }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">用户</dt>
            <dd>{{ $activityLog->user->real_name ?? '未知' }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">操作</dt>
            <dd>{{ $activityLog->action_name }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">模型类型</dt>
            <dd>{{ $activityLog->model_type_name }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">模型ID</dt>
            <dd>{{ $activityLog->model_id }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">IP地址</dt>
            <dd>{{ $activityLog->ip_address }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">状态码</dt>
            <dd>{{ $activityLog->status_code_text }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">执行时间</dt>
            <dd>{{ $activityLog->execution_time_text }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">响应大小</dt>
            <dd>{{ $activityLog->response_size_text }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">请求方法</dt>
            <dd>{{ $activityLog->method }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">请求URL</dt>
            <dd class="break-all">{{ $activityLog->url }}</dd>
        </div>
        <div class="py-2 flex justify-between">
            <dt class="font-medium text-gray-600">时间</dt>
            <dd>{{ $activityLog->created_at->format('Y-m-d H:i:s') }}</dd>
        </div>
        <div class="py-2">
            <dt class="font-medium text-gray-600">请求数据</dt>
            <dd><pre class="bg-gray-50 rounded p-2 text-xs">{{ json_encode($activityLog->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></dd>
        </div>
        <div class="py-2">
            <dt class="font-medium text-gray-600">User Agent</dt>
            <dd class="break-all text-xs">{{ $activityLog->user_agent }}</dd>
        </div>
    </dl>
    <div class="mt-6">
        <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary">返回日志列表</a>
    </div>
</div>
@endsection 