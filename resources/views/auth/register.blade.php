@extends('layouts.app', ['title' => 'Rejestracja konta'])

@section('content')
<div class="min-h-[70vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="text-center text-3xl font-extrabold text-gray-900">
            Załóż nowe konto
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Masz już konto?
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                Zaloguj się tutaj
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-sm border border-gray-200 sm:rounded-xl sm:px-10">
            <form class="space-y-6" method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Imię i nazwisko / Nazwa -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Imię i nazwisko
                    </label>
                    <div class="mt-1">
                        <input id="name" 
                               name="name" 
                               type="text" 
                               autocomplete="name" 
                               required 
                               value="{{ old('name') }}"
                               class="appearance-none block w-full px-3 py-2 border @error('name') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror rounded-lg shadow-xs placeholder-gray-400 sm:text-sm">
                    </div>
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

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
                               autocomplete="new-password" 
                               required 
                               class="appearance-none block w-full px-3 py-2 border @error('password') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror rounded-lg shadow-xs placeholder-gray-400 sm:text-sm">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Potwierdzenie hasła -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Powtórz hasło
                    </label>
                    <div class="mt-1">
                        <input id="password_confirmation" 
                               name="password_confirmation" 
                               type="password" 
                               autocomplete="new-password" 
                               required 
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-xs placeholder-gray-400 sm:text-sm">
                    </div>
                </div>

                <!-- Przycisk Zarejestruj -->
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-xs text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Zarejestruj się
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
