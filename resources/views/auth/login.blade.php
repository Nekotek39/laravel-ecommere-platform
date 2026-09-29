@extends('layouts.app', ['title' => 'Logowanie'])

@section('content')
<div class="min-h-[70vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="text-center text-3xl font-extrabold text-gray-900">
            Zaloguj się do konta
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Lub
            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                utwórz nowe bezpłatne konto
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-sm border border-gray-200 sm:rounded-xl sm:px-10">
            <form class="space-y-6" method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Adres e-mail -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Adres e-mail
                    </label>
                    <div class="mt-1">
                        <input id="email" 
                               name="email" 
                               type="email" 
                               autocomplete="email" 
                               required 
                               value="{{ old('email') }}"
                               class="appearance-none block w-full px-3 py-2 border @error('email') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror rounded-lg shadow-xs placeholder-gray-400 sm:text-sm">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hasło -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Hasło
                    </label>
                    <div class="mt-1">
                        <input id="password" 
                               name="password" 
                               type="password" 
                               autocomplete="current-password" 
                               required 
                               class="appearance-none block w-full px-3 py-2 border @error('password') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror rounded-lg shadow-xs placeholder-gray-400 sm:text-sm">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Zapamiętaj mnie -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" 
                               name="remember" 
                               type="checkbox" 
                               value="1"
                               {{ old('remember') ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Zapamiętaj mnie
                        </label>
                    </div>
                </div>

                <!-- Przycisk zaloguj -->
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-xs text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Zaloguj się
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
