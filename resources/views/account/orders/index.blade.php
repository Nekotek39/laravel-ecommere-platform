@extends('layouts.app', ['title' => 'Moje zamówienia'])

@section('content')
<div class="space-y-6">
    <div class="pb-6 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900">Moje zamówienia</h1>
        <p class="mt-1 text-sm text-gray-500">Przeglądaj historię i szczegóły wszystkich złożonych przez Ciebie zamówień.</p>
    </div>

    @if($orders->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-200 p-8 shadow-xs">
            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">Brak historii zamówień</h3>
            <p class="mt-1 text-sm text-gray-500">Nie złożyłeś jeszcze żadnego zamówienia w naszym sklepie.</p>
            <div class="mt-6">
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 border border-transparent text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition-colors">
                    Przejdź do zakupów
                </a>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Zamówienie
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Data złożenia
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Wartość
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Akcja
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('d.m.Y, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusClass }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ number_format($order->total, 2, ',', ' ') }} zł
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('account.orders.show', $order) }}" 
                                       class="inline-flex items-center gap-1 font-semibold text-indigo-600 hover:text-indigo-800">
                                        <span>Szczegóły</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
