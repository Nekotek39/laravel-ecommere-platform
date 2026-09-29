@extends('layouts.app', ['title' => 'Koszyk'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-6 border-b border-gray-200">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Twój koszyk</h1>
            <p class="mt-1 text-sm text-gray-500">Przejrzyj wybrane produkty przed złożeniem zamówienia.</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kontynuuj zakupy</span>
        </a>
    </div>

    @if($items->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-200 p-8 shadow-xs">
            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">Twój koszyk jest pusty</h3>
            <p class="mt-1 text-sm text-gray-500">Nie dodałeś jeszcze żadnych produktów do swojego koszyka.</p>
            <div class="mt-6">
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 border border-transparent text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition-colors">
                    Przeglądaj produkty
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Lista produktów w koszyku -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="divide-y divide-gray-200">
                    @foreach($items as $item)
                        @php
                            $product = $item['product'];
                            $quantity = $item['quantity'];
                            $itemTotal = $item['total'];
                        @endphp
                        <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <!-- Zdjęcie i dane produktu -->
                            <div class="flex items-center gap-4 flex-1">
                                <a href="{{ route('products.show', $product) }}" class="shrink-0 w-20 h-20 bg-gray-100 rounded-lg overflow-hidden border border-gray-200 flex items-center justify-center">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-8 h-8 text-gray-400 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    @endif
                                </a>

                                <div class="space-y-1">
                                    <h3 class="text-base font-semibold text-gray-900">
                                        <a href="{{ route('products.show', $product) }}" class="hover:text-indigo-600 transition-colors">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    <div class="text-xs text-gray-500">
                                        Cena jedn.: <span class="font-medium text-gray-800">{{ number_format($product->price, 2, ',', ' ') }} zł</span>
                                    </div>
                                    @if($quantity > $product->stock)
                                        <span class="inline-block text-[11px] font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-sm">
                                            Uwaga: Dostępne tylko {{ $product->stock }} szt. w magazynie
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Zmiana ilości i usuwanie -->
                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                                <form method="POST" action="{{ route('cart.update', $product) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" 
                                           name="quantity" 
                                           value="{{ $quantity }}" 
                                           min="1" 
                                           max="99" 
                                           class="w-16 rounded-lg border-gray-300 text-sm font-medium focus:border-indigo-500 focus:ring-indigo-500 shadow-xs text-center py-1.5">
                                    <button type="submit" 
                                            title="Zaktualizuj ilość"
                                            class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </form>

                                <div class="text-right min-w-[90px]">
                                    <div class="text-base font-bold text-gray-900">
                                        {{ number_format($itemTotal, 2, ',', ' ') }} zł
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('cart.destroy', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            title="Usuń z koszyka" 
                                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Podsumowanie koszyka -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                    <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4">
                        Podsumowanie
                    </h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Liczba pozycji:</span>
                            <span class="font-medium text-gray-900">{{ $items->count() }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Suma sztuk:</span>
                            <span class="font-medium text-gray-900">{{ $items->sum('quantity') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Dostawa:</span>
                            <span class="text-emerald-600 font-medium">Darmowa</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between items-baseline">
                            <span class="text-base font-bold text-gray-900">Łącznie do zapłaty:</span>
                            <span class="text-2xl font-black text-indigo-600">
                                {{ number_format($total, 2, ',', ' ') }} zł
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.create') }}" 
                       class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition-colors">
                        <span>Przejdź do kasy</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
