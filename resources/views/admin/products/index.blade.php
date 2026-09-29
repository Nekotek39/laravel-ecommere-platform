@extends('layouts.admin', ['title' => 'Zarządzanie produktami'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Zarządzanie produktami</h1>
            <p class="mt-1 text-sm text-gray-500">Lista wszystkich produktów w ofercie sklepu.</p>
        </div>
        <div>
            <a href="{{ route('admin.products.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-xs transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Dodaj nowy produkt</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        @if($products->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="mt-2 text-sm font-medium">Brak dodanych produktów.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Miniatura</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Nazwa</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Cena</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Magazyn</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($products as $product)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-gray-400 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                    @if($product->description)
                                        <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $product->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ number_format($product->price, 2, ',', ' ') }} zł
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($product->isInStock())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                            {{ $product->stock }} szt.
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Brak
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('products.show', $product) }}" target="_blank" class="text-gray-500 hover:text-gray-800 text-xs font-medium" title="Podgląd w sklepie">
                                            Podgląd
                                        </a>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs" title="Edytuj">
                                            Edytuj
                                        </a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Czy na pewno chcesz usunąć ten produkt?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold text-xs">
                                                Usuń
                                            </button>
                                        </form>
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
        {{ $products->links() }}
    </div>
</div>
@endsection
