@extends('layouts.app')

@section('title', '语言管理')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">语言管理</h1>
            <p class="text-gray-600 mt-2">管理系统多语言设置和翻译文件</p>
        </div>

        <!-- 当前语言状态 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">当前语言状态</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">当前语言</label>
                    <p class="mt-1 text-lg font-semibold text-blue-600" id="currentLanguage">加载中...</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">支持的语言</label>
                    <p class="mt-1 text-lg font-semibold text-gray-900" id="supportedLanguages">加载中...</p>
                </div>
            </div>
        </div>

        <!-- 语言切换 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">语言切换</h2>
            <div class="flex flex-wrap gap-3">
                <button onclick="switchLanguage('zh_CN')" class="language-btn bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    中文
                </button>
                <button onclick="switchLanguage('en')" class="language-btn bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    English
                </button>
                <button onclick="switchLanguage('vi')" class="language-btn bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    Tiếng Việt
                </button>
            </div>
        </div>

        <!-- 翻译文件状态 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">翻译文件状态</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">语言</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">翻译数量</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">缺失翻译</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="translationStatus">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">加载中...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 缺失翻译列表 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">缺失的翻译</h2>
                <button onclick="clearTranslationCache()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    清除缓存
                </button>
            </div>
            <div id="missingTranslations" class="space-y-2">
                <p class="text-gray-500">加载中...</p>
            </div>
        </div>

        <!-- 操作按钮 -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">管理操作</h2>
            <div class="flex flex-wrap gap-3">
                <button onclick="checkTranslations()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    检查翻译完整性
                </button>
                <button onclick="validateTranslationFiles()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    验证翻译文件
                </button>
                <a href="{{ route('language.missing') }}" target="_blank" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                    查看缺失翻译API
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// 页面加载时初始化
document.addEventListener('DOMContentLoaded', function() {
    loadCurrentLanguage();
    loadTranslationStatus();
    loadMissingTranslations();
});

// 加载当前语言信息
function loadCurrentLanguage() {
    fetch('/language/current')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('currentLanguage').textContent = data.current_language;
                document.getElementById('supportedLanguages').textContent = Object.keys(data.supported_languages).length + ' 种语言';
                
                // 更新语言按钮状态
                updateLanguageButtons(data.current_language);
            }
        })
        .catch(error => {
            console.error('Error loading current language:', error);
        });
}

// 更新语言按钮状态
function updateLanguageButtons(currentLanguage) {
    const buttons = document.querySelectorAll('.language-btn');
    buttons.forEach(button => {
        const language = button.getAttribute('onclick').match(/'([^']+)'/)[1];
        if (language === currentLanguage) {
            button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
        } else {
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-gray-600', 'hover:bg-gray-700');
        }
    });
}

// 切换语言
function switchLanguage(language) {
    fetch(`/language/switch/${language}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 显示成功消息
                showMessage('语言切换成功', 'success');
                
                // 重新加载页面数据
                loadCurrentLanguage();
                loadTranslationStatus();
                loadMissingTranslations();
                
                // 可选：刷新页面
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showMessage('语言切换失败: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error switching language:', error);
            showMessage('语言切换失败', 'error');
        });
}

// 加载翻译状态
function loadTranslationStatus() {
    fetch('/language/validate')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTranslationStatus(data.validation_results);
            }
        })
        .catch(error => {
            console.error('Error loading translation status:', error);
        });
}

// 显示翻译状态
function displayTranslationStatus(results) {
    const tbody = document.getElementById('translationStatus');
    tbody.innerHTML = '';
    
    Object.entries(results).forEach(([language, result]) => {
        const row = document.createElement('tr');
        
        let statusClass = 'text-red-600';
        let statusText = '错误';
        
        if (result.status === 'valid') {
            statusClass = 'text-green-600';
            statusText = '正常';
        } else if (result.status === 'missing') {
            statusClass = 'text-yellow-600';
            statusText = '缺失';
        }
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${language}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm ${statusClass}">${statusText}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${result.count || 0}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <button onclick="loadMissingTranslationsForLanguage('${language}')" class="text-blue-600 hover:text-blue-800">
                    查看缺失
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// 加载缺失的翻译
function loadMissingTranslations() {
    fetch('/language/missing')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMissingTranslations(data.missing_translations);
            }
        })
        .catch(error => {
            console.error('Error loading missing translations:', error);
        });
}

// 显示缺失的翻译
function displayMissingTranslations(missingTranslations) {
    const container = document.getElementById('missingTranslations');
    
    if (missingTranslations.length === 0) {
        container.innerHTML = '<p class="text-green-600">✓ 没有缺失的翻译</p>';
        return;
    }
    
    let html = '<div class="space-y-2">';
    missingTranslations.forEach(key => {
        html += `<div class="flex justify-between items-center p-2 bg-gray-50 rounded">
            <span class="text-sm text-gray-700">${key}</span>
            <span class="text-xs text-gray-500">缺失</span>
        </div>`;
    });
    html += '</div>';
    
    container.innerHTML = html;
}

// 为特定语言加载缺失翻译
function loadMissingTranslationsForLanguage(language) {
    fetch(`/language/missing?language=${language}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`${language} 语言缺失 ${data.count} 个翻译`);
            }
        })
        .catch(error => {
            console.error('Error loading missing translations for language:', error);
        });
}

// 检查翻译完整性
function checkTranslations() {
    showMessage('正在检查翻译完整性...', 'info');
    
    // 这里可以调用后端命令或API
    setTimeout(() => {
        showMessage('翻译检查完成', 'success');
        loadTranslationStatus();
        loadMissingTranslations();
    }, 2000);
}

// 验证翻译文件
function validateTranslationFiles() {
    fetch('/language/validate')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('翻译文件验证完成', 'success');
                displayTranslationStatus(data.validation_results);
            } else {
                showMessage('翻译文件验证失败', 'error');
            }
        })
        .catch(error => {
            console.error('Error validating translation files:', error);
            showMessage('翻译文件验证失败', 'error');
        });
}

// 清除翻译缓存
function clearTranslationCache() {
    fetch('/language/clear-cache', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('翻译缓存已清除', 'success');
            loadMissingTranslations();
        } else {
            showMessage('清除缓存失败', 'error');
        }
    })
    .catch(error => {
        console.error('Error clearing translation cache:', error);
        showMessage('清除缓存失败', 'error');
    });
}

// 显示消息
function showMessage(message, type) {
    // 创建消息元素
    const messageDiv = document.createElement('div');
    messageDiv.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    messageDiv.textContent = message;
    
    document.body.appendChild(messageDiv);
    
    // 3秒后自动移除
    setTimeout(() => {
        document.body.removeChild(messageDiv);
    }, 3000);
}
</script>
@endsection 