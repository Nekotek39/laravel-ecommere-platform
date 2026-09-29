@extends('layouts.admin', ['title' => 'Użytkownik: ' . $user->name])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 items-center gap-2">
        <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Lista użytkowników</span>
        </a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Szczegóły: {{ $user->name }}</span>
    </nav>

    <!-- Karta profilu użytkownika -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-2xl uppercase">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                    @php
                        $roleClass = match ($user->role) {
                            \App\Enums\UserRole::Admin => 'bg-purple-100 text-purple-800 border-purple-200',
                            \App\Enums\UserRole::Moderator => 'bg-blue-100 text-blue-800 border-blue-200',
                            \App\Enums\UserRole::Customer => 'bg-gray-100 text-gray-800 border-gray-200',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $roleClass }}">
                        {{ $user->role->label() }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">{{ $user->email }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Zarejestrowany: {{ $user->created_at->format('d.m.Y, H:i') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.edit', $user) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edytuj profil</span>
            </a>
        </div>
    </div>

    <!-- Historia zamówień tego użytkownika -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Zamówienia użytkownika ({{ $orders->count() }})</h2>
        </div>

        @if($orders->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500">
                Ten użytkownik nie złożył jeszcze żadnych zamówień.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kwota</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Akcja</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">#{{ $order->id }}</td>
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
