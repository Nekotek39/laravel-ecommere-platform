@extends('layouts.app', ['title' => 'Finalizacja zamówienia'])

@section('content')
<div class="space-y-6">
    <div class="pb-6 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900">Finalizacja zamówienia</h1>
        <p class="mt-1 text-sm text-gray-500">Wprowadź dane dostawy i potwierdź złożenie zamówienia.</p>
    </div>

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Formularz danych adresowych -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-xs space-y-6">
                <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4">
                    Dane odbiorcy i adres dostawy
                </h2>

                <div class="space-y-4">
                    <!-- Imię i nazwisko -->
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">
                            Imię i nazwisko <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   required 
                                   value="{{ old('full_name', $user->name) }}" 
                                   class="w-full rounded-lg border @error('full_name') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                        </div>
                        @error('full_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telefon -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            Numer telefonu <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   id="phone" 
                                   name="phone" 
                                   required 
                                   placeholder="+48 123 456 789"
                                   value="{{ old('phone') }}" 
                                   class="w-full rounded-lg border @error('phone') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                        </div>
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ulica i numer -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">
                            Ulica i numer lokalu <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   id="address" 
                                   name="address" 
                                   required 
                                   placeholder="np. ul. Kwiatowa 12/4"
                                   value="{{ old('address') }}" 
                                   class="w-full rounded-lg border @error('address') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                        </div>
                        @error('address')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kod pocztowy i Miasto -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700">
                                Kod pocztowy <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="postal_code" 
                                       name="postal_code" 
                                       required 
                                       placeholder="00-000"
                                       value="{{ old('postal_code') }}" 
                                       class="w-full rounded-lg border @error('postal_code') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                            </div>
                            @error('postal_code')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">
                                Miejscowość <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="city" 
                                       name="city" 
                                       required 
                                       placeholder="np. Warszawa"
                                       value="{{ old('city') }}" 
                                       class="w-full rounded-lg border @error('city') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                            </div>
                            @error('city')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Uwagi do zamówienia -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">
                            Uwagi do zamówienia (opcjonalnie)
                        </label>
                        <div class="mt-1">
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Dodatkowe informacje dla kuriera lub sklepu..."
                                      class="w-full rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-xs text-sm">{{ old('notes') }}</textarea>
                        </div>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Podsumowanie koszyka & Przycisk Złóż zamówienie -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-bold text-gray-900">
                            Twoje zamówienie
                        </h2>
                        <a href="{{ route('cart.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
                            Edytuj koszyk
                        </a>
                    </div>

                    <!-- Lista zamawianych produktów -->
                    <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto pr-1">
                        @foreach($items as $item)
                            <div class="py-3 flex items-center justify-between gap-3 text-sm">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $item['product']->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item['quantity'] }} x {{ number_format($item['product']->price, 2, ',', ' ') }} zł</p>
                                </div>
                                <div class="font-semibold text-gray-900 shrink-0">
                                    {{ number_format($item['total'], 2, ',', ' ') }} zł
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Zestawienie kosztów -->
                    <div class="border-t border-gray-200 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Wartość koszyka:</span>
                            <span class="font-medium text-gray-900">{{ number_format($total, 2, ',', ' ') }} zł</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Wysyłka:</span>
                            <span class="text-emerald-600 font-medium">Bezpłatna</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between items-baseline">
                            <span class="text-base font-bold text-gray-900">Do zapłaty:</span>
                            <span class="text-2xl font-black text-indigo-600">
                                {{ number_format($total, 2, ',', ' ') }} zł
                            </span>
                        </div>
                    </div>

                    <!-- Przycisk zatwierdzający -->
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Kupuję i płacę</span>
                    </button>

                    <p class="text-center text-xs text-gray-400">
                        Klikając powyższy przycisk, składasz wiążące zamówienie w naszym sklepie.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
