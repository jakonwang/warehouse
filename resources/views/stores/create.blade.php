@extends('layouts.app')

@section('title', __('messages.stores.create_title'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-white py-10 px-2 md:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900"><x-lang key="messages.stores.create_title"/></h1>
                    <p class="mt-2 text-gray-600"><x-lang key="messages.stores.create_subtitle"/></p>
                </div>
                <a href="{{ route('stores.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    <i class="bi bi-arrow-left mr-2"></i><x-lang key="messages.stores.back_to_list"/>
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
            <form action="{{ route('stores.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 仓库名称 -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stores.store_name"/></label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="<x-lang key="messages.stores.store_name_placeholder"/>"
                               required>
                    </div>

                    <!-- 仓库编码 -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stores.store_code"/></label>
                        <input type="text" 
                               id="code" 
                               name="code" 
                               value="{{ old('code') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="<x-lang key="messages.stores.store_code_placeholder"/>"
                               required>
                    </div>

                    <!-- 仓库状态 -->
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stores.store_status"/></label>
                        <select id="is_active" 
                                name="is_active"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}><x-lang key="messages.stores.active"/></option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}><x-lang key="messages.stores.inactive"/></option>
                        </select>
                    </div>

                    <!-- 仓库描述 -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.stores.store_description"/></label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="<x-lang key="messages.stores.store_description_placeholder"/>">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- 提交按钮 -->
                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ route('stores.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <x-lang key="messages.stores.cancel"/>
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <x-lang key="messages.stores.create_store"/>
                    </button>
                </div>
            </form>
        </div>

        <!-- 创建说明 -->
        <div class="mt-8 bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2"><x-lang key="messages.stores.create_instructions"/></h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="bi bi-info-circle text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900"><x-lang key="messages.stores.name_instruction_title"/></h4>
                    </div>
                    <p class="text-sm text-gray-600"><x-lang key="messages.stores.name_instruction_desc"/></p>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="bi bi-code-slash text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900"><x-lang key="messages.stores.code_instruction_title"/></h4>
                    </div>
                    <p class="text-sm text-gray-600"><x-lang key="messages.stores.code_instruction_desc"/></p>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="bi bi-toggle-on text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900"><x-lang key="messages.stores.status_instruction_title"/></h4>
                    </div>
                    <p class="text-sm text-gray-600"><x-lang key="messages.stores.status_instruction_desc"/></p>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="bi bi-file-text text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900"><x-lang key="messages.stores.description_instruction_title"/></h4>
                    </div>
                    <p class="text-sm text-gray-600"><x-lang key="messages.stores.description_instruction_desc"/></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 