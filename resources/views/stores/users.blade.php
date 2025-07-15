@extends('layouts.app')

@section('title', '仓库用户管理')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $store->name }} - 用户管理</h5>
            <a href="{{ route('stores.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                返回列表
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">当前用户</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>用户名</th>
                                            <th>姓名</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->username }}</td>
                                            <td>{{ $user->real_name }}</td>
                                            <td>
                                                <form action="{{ route('stores.remove-user', $store) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('确定要移除该用户吗？')">
                                                        <i class="bi bi-person-dash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                {{ $users->links() }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">添加用户</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('stores.add-user', $store) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">选择用户</label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" 
                                        id="user_id" name="user_id" required>
                                        <option value="">请选择用户</option>
                                        @foreach($availableUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->real_name }} ({{ $user->username }})</option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-person-plus me-1"></i>
                                        添加用户
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 