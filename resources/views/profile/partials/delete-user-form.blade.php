<section class="space-y-6">
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">
            删除账户
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            删除账户后，所有相关数据将被永久删除且无法恢复。请在删除前备份您需要保留的数据。
        </p>
    </header>

    <div class="bg-red-50 border border-red-200 rounded-xl p-6">
        <div class="flex items-start">
            <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center mr-4">
                <i class="bi bi-exclamation-triangle text-red-600"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-red-800 mb-2">危险操作警告</h3>
                <p class="text-sm text-red-700 mb-4">
                    此操作将永久删除您的账户和所有相关数据，包括：
                </p>
                <ul class="text-sm text-red-700 list-disc list-inside space-y-1 mb-6">
                    <li>个人资料信息</li>
                    <li>操作历史记录</li>
                    <li>系统配置数据</li>
                    <li>所有关联的业务数据</li>
                </ul>
                
                <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4" onsubmit="return confirm('您确定要删除账户吗？此操作无法撤销！')">
                    @csrf
                    @method('delete')

                    <div>
                        <x-input-label for="password" value="请输入当前密码确认" class="text-red-800" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" placeholder="输入密码确认删除" required />
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>

                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold rounded-xl hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-4 focus:ring-red-500/20 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="bi bi-trash mr-2"></i>
                        永久删除账户
                    </button>
                </form>
            </div>
        </div>
    </div>
</section> 