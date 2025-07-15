@extends('layouts.app')

@section('title', __('messages.backup.title'))
@section('header', __('messages.backup.title'))

@section('content')
<div class="space-y-6" x-data="{ 
    loading: false,
    showRestoreModal: false,
    selectedBackup: null,
    restoreType: 'database'
}">
    <!-- 页面标题 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.backup.title') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('messages.backup.subtitle') }}</p>
            </div>
            <div class="mt-4 lg:mt-0 flex items-center space-x-3">
                <button @click="createBackup('database')" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="bi bi-database mr-2"></i>
                    {{ __('messages.backup.create_database_backup') }}
                </button>
                <button @click="createBackup('files')" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                    <i class="bi bi-folder2-open mr-2"></i>
                    {{ __('messages.backup.create_file_backup') }}
                </button>
                <button @click="createBackup('full')" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                    <i class="bi bi-archive mr-2"></i>
                    {{ __('messages.backup.create_full_backup') }}
                </button>
            </div>
        </div>
    </div>

    <!-- 备份统计 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">{{ __('messages.backup.total_backups') }}</p>
                    <p class="text-3xl font-bold">{{ $backupStats['total_count'] }}</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-archive text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">{{ __('messages.backup.database_backups') }}</p>
                    <p class="text-3xl font-bold">{{ $backupStats['type_counts']['database'] }}</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-database text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium">{{ __('messages.backup.file_backups') }}</p>
                    <p class="text-3xl font-bold">{{ $backupStats['type_counts']['files'] }}</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-folder2-open text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">{{ __('messages.backup.full_backups') }}</p>
                    <p class="text-3xl font-bold">{{ $backupStats['type_counts']['full'] }}</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-archive text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 备份列表 -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-xl font-semibold text-gray-900">备份文件列表</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">文件名</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">类型</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">大小</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">创建时间</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($backups as $backup)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $backup['filename'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($backup['type'] == 'database')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="bi bi-database mr-1"></i>数据库
                                </span>
                            @elseif($backup['type'] == 'files')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="bi bi-folder2-open mr-1"></i>文件
                                </span>
                            @elseif($backup['type'] == 'full')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="bi bi-archive mr-1"></i>完整
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="bi bi-question-circle mr-1"></i>未知
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $backup['size'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $backup['created_at'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('backup.download', $backup['filename']) }}" class="text-blue-600 hover:text-blue-900 transition-colors">
                                    <i class="bi bi-download"></i>
                                </a>
                                @if($backup['type'] == 'database')
                                    <button @click="showRestoreModal = true; selectedBackup = '{{ $backup['filename'] }}'; restoreType = 'database'" class="text-green-600 hover:text-green-900 transition-colors">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                @endif
                                <button @click="deleteBackup('{{ $backup['filename'] }}')" class="text-red-600 hover:text-red-900 transition-colors">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="bi bi-archive text-4xl mb-2"></i>
                                <p>暂无备份文件</p>
                                <p class="text-sm">点击上方按钮创建第一个备份</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 备份时间信息 -->
    @if($backupStats['total_count'] > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">备份时间信息</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-900 mb-2">最新备份</h4>
                <p class="text-xl font-bold text-blue-600">{{ $backupStats['newest_backup'] }}</p>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-orange-900 mb-2">最早备份</h4>
                <p class="text-xl font-bold text-orange-600">{{ $backupStats['oldest_backup'] }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- 恢复确认模态框 -->
<div x-show="showRestoreModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="bi bi-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">确认恢复</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                您确定要恢复数据库备份吗？这将覆盖当前数据库中的所有数据。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="restoreBackup()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    确认恢复
                </button>
                <button @click="showRestoreModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    取消
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function createBackup(type) {
    this.loading = true;
    
    let url = '';
    switch(type) {
        case 'database':
            url = '{{ route("backup.database") }}';
            break;
        case 'files':
            url = '{{ route("backup.files") }}';
            break;
        case 'full':
            url = '{{ route("backup.full") }}';
            break;
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 显示成功消息
            showNotification(data.message, 'success');
            // 刷新页面
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('备份创建失败', 'error');
    })
    .finally(() => {
        this.loading = false;
    });
}

function deleteBackup(filename) {
    if (!confirm('确定要删除这个备份文件吗？')) {
        return;
    }
    
    fetch(`{{ route('backup.destroy', '') }}/${filename}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('删除失败', 'error');
    });
}

function restoreBackup() {
    if (!this.selectedBackup) {
        return;
    }
    
    fetch(`{{ route('backup.restore', '') }}/${this.selectedBackup}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            this.showRestoreModal = false;
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('恢复失败', 'error');
    });
}

function showNotification(message, type) {
    // 这里可以集成到现有的通知系统
    alert(message);
}
</script>
@endsection 