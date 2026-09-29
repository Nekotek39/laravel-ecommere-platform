@extends('layouts.admin', ['title' => 'Edycja produktu: ' . $product->name])

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 items-center gap-2">
        <a href="{{ route('admin.products.index') }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Lista produktów</span>
        </a>
        <span>/</span>
        <span class="text-gray-900 font-medium truncate">Edycja: {{ $product->name }}</span>
    </nav>

    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-xs">
        <div class="pb-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edycja produktu</h1>
                <p class="mt-1 text-sm text-gray-500">Zaktualizuj dane i parametry produktu.</p>
            </div>
            <a href="{{ route('products.show', $product) }}" target="_blank" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                Podgląd w sklepie &rarr;
            </a>
        </div>

        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Nazwa produktu -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nazwa produktu <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="text"
                           id="name"
                           name="name"
                           required
                           value="{{ old('name', $product->name) }}"
                           class="w-full p-2 rounded-lg border @error('name') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                </div>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cena i Magazyn -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">
                        Cena (PLN) <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-lg shadow-xs">
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               max="999999.99"
                               id="price"
                               name="price"
                               required
                               value="{{ old('price', $product->price) }}"
                               class="w-full p-2 rounded-lg border @error('price') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror text-sm pr-12">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 text-sm">
                            PLN
                        </div>
                    </div>
                    @error('price')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">
                        Stan magazynowy (szt.) <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input type="number"
                               min="0"
                               id="stock"
                               name="stock"
                               required
                               value="{{ old('stock', $product->stock) }}"
                               class="w-full p-2 rounded-lg border @error('stock') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror shadow-xs text-sm">
                    </div>
                    @error('stock')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Zdjęcie produktu i podgląd -->
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">
                    Zdjęcie produktu
                </label>

                @if($product->image)
                    <div class="mt-2 flex items-center gap-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded-md">
                        <div class="text-xs text-gray-500">
                            Aktualne zdjęcie. Wgranie nowego pliku zastąpi dotychczasowe.
                        </div>
                    </div>
                @endif

                <div class="mt-2">
                    <input type="file"
                           id="image"
                           name="image"
                           accept="image/png,image/jpeg,image/webp"
                           class="block p-2 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
                <p class="mt-1 text-xs text-gray-400">Dopuszczalne formaty: JPG, JPEG, PNG, WEBP (maks. 2MB).</p>
                @error('image')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Opis produktu -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    Opis produktu
                </label>
                <div class="mt-1">
                    <textarea id="description"
                              name="description"
                              rows="5"
                              class="w-full p-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-xs text-sm">{{ old('description', $product->description) }}</textarea>
                </div>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Przyciski akcji -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.products.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    Anuluj
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-xs transition-colors">
                    Zapisz zmiany
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
