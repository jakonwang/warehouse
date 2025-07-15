@extends('layouts.app')

@section('title', '操作日志')
@section('header', '操作日志')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold mb-4">操作日志</h1>
        <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <select name="user_id" class="form-select">
                <option value="">全部用户</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @if(request('user_id') == $user->id) selected @endif>{{ $user->real_name }}</option>
                @endforeach
            </select>
            <select name="action" class="form-select">
                <option value="">全部操作</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @if(request('action') == $action) selected @endif>{{ $action }}</option>
                @endforeach
            </select>
            <select name="ip_address" class="form-select">
                <option value="">全部IP</option>
                @foreach($ipAddresses as $ip)
                    <option value="{{ $ip }}" @if(request('ip_address') == $ip) selected @endif>{{ $ip }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">筛选</button>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th>ID</th>
                        <th>用户</th>
                        <th>操作</th>
                        <th>模型</th>
                        <th>模型ID</th>
                        <th>IP</th>
                        <th>状态码</th>
                        <th>执行时间</th>
                        <th>响应大小</th>
                        <th>时间</th>
                        <th>详情</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr class="border-b">
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->user->real_name ?? '未知' }}</td>
                        <td>{{ $log->action_name }}</td>
                        <td>{{ $log->model_type_name }}</td>
                        <td>{{ $log->model_id }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->status_code_text }}</td>
                        <td>{{ $log->execution_time_text }}</td>
                        <td>{{ $log->response_size_text }}</td>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td><a href="{{ route('activity-logs.show', $log) }}" class="text-blue-600 hover:underline">查看</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->withQueryString()->links() }}</div>
    </div>
</div>
@endsection 