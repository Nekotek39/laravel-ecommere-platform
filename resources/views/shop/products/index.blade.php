@extends('layouts.app', ['title' => 'Katalog produktów'])
<!-- Strona główna z produktami -->
@section('content')
<div class="space-y-8">
    <!-- Nagłówek i Wyszukiwarka -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-200">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Katalog produktów</h1>
            <p class="mt-1 text-sm text-gray-500">Zobacz naszą ofertę i złóż zamówienie już dziś!</p>
        </div>

        <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2 max-w-md w-full">
            <div class="relative flex-1">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Szukaj produktu po nazwie..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <!--Ikonka wyszukiwania-->
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-xs transition-colors">
                Szukaj
            </button>
            @if(filled($search))
                <a href="{{ route('products.index') }}"
                   class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Wyczyść
                </a>
            @endif
        </form>
    </div>

    @if(filled($search))
        <div class="text-sm text-gray-600">
            Wyniki wyszukiwania dla: <strong class="text-gray-900">"{{ $search }}"</strong>
        </div>
    @endif

    <!-- Lista produktów -->
    @if($products->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl border border-gray-200 p-8 shadow-xs">
            <!--Brakujące produkty pudełko-->
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-4 text-base font-semibold text-gray-900">Brak produktów</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(filled($search))
                    Nie znaleziono produktów pasujących do wyszukiwania. Spróbuj innego hasła.
                @else
                    W tej chwili w sklepie nie ma dostępnych produktów.
                @endif
            </p>
            @if(filled($search))
                <div class="mt-6">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                        Wróć do katalogu
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <!-- Zdjęcie produktu -->
                        <a href="{{ route('products.show', $product) }}" class="block aspect-4/3 bg-gray-100 relative overflow-hidden group">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}"
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 bg-gray-50 group-hover:bg-gray-100 transition-colors">
                                    <!--Ikonka zdjęcia-->
                                    <svg class="w-12 h-12 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs mt-1">Brak zdjęcia</span>
                                </div>
                            @endif

                            @if(!$product->isInStock())
                                <div class="absolute top-2 right-2 bg-red-600 text-white text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm shadow-xs">
                                    Brak w magazynie
                                </div>
                            @endif
                        </a>

                        <!-- Informacje o produkcie -->
                        <div class="p-4">
                            <h2 class="text-base font-semibold text-gray-900 group">
                                <a href="{{ route('products.show', $product) }}" class="hover:text-indigo-600 transition-colors line-clamp-1">
                                    {{ $product->name }}
                                </a>
                            </h2>

                            @if($product->description)
                                <p class="mt-1 text-xs text-gray-500 line-clamp-2">
                                    {{ $product->description }}
                                </p>
                            @endif

                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-lg font-bold text-gray-900">
                                    {{ number_format($product->price, 2, ',', ' ') }} zł
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($product->isInStock())
                                        Stan: <span class="font-medium text-emerald-600">{{ $product->stock }} szt.</span>
                                    @else
                                        <span class="text-red-500 font-medium">Niedostępny</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dodawanie do koszyka -->
                    <div class="p-4 pt-0">
                        <form method="POST" action="{{ route('cart.store', $product) }}">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit"
                                    @disabled(!$product->isInStock())
                                    class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-semibold shadow-xs transition-colors {{ !$product->isInStock() ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 text-white' }}">
                                <!--Ikonka koszyka-->
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>{{ !$product->isInStock() ? 'Wyprzedane' : 'Dodaj do koszyka' }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginacja -->
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
