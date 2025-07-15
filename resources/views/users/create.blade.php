@extends('layouts.app')

@section('header')
    <x-lang key="messages.users.create_user"/>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0"><x-lang key="messages.users.create_user"/></h5>
                    <p class="text-muted mb-0"><x-lang key="messages.users.create_user_subtitle"/></p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i>
                    <x-lang key="messages.users.back_to_list"/>
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0"><x-lang key="messages.users.basic_info"/></h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><x-lang key="messages.users.username"/></label>
                                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                                        @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><x-lang key="messages.users.password"/></label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><x-lang key="messages.users.real_name"/></label>
                                        <input type="text" name="real_name" class="form-control @error('real_name') is-invalid @enderror" value="{{ old('real_name') }}" required>
                                        @error('real_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><x-lang key="messages.users.email"/></label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0"><x-lang key="messages.users.other_info"/></h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="role_id" class="form-label"><x-lang key="messages.users.role"/></label>
                                    <select class="form-select @error('role_id') is-invalid @enderror" 
                                        id="role_id" name="role_id" required>
                                        <option value=""><x-lang key="messages.users.select_role"/></option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ $role->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><x-lang key="messages.users.status"/></label>
                                    <div class="form-check">
                                        <input type="radio" name="status" value="1" class="form-check-input @error('status') is-invalid @enderror" {{ old('status', 1) == 1 ? 'checked' : '' }} required>
                                        <label class="form-check-label"><x-lang key="messages.users.active"/></label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" name="status" value="0" class="form-check-input @error('status') is-invalid @enderror" {{ old('status') == 0 ? 'checked' : '' }} required>
                                        <label class="form-check-label"><x-lang key="messages.users.inactive"/></label>
                                    </div>
                                    @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><x-lang key="messages.users.assign_stores"/></label>
                                    <div class="row">
                                        @foreach($stores as $store)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="store_ids[]" 
                                                       value="{{ $store->id }}" 
                                                       class="form-check-input @error('store_ids') is-invalid @enderror"
                                                       id="store_{{ $store->id }}"
                                                       {{ in_array($store->id, old('store_ids', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="store_{{ $store->id }}">
                                                    {{ $store->name }} ({{ $store->code }})
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @error('store_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted"><x-lang key="messages.users.select_stores_note"/></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        <x-lang key="messages.users.save"/>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    background: var(--bg-card);
    border: none;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    border-radius: 1rem;
}

.card-header {
    background: none;
    border-bottom: 1px solid var(--border-color);
    padding: 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

.form-label {
    font-weight: 500;
    color: var(--text-secondary);
}

.form-control {
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 0.625rem 1rem;
    color: var(--text-primary);
    background-color: var(--bg-input);
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.1);
}

.form-control.is-invalid {
    border-color: var(--danger);
}

.form-control.is-invalid:focus {
    box-shadow: 0 0 0 0.25rem rgba(var(--danger-rgb), 0.1);
}

.invalid-feedback {
    font-size: 0.875rem;
    color: var(--danger);
}

.form-check-input {
    width: 1.25em;
    height: 1.25em;
    margin-top: 0.125em;
    vertical-align: top;
    background-color: var(--bg-input);
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    border: 1px solid var(--border-color);
    appearance: none;
    color-adjust: exact;
    border-radius: 0.25em;
}

.form-check-input:checked {
    background-color: var(--primary);
    border-color: var(--primary);
}

.form-check-input:focus {
    border-color: var(--primary);
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.1);
}

.form-check-input.is-invalid {
    border-color: var(--danger);
}

.form-check-label {
    margin-left: 0.5rem;
    color: var(--text-primary);
}

.btn {
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-weight: 500;
}

.btn-primary {
    background-color: var(--primary);
    border-color: var(--primary);
}

.btn-primary:hover {
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
}

.btn-light {
    background-color: var(--bg-light);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.btn-light:hover {
    background-color: var(--bg-hover);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.g-3 {
    --bs-gutter-y: 1rem;
}
</style>
@endsection 