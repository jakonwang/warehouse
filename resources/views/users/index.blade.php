@extends('layouts.app')

@section('title')
    <x-lang key="messages.users.title"/>
@endsection
@section('header')
    <x-lang key="messages.users.title"/>
@endsection

@section('content')
<div class="space-y-6" x-data="{ 
    showAddModal: false, 
    showRoleModal: false,
    editingUser: null,
    selectedUser: null,
    newUser: {
        username: '',
        real_name: '',
        email: '',
        password: '',
        role: 'viewer',
        store_ids: []
    },
    resetForm() {
        this.newUser = {
            username: '',
            real_name: '',
            email: '',
            password: '',
            role: 'viewer',
            store_ids: []
        };
        this.editingUser = null;
    }
}">
    <!-- 页面头部 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><x-lang key="messages.users.title"/></h2>
                <p class="mt-1 text-sm text-gray-600"><x-lang key="messages.users.title"/></p>
            </div>
            <div class="flex items-center space-x-3">
                <button @click="showAddModal = true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-lg font-medium text-white hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="bi bi-person-plus mr-2"></i>
                    <x-lang key="messages.users.add"/>
                </button>
                <button class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                    <i class="bi bi-upload mr-2"></i>
                    <x-lang key="messages.users.batch_import"/>
                </button>
            </div>
        </div>
    </div>

    <!-- 权限角色说明 -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl border border-blue-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4"><x-lang key="messages.users.role_description_title"/></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex items-start">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="bi bi-crown text-red-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900"><x-lang key="messages.users.role_super_admin"/></h4>
                    <p class="text-sm text-gray-600"><x-lang key="messages.users.role_super_admin_description"/></p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="bi bi-gear text-blue-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900"><x-lang key="messages.users.role_inventory_manager"/></h4>
                    <p class="text-sm text-gray-600"><x-lang key="messages.users.role_inventory_manager_description"/></p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="bi bi-cart text-green-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900"><x-lang key="messages.users.role_sales_clerk"/></h4>
                    <p class="text-sm text-gray-600"><x-lang key="messages.users.role_sales_clerk_description"/></p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="bi bi-eye text-gray-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900"><x-lang key="messages.users.role_viewer"/></h4>
                    <p class="text-sm text-gray-600"><x-lang key="messages.users.role_viewer_description"/></p>
                </div>
            </div>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-people text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-blue-100 text-sm"><x-lang key="messages.users.total_users"/></p>
                    <p class="text-2xl font-bold">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-person-check text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-green-100 text-sm"><x-lang key="messages.users.active_users"/></p>
                    <p class="text-2xl font-bold">{{ $activeUsers }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-shield-check text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-purple-100 text-sm"><x-lang key="messages.users.admin_users"/></p>
                    <p class="text-2xl font-bold">{{ $adminUsers }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 bg-white/20 rounded-lg">
                    <i class="bi bi-clock-history text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-orange-100 text-sm"><x-lang key="messages.users.new_users_this_month"/></p>
                    <p class="text-2xl font-bold">{{ $newUsersThisMonth }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选和搜索栏 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- 搜索 -->
            <div class="flex-1 max-w-lg">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="bi bi-search text-gray-400"></i>
                    </div>
                    <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<x-lang key="messages.users.search_placeholder"/>">
                </div>
            </div>
            
            <!-- 筛选 -->
            <div class="flex items-center space-x-4">
                <select class="block w-40 pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent rounded-lg">
                    <option value=""><x-lang key="messages.users.all_roles"/></option>
                    <option value="admin"><x-lang key="messages.users.role_super_admin"/></option>
                    <option value="manager"><x-lang key="messages.users.role_inventory_manager"/></option>
                    <option value="sales"><x-lang key="messages.users.role_sales_clerk"/></option>
                    <option value="viewer"><x-lang key="messages.users.role_viewer"/></option>
                </select>
                <select class="block w-40 pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent rounded-lg">
                    <option value=""><x-lang key="messages.users.all_status"/></option>
                    <option value="active"><x-lang key="messages.users.active"/></option>
                    <option value="inactive"><x-lang key="messages.users.inactive"/></option>
                </select>
            </div>
        </div>
    </div>

    <!-- 用户列表 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900"><x-lang key="messages.users.user_list"/></h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-lang key="messages.users.user_info"/>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-lang key="messages.users.role_permissions"/>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-lang key="messages.users.stores"/>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-lang key="messages.users.status"/>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-lang key="messages.users.last_login"/>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-lang key="messages.users.actions"/>
                        </th>
                        </tr>
                    </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">{{ mb_substr($user->real_name, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->real_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->username }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($user->role)
                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="bi bi-crown text-red-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <x-lang key="messages.users.role_{{ $user->role->name }}"/>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <x-lang key="messages.users.role_{{ $user->role->name }}_description"/>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500"><x-lang key="messages.users.no_role_assigned"/></span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($user->isSuperAdmin())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <x-lang key="messages.users.all_stores"/>
                                    </span>
                                @else
                                    @foreach($user->stores as $store)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                            {{ $store->name }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                    <x-lang key="messages.users.active_status"/>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                    <x-lang key="messages.users.inactive_status"/>
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($user->last_login_at)
                                <div>{{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}</div>
                                <div class="text-xs text-gray-400">{{ $user->last_login_ip }}</div>
                            @else
                                <div class="text-gray-400"><x-lang key="messages.users.never_logged_in"/></div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('users.show', $user) }}" class="text-blue-600 hover:text-blue-900 transition-colors" title="<x-lang key="messages.users.view"/>">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user) }}" class="text-gray-600 hover:text-gray-900 transition-colors" title="<x-lang key="messages.users.edit"/>">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition-colors" onclick="return confirm('<x-lang key="messages.users.confirm_delete"/>')" title="<x-lang key="messages.users.delete"/>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        <!-- 分页 -->
        <div class="bg-white px-6 py-3 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>

    <!-- 添加用户模态框 -->
    <div x-show="showAddModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showAddModal = false; resetForm()"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="#" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="bi bi-person-plus text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900"><x-lang key="messages.users.add_user"/></h3>
                                <div class="mt-4 space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.username"/></label>
                                            <input type="text" name="username" x-model="newUser.username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<x-lang key="messages.users.username_placeholder"/>" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.real_name"/></label>
                                            <input type="text" name="real_name" x-model="newUser.real_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<x-lang key="messages.users.real_name_placeholder"/>" required>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.email_address"/></label>
                                        <input type="email" name="email" x-model="newUser.email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<x-lang key="messages.users.email_placeholder"/>">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.login_password"/></label>
                                        <input type="password" name="password" x-model="newUser.password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<x-lang key="messages.users.password_placeholder"/>" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.user_role"/></label>
                                        <select name="role" x-model="newUser.role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="viewer"><x-lang key="messages.users.role_viewer"/></option>
                                            <option value="sales"><x-lang key="messages.users.role_sales_clerk"/></option>
                                            <option value="manager"><x-lang key="messages.users.role_inventory_manager"/></option>
                                            <option value="admin"><x-lang key="messages.users.role_super_admin"/></option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><x-lang key="messages.users.assign_stores"/></label>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach($stores as $store)
                                            <label class="flex items-center">
                                                <input type="checkbox"
                                                       name="store_ids[]"
                                                       :value="{{ $store->id }}"
                                                       x-model="newUser.store_ids"
                                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                                <span class="ml-2 text-sm text-gray-700">{{ $store->name }} ({{ $store->code }})</span>
                                            </label>
                                            @endforeach
                                        </div>
                                        <small class="text-gray-500 text-xs mt-1"><x-lang key="messages.users.select_stores_note"/></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <x-lang key="messages.users.create_user"/>
                        </button>
                        <button type="button" @click="showAddModal = false; resetForm()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            <x-lang key="messages.users.cancel"/>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 权限管理模态框 -->
    <div x-show="showRoleModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRoleModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="bi bi-shield-check text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900"><x-lang key="messages.users.permission_management"/></h3>
                            <div class="mt-4">
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <div class="text-sm font-medium text-gray-900"><x-lang key="messages.users.current_user"/>: <span x-text="selectedUser"></span></div>
                                </div>
                                
                                <!-- 权限矩阵 -->
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900"><x-lang key="messages.users.function_permissions"/></h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900"><x-lang key="messages.users.product_management"/></div>
                                                <div class="text-sm text-gray-500"><x-lang key="messages.users.product_management_description"/></div>
                                            </div>
                                            <label class="flex items-center">
                                                <input type="checkbox" checked class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                            </label>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900"><x-lang key="messages.users.inventory_management"/></div>
                                                <div class="text-sm text-gray-500"><x-lang key="messages.users.inventory_management_description"/></div>
                                            </div>
                                            <label class="flex items-center">
                                                <input type="checkbox" checked class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                            </label>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900"><x-lang key="messages.users.sales_management"/></div>
                                                <div class="text-sm text-gray-500"><x-lang key="messages.users.sales_management_description"/></div>
                                            </div>
                                            <label class="flex items-center">
                                                <input type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                            </label>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900"><x-lang key="messages.users.system_configuration"/></div>
                                                <div class="text-sm text-gray-500"><x-lang key="messages.users.system_configuration_description"/></div>
                                            </div>
                                            <label class="flex items-center">
                                                <input type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <x-lang key="messages.users.save_permissions"/>
                    </button>
                    <button type="button" @click="showRoleModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        <x-lang key="messages.users.cancel"/>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 