<section>
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">
            {{ __("messages.profile.personal_info") }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("messages.profile.update_account_info") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">{{ __("messages.users.username") }}</label>
            <input id="username" name="username" type="text" class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 shadow-sm px-4 py-3 text-gray-900 placeholder-gray-400 transition-all duration-200" value="{{ old('username', $user->username) }}" required autofocus autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <label for="real_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __("messages.users.real_name") }}</label>
            <input id="real_name" name="real_name" type="text" class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 shadow-sm px-4 py-3 text-gray-900 placeholder-gray-400 transition-all duration-200" value="{{ old('real_name', $user->real_name) }}" autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('real_name')" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __("messages.users.email_address") }}</label>
            <input id="email" name="email" type="email" class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 shadow-sm px-4 py-3 text-gray-900 placeholder-gray-400 transition-all duration-200" value="{{ old('email', $user->email) }}" autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __("messages.profile.phone_number") }}</label>
            <input id="phone" name="phone" type="text" class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 shadow-sm px-4 py-3 text-gray-900 placeholder-gray-400 transition-all duration-200" value="{{ old('phone', $user->phone) }}" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-4 mt-6">
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-xl hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-4 focus:ring-green-500/20 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-check-circle mr-2"></i>
                {{ __("messages.save") }}
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-green-600 flex items-center">
                    <i class="bi bi-check-circle-fill mr-2"></i>
                    {{ __("messages.profile.save_success") }}
                </p>
            @endif
        </div>
    </form>
</section> 