<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Sklep Internetowy' }} - E-Commerce Platform</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-gray-900 bg-gray-50">
    <!-- Header / Nawigacja -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo & Linki główne -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('products.index') }}" class="flex items-center gap-2 text-indigo-600 font-bold text-xl tracking-tight">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>E-Sklep</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-6">
                        <a href="{{ route('products.index') }}" 
                           class="text-sm font-medium transition-colors {{ request()->routeIs('products.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                            Katalog produktów
                        </a>
                    </nav>
                </div>

                <!-- Prawa strona nawigacji -->
                <div class="flex items-center gap-4">
                    <!-- Koszyk -->
                    <a href="{{ route('cart.index') }}" 
                       class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="hidden sm:inline">Koszyk</span>
                        @php
                            $cartCount = app(\App\Services\Cart\CartService::class)->count();
                        @endphp
                        @if($cartCount > 0)
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-indigo-600 rounded-full">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>

                    @auth
                        <!-- Moje zamówienia -->
                        <a href="{{ route('account.orders.index') }}" 
                           class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors hidden sm:inline-block">
                            Moje zamówienia
                        </a>

                        <!-- Panel administracyjny (jeśli moderator/admin) -->
                        @if(auth()->user()->canManageProducts())
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('admin.products.index') }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md bg-amber-100 text-amber-800 hover:bg-amber-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Panel Admina</span>
                            </a>
                        @endif

                        <!-- Profil użytkownika & Wyloguj -->
                        <div class="flex items-center gap-3 pl-2 border-l border-gray-200">
                            <span class="text-sm text-gray-600 hidden sm:inline">
                                Witaj, <strong class="text-gray-900">{{ auth()->user()->name }}</strong>
                            </span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="text-sm font-medium text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors">
                                    Wyloguj
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Gość: Logowanie i Rejestracja -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('login') }}" 
                               class="text-sm font-medium text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                Zaloguj się
                            </a>
                            <a href="{{ route('register') }}" 
                               class="text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg shadow-xs transition-colors">
                                Załóż konto
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Komunikaty Flash i Błędy -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="flex items-center p-4 mb-4 text-green-800 bg-green-50 border border-green-200 rounded-lg" role="alert">
                <svg class="shrink-0 w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm font-medium">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center p-4 mb-4 text-red-800 bg-red-50 border border-red-200 rounded-lg" role="alert">
                <svg class="shrink-0 w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm font-medium">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 mb-4 text-red-800 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <svg class="shrink-0 w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-sm">Wystąpiły błędy podczas wypełniania formularza:</span>
                </div>
                <ul class="list-disc list-inside text-sm text-red-700 pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Główna zawartość strony -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @yield('content')
    </main>

    <!-- Stopka -->
    <footer class="bg-white border-t border-gray-200 mt-auto py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} Platforma E-Commerce. Wszelkie prawa zastrzeżone.</p>
        </div>
    </footer>
</body>
</html>
