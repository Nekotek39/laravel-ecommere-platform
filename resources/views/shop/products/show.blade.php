@extends('layouts.app', ['title' => $product->name])
<!-- Strona szczegółowa produktu -->
@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 items-center gap-2">
        <a href="{{ route('products.index') }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Wszystkie produkty</span>
        </a>
        <span>/</span>
        <span class="text-gray-900 font-medium truncate">{{ $product->name }}</span>
    </nav>

    <!-- Karta szczegółów produktu -->
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6 lg:p-10">
            <!-- Kolumna ze zdjęciem -->
            <div class="space-y-4">
                <div class="aspect-4/3 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden relative flex items-center justify-center">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="flex flex-col items-center justify-center text-gray-400 p-8 text-center">
                            <!--Ikonka zdjęcia-->
                            <svg class="w-20 h-20 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm mt-2 font-medium">Brak zdjęcia dla tego produktu</span>
                        </div>
                    @endif

                    @if(!$product->isInStock())
                        <div class="absolute top-4 right-4 bg-red-600 text-white text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-md shadow-xs">
                            Produkt niedostępny
                        </div>
                    @endif
                </div>

                @auth
                    @if(auth()->user()->canManageProducts())
                        <div class="flex justify-end pt-2">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-2 rounded-lg transition-colors">
                                <!--Ikonka edycji-->
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Edytuj w panelu admina</span>
                            </a>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- Kolumna z informacjami i zakupem -->
            <div class="flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ $product->name }}
                    </h1>

                    <div class="flex items-center gap-4">
                        <div class="text-3xl font-black text-indigo-600">
                            {{ number_format($product->price, 2, ',', ' ') }} zł
                        </div>

                        <div>
                            @if($product->isInStock())
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    W magazynie: {{ $product->stock }} szt.
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    Brak w magazynie
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Opis -->
                    <div class="pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-2">Opis produktu</h3>
                        @if($product->description)
                            <div class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">
                                {{ $product->description }}
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic">Ten produkt jeszcze nie posiada szczegółowego opisu.</p>
                        @endif
                    </div>
                </div>

                <!-- Formularz zakupu -->
                <div class="pt-6 border-t border-gray-200">
                    <form method="POST" action="{{ route('cart.store', $product) }}" class="space-y-4">
                        @csrf
                        <div class="flex items-center gap-4">
                            <div class="w-32">
                                <label for="quantity" class="block text-xs font-medium text-gray-700 mb-1">
                                    Ilość:
                                </label>
                                <input type="number"
                                       id="quantity"
                                       name="quantity"
                                       value="1"
                                       min="1"
                                       max="{{ max(1, $product->stock) }}"
                                       @disabled(!$product->isInStock())
                                       class="w-full rounded-lg border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium disabled:bg-gray-100 disabled:cursor-not-allowed">
                            </div>

                            <div class="flex-1 self-end">
                                <button type="submit"
                                        @disabled(!$product->isInStock())
                                        class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-lg text-sm font-semibold text-white shadow-xs transition-colors {{ !$product->isInStock() ? 'bg-gray-300 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2' }}">
                                    <!--Ikonka koszyka-->
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span>{{ !$product->isInStock() ? 'Produkt wyprzedany' : 'Dodaj do koszyka' }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
