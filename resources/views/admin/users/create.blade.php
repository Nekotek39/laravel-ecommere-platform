@extends('layouts.admin', ['title' => 'Dodaj użytkownika'])

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 items-center gap-2">
        <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Lista użytkowników</span>
        </a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Nowy użytkownik</span>
    </nav>

    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-xs">
        <div class="pb-6 border-b border-gray-100">
            <h1 class="text-2xl font-bold text-gray-900">Utwórz konto użytkownika</h1>
            <p class="mt-1 text-sm text-gray-500">Wprowadź dane do rejestracji nowego użytkownika oraz przypisz odpowiednią rolę.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 space-y-6">
            @csrf

            <!-- Imię i nazwisko -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Imię i nazwisko <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="text"
                           id="name"
                           name="name"
                           required
                           value="{{ old('name') }}"
                           class="w-full p-2 rounded-lg border @error('name') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                </div>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Adres e-mail -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Adres e-mail <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="email"
                           id="email"
                           name="email"
                           required
                           value="{{ old('email') }}"
                           class="w-full p-2 rounded-lg border @error('email') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                </div>
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rola w systemie -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">
                    Rola systemowa <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <select id="role"
                            name="role"
                            required
                            class="w-full p-2 rounded-lg border @error('role') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                        @foreach($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', \App\Enums\UserRole::Customer->value) === $role->value)>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('role')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hasło i Powtórzenie hasła -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Hasło (min. 8 znaków) <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               class="w-full p-2 rounded-lg border @error('password') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Powtórz hasło <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               required
                               class="w-full p-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-xs text-sm">
                    </div>
                </div>
            </div>

            <!-- Przyciski akcji -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    Anuluj
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-xs transition-colors">
                    Utwórz konto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
