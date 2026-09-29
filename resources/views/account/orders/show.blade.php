@extends('layouts.app', ['title' => 'Zamówienie #' . $order->id])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb / Powrót -->
    <nav class="flex text-sm text-gray-500 items-center gap-2">
        <a href="{{ route('account.orders.index') }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Wróć do listy zamówień</span>
        </a>
    </nav>

    <!-- Nagłówek zamówienia -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Zamówienie #{{ $order->id }}</h1>
                @php
                    $statusClass = match ($order->status) {
                        \App\Enums\OrderStatus::Pending => 'bg-amber-100 text-amber-800 border-amber-200',
                        \App\Enums\OrderStatus::Processing => 'bg-blue-100 text-blue-800 border-blue-200',
                        \App\Enums\OrderStatus::Shipped => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                        \App\Enums\OrderStatus::Completed => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        \App\Enums\OrderStatus::Cancelled => 'bg-red-100 text-red-800 border-red-200',
                        default => 'bg-gray-100 text-gray-800 border-gray-200',
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                    {{ $order->status->label() }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">Złożone w dniu: {{ $order->created_at->format('d.m.Y, H:i') }}</p>
        </div>

        <div class="text-right">
            <div class="text-xs text-gray-500 font-medium">Całkowity koszt:</div>
            <div class="text-2xl font-black text-indigo-600">
                {{ number_format($order->total, 2, ',', ' ') }} zł
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pozycje zamówienia -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Zakupione produkty</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produkt</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Cena</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Ilość</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Razem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    @if($item->product)
                                        <a href="{{ route('products.show', $item->product) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $item->product_name }}
                                        </a>
                                    @else
                                        {{ $item->product_name }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-center text-gray-600 whitespace-nowrap">
                                    {{ number_format($item->price, 2, ',', ' ') }} zł
                                </td>
                                <td class="px-6 py-4 text-sm text-center text-gray-900 font-semibold whitespace-nowrap">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-gray-900 whitespace-nowrap">
                                    {{ number_format($item->total(), 2, ',', ' ') }} zł
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50/50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-700 text-right">Łącznie:</td>
                            <td class="px-6 py-4 text-sm font-black text-indigo-600 text-right">
                                {{ number_format($order->total, 2, ',', ' ') }} zł
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Dane odbiorcy i wysyłki -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
            <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4">
                Dane dostawy
            </h2>

            <div class="space-y-4 text-sm">
                <div>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Odbiorca</span>
                    <span class="font-semibold text-gray-900">{{ $order->full_name }}</span>
                </div>

                <div>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon</span>
                    <span class="text-gray-900 font-medium">{{ $order->phone }}</span>
                </div>

                <div>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Adres doręczenia</span>
                    <span class="text-gray-900 font-medium block">{{ $order->address }}</span>
                    <span class="text-gray-900 font-medium block">{{ $order->postal_code }} {{ $order->city }}</span>
                </div>

                @if($order->notes)
                    <div class="pt-2 border-t border-gray-100">
                        <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Uwagi do zamówienia</span>
                        <p class="mt-1 text-gray-700 bg-gray-50 p-3 rounded-lg text-xs italic">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
