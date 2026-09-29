@extends('layouts.admin', ['title' => 'Pulpit'])

@section('content')
<div class="space-y-8">
    <div class="pb-6 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900">Pulpit administratora</h1>
        <p class="mt-1 text-sm text-gray-500">Podsumowanie i statystyki działania sklepu.</p>
    </div>

    <!-- Kafelki ze statystykami -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Użytkownicy -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Użytkownicy</p>
                <p class="text-2xl font-bold text-gray-900">{{ $usersCount }}</p>
            </div>
        </div>

        <!-- Produkty -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Produkty</p>
                <p class="text-2xl font-bold text-gray-900">{{ $productsCount }}</p>
            </div>
        </div>

        <!-- Wszystkie zamówienia -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Zamówienia</p>
                <p class="text-2xl font-bold text-gray-900">{{ $ordersCount }}</p>
            </div>
        </div>

        <!-- Oczekujące zamówienia -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Oczekujące</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pendingOrdersCount }}</p>
            </div>
        </div>
    </div>

    <!-- Ostatnie zamówienia -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Ostatnie zamówienia</h2>
                <p class="text-xs text-gray-500">5 najnowszych zamówień złożonych w sklepie</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                Wszystkie zamówienia &rarr;
            </a>
        </div>

        @if($latestOrders->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500">
                Brak zamówień w systemie.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Klient</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kwota</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Akcja</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($latestOrders as $order)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">#{{ $order->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $order->user?->name ?? $order->full_name }}
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $statusClass }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ number_format($order->total, 2, ',', ' ') }} zł
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs">
                                        Szczegóły
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
