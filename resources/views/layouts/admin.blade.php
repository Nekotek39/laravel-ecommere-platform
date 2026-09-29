<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Panel Administracyjny' }} - E-Commerce Platform</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-gray-900 bg-gray-100">
    <!-- Górny pasek nawigacyjny panelu -->
    <header class="bg-gray-900 text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Lewa strona: Logo & Menu -->
                <div class="flex items-center gap-8">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('admin.products.index') }}" 
                       class="flex items-center gap-2 font-bold text-lg text-white tracking-wide">
                        <span class="bg-indigo-600 text-white p-1.5 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <span>Panel Zarządzania</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-1">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                Pulpit
                            </a>
                        @endif

                        <a href="{{ route('admin.products.index') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            Produkty
                        </a>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                Użytkownicy
                            </a>

                            <a href="{{ route('admin.orders.index') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                Zamówienia
                            </a>
                        @endif
                    </nav>
                </div>

                <!-- Prawa strona -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('products.index') }}" 
                       class="text-xs font-medium text-gray-300 hover:text-white flex items-center gap-1 bg-gray-800 hover:bg-gray-700 px-3 py-1.5 rounded-md transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Przejdź do sklepu</span>
                    </a>

                    <div class="flex items-center gap-3 pl-3 border-l border-gray-700">
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-semibold text-white">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-gray-400 capitalize">{{ auth()->user()->role->label() }}</div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-xs font-medium text-red-400 hover:text-red-300 bg-red-950/40 hover:bg-red-900/60 border border-red-800/50 px-2.5 py-1.5 rounded-md transition-colors">
                                Wyloguj
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Komunikaty Flash i Błędy -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="flex items-center p-4 mb-4 text-green-800 bg-green-50 border border-green-200 rounded-lg shadow-xs" role="alert">
                <svg class="shrink-0 w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm font-medium">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center p-4 mb-4 text-red-800 bg-red-50 border border-red-200 rounded-lg shadow-xs" role="alert">
                <svg class="shrink-0 w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm font-medium">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 mb-4 text-red-800 bg-red-50 border border-red-200 rounded-lg shadow-xs">
                <div class="flex items-center mb-2">
                    <svg class="shrink-0 w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-sm">Wystąpiły błędy w formularzu:</span>
                </div>
                <ul class="list-disc list-inside text-sm text-red-700 pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Główna zawartość strony admina -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @yield('content')
    </main>
</body>
</html>
