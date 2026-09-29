@extends('layouts.admin', ['title' => 'Zarządzanie zamówieniami'])

@section('content')
<div class="space-y-6">
    <div class="pb-6 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900">Zamówienia</h1>
        <p class="mt-1 text-sm text-gray-500">Przeglądaj wszystkie zamówienia i zarządzaj ich statusami.</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        @if($orders->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <p class="mt-2 text-sm font-medium">Brak złożonych zamówień w sklepie.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Klient / Odbiorca</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Kwota</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $order->full_name }}</div>
                                    @if($order->user)
                                        <a href="{{ route('admin.users.show', $order->user) }}" class="text-xs text-indigo-600 hover:text-indigo-800">
                                            Konto: {{ $order->user->email }}
                                        </a>
                                    @endif
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClass }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ number_format($order->total, 2, ',', ' ') }} zł
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs">
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

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
@endsection
