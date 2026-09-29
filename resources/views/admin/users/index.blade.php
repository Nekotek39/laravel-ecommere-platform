@extends('layouts.admin', ['title' => 'Zarządzanie użytkownikami'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Użytkownicy</h1>
            <p class="mt-1 text-sm text-gray-500">Zarządzaj kontami klientów, moderatorów i administratorów.</p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-xs transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span>Dodaj użytkownika</span>
            </a>
        </div>
    </div>

    <!-- Wyszukiwarka użytkowników -->
    <div class="flex items-center justify-between">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 max-w-md w-full">
            <div class="relative flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Szukaj po nazwisku lub e-mailu..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-xs bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-xs transition-colors">
                Filtruj
            </button>
            @if(filled($search))
                <a href="{{ route('admin.users.index') }}" 
                   class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Wyczyść
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        @if($users->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <p class="text-sm font-medium">Nie znaleziono żadnych użytkowników.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Użytkownik</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Rola</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Data rejestracji</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">#{{ $user->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $roleClass = match ($user->role) {
                                            \App\Enums\UserRole::Admin => 'bg-purple-100 text-purple-800 border-purple-200',
                                            \App\Enums\UserRole::Moderator => 'bg-blue-100 text-blue-800 border-blue-200',
                                            \App\Enums\UserRole::Customer => 'bg-gray-100 text-gray-800 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $roleClass }}">
                                        {{ $user->role->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('d.m.Y, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-gray-600 hover:text-gray-900 font-medium text-xs">
                                            Szczegóły
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs">
                                            Edytuj
                                        </a>
                                        @if(!auth()->user()->is($user))
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-semibold text-xs">
                                                    Usuń
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
@endsection
