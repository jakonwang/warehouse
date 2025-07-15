@extends('layouts.app')

@section('title', '语言系统测试')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">语言系统测试</h1>

        <!-- 当前语言状态 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">当前语言状态</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">当前语言</label>
                    <p class="mt-1 text-lg font-semibold text-blue-600">{{ app()->getLocale() }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Session语言</label>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ session('locale', '未设置') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">配置语言</label>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ config('app.locale') }}</p>
                </div>
            </div>
        </div>

        <!-- 语言切换测试 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">语言切换测试</h2>
            <div class="flex flex-wrap gap-3 mb-4">
                <a href="{{ route('language.switch', 'zh_CN') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    中文
                </a>
                <a href="{{ route('language.switch', 'vi') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    Tiếng Việt
                </a>
            </div>
            <p class="text-sm text-gray-600">点击上面的按钮切换语言，然后刷新页面查看效果</p>
        </div>

        <!-- 翻译测试 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">翻译测试</h2>
            <div class="space-y-4">
                <div class="border-b pb-2">
                    <h3 class="font-medium text-gray-900">基础翻译</h3>
                    <p class="text-gray-600">成功: <x-lang key="success" /></p>
                    <p class="text-gray-600">错误: <x-lang key="error" /></p>
                    <p class="text-gray-600">保存: <x-lang key="save" /></p>
                </div>
                
                <div class="border-b pb-2">
                    <h3 class="font-medium text-gray-900">嵌套翻译</h3>
                    <p class="text-gray-600">仪表板标题: <x-lang key="dashboard.title" /></p>
                    <p class="text-gray-600">商品管理: <x-lang key="products.title" /></p>
                    <p class="text-gray-600">库存管理: <x-lang key="inventory.title" /></p>
                </div>
                
                <div class="border-b pb-2">
                    <h3 class="font-medium text-gray-900">缺失翻译测试</h3>
                    <p class="text-gray-600">存在的键: <x-lang key="dashboard.title" /></p>
                    <p class="text-gray-600">不存在的键: <x-lang key="nonexistent.key" /></p>
                    <p class="text-gray-600">带默认值: <x-lang key="nonexistent.key" default="这是默认文本" /></p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-900">参数翻译</h3>
                    <p class="text-gray-600">总记录数: <x-lang key="stock_ins.total_records" :parameters="['count' => 25]" /></p>
                </div>
            </div>
        </div>

        <!-- API测试 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">API测试</h2>
            <div class="space-y-4">
                <div>
                    <button onclick="testCurrentLanguage()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        测试获取当前语言
                    </button>
                    <div id="currentLanguageResult" class="mt-2 p-2 bg-gray-100 rounded text-sm"></div>
                </div>
                
                <div>
                    <button onclick="testMissingTranslations()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        测试获取缺失翻译
                    </button>
                    <div id="missingTranslationsResult" class="mt-2 p-2 bg-gray-100 rounded text-sm"></div>
                </div>
                
                <div>
                    <button onclick="testValidateFiles()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        测试验证翻译文件
                    </button>
                    <div id="validateFilesResult" class="mt-2 p-2 bg-gray-100 rounded text-sm"></div>
                </div>
            </div>
        </div>

        <!-- 语言管理链接 -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">管理工具</h2>
            <div class="space-y-2">
                <a href="{{ route('language.current') }}" target="_blank" class="block text-blue-600 hover:text-blue-800">
                    → 获取当前语言API
                </a>
                <a href="{{ route('language.missing') }}" target="_blank" class="block text-blue-600 hover:text-blue-800">
                    → 获取缺失翻译API
                </a>
                <a href="{{ route('language.validate') }}" target="_blank" class="block text-blue-600 hover:text-blue-800">
                    → 验证翻译文件API
                </a>
                <a href="/language" class="block text-blue-600 hover:text-blue-800">
                    → 语言管理界面
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function testCurrentLanguage() {
    fetch('/language/current')
        .then(response => response.json())
        .then(data => {
            document.getElementById('currentLanguageResult').innerHTML = 
                '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            document.getElementById('currentLanguageResult').innerHTML = 
                '<p class="text-red-600">错误: ' + error.message + '</p>';
        });
}

function testMissingTranslations() {
    fetch('/language/missing')
        .then(response => response.json())
        .then(data => {
            document.getElementById('missingTranslationsResult').innerHTML = 
                '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            document.getElementById('missingTranslationsResult').innerHTML = 
                '<p class="text-red-600">错误: ' + error.message + '</p>';
        });
}

function testValidateFiles() {
    fetch('/language/validate')
        .then(response => response.json())
        .then(data => {
            document.getElementById('validateFilesResult').innerHTML = 
                '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            document.getElementById('validateFilesResult').innerHTML = 
                '<p class="text-red-600">错误: ' + error.message + '</p>';
        });
}
</script>
@endsection 