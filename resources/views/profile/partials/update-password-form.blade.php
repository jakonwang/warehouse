<section>
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">
            {{ __("messages.profile.change_password") }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("messages.profile.change_password_tip") }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __("messages.profile.current_password") }}</label>
            <input id="current_password" name="current_password" type="password" class="w-full rounded-xl border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 shadow-sm px-4 py-3 text-gray-900 placeholder-gray-400 transition-all duration-200" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __("messages.profile.new_password") }}</label>
            <input id="password" name="password" type="password" class="w-full rounded-xl border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 shadow-sm px-4 py-3 text-gray-900 placeholder-gray-400 transition-all duration-200" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __("messages.profile.password_confirmation") }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-xl border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 shadow-sm px-4 py-3 text-gray-900 placeholder-gray-400 transition-all duration-200" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 mt-6">
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-600 to-orange-700 text-white font-semibold rounded-xl hover:from-yellow-700 hover:to-orange-800 focus:outline-none focus:ring-4 focus:ring-yellow-500/20 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-shield-check mr-2"></i>
                {{ __("messages.profile.change_password") }}
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-green-600 flex items-center">
                    <i class="bi bi-check-circle-fill mr-2"></i>
                    {{ __("messages.profile.password_update_success") }}
                </p>
            @endif
        </div>
    </form>
</section> 