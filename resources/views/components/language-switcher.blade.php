<div class="relative inline-block text-left" x-data="{ open: false }">
    <div>
        <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 border-2 border-blue-300 shadow-lg text-sm leading-4 font-bold rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200" id="language-menu" aria-expanded="true" aria-haspopup="true">
            <i class="bi bi-globe mr-2 text-blue-600 text-lg"></i>
            @switch(app()->getLocale())
                @case('zh_CN')
                    <span class="text-blue-700 font-bold">🇨🇳 中文</span>
                    @break
                @case('en')
                    <span class="text-blue-700 font-bold">🇺🇸 English</span>
                    @break
                @case('vi')
                    <span class="text-blue-700 font-bold">🇻🇳 Tiếng Việt</span>
                    @break
                @default
                    <span class="text-blue-700 font-bold">{{ strtoupper(app()->getLocale()) }}</span>
            @endswitch
            <i class="bi bi-chevron-down ml-2 text-blue-500"></i>
        </button>
    </div>

    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" role="menu" aria-orientation="vertical" aria-labelledby="language-menu" style="display: none;">
        <div class="py-2" role="none">
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                选择语言 / Select Language / Chọn ngôn ngữ
            </div>
            <a href="{{ route('language.switch', 'zh_CN') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ app()->getLocale() == 'zh_CN' ? 'bg-blue-50 text-blue-700' : '' }}" role="menuitem">
                <span class="text-lg mr-3">🇨🇳</span>
                <div>
                    <div class="font-semibold">中文</div>
                    <div class="text-xs text-gray-500">Chinese</div>
                </div>
                <i class="bi bi-check {{ app()->getLocale() == 'zh_CN' ? 'text-blue-600 ml-auto' : 'invisible ml-auto' }}"></i>
            </a>
            <a href="{{ route('language.switch', 'en') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ app()->getLocale() == 'en' ? 'bg-blue-50 text-blue-700' : '' }}" role="menuitem">
                <span class="text-lg mr-3">🇺🇸</span>
                <div>
                    <div class="font-semibold">English</div>
                    <div class="text-xs text-gray-500">English</div>
                </div>
                <i class="bi bi-check {{ app()->getLocale() == 'en' ? 'text-blue-600 ml-auto' : 'invisible ml-auto' }}"></i>
            </a>
            <a href="{{ route('language.switch', 'vi') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ app()->getLocale() == 'vi' ? 'bg-blue-50 text-blue-700' : '' }}" role="menuitem">
                <span class="text-lg mr-3">🇻🇳</span>
                <div>
                    <div class="font-semibold">Tiếng Việt</div>
                    <div class="text-xs text-gray-500">Vietnamese</div>
                </div>
                <i class="bi bi-check {{ app()->getLocale() == 'vi' ? 'text-blue-600 ml-auto' : 'invisible ml-auto' }}"></i>
            </a>
        </div>
    </div>
</div> 