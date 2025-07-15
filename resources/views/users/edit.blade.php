@extends('layouts.app')

@section('header')
    <x-lang key="messages.users.edit_user"/>
@endsection

@section('content')
<div class="flex justify-center items-center min-h-[70vh] py-8">
    <div class="w-full max-w-xl">
        <div class="backdrop-blur-xl bg-white/30 border border-white/40 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-8 pt-8 pb-6 text-center">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                    <i class="bi bi-person-gear text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2"><x-lang key="messages.users.edit_user"/></h1>
                <p class="text-gray-500 text-sm"><x-lang key="messages.users.edit_user_subtitle"/></p>
            </div>
            <div class="px-8 pb-8">
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-500/20 border border-red-500/30 rounded-lg backdrop-blur-sm">
                        <div class="flex items-center">
                            <i class="bi bi-exclamation-triangle text-red-300 mr-2"></i>
                            <span class="text-red-100 text-sm">{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif
                <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.username"/></label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.real_name"/></label>
                            <input type="text" name="real_name" value="{{ old('real_name', $user->real_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.email"/></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.phone"/></label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.new_password"/></label>
                        <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<x-lang key='messages.users.new_password_placeholder'/>">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.user_role"/></label>
                            <select name="role_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @foreach(\App\Models\Role::all() as $role)
                                    <option value="{{ $role->id }}" @if(old('role_id', $user->role_id) == $role->id) selected @endif>{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.user_status"/></label>
                            <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="1" @if(old('is_active', $user->is_active)) selected @endif><x-lang key="messages.users.active_status"/></option>
                                <option value="0" @if(!old('is_active', $user->is_active)) selected @endif><x-lang key="messages.users.inactive_status"/></option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.assign_stores"/></label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($stores as $store)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="store_ids[]" 
                                       value="{{ $store->id }}" 
                                       id="store_{{ $store->id }}"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                       {{ in_array($store->id, old('store_ids', $userStores)) ? 'checked' : '' }}>
                                <label class="ml-2 text-sm text-gray-700" for="store_{{ $store->id }}">
                                    {{ $store->name }} ({{ $store->code }})
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-gray-500 text-xs mt-1"><x-lang key="messages.users.select_stores_note"/></small>
                    </div>
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-lg font-medium text-white hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <i class="bi bi-save mr-2"></i> <x-lang key="messages.users.save_changes"/>
                        </button>
                        <a href="{{ route('users.index') }}" class="inline-flex items-center px-6 py-2 bg-gray-200 border border-transparent rounded-lg font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all duration-200">
                            <i class="bi bi-arrow-left mr-2"></i> <x-lang key="messages.users.back_to_list"/>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 