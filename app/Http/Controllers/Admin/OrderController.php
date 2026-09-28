<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->when($filters['payment_status'] ?? null, fn ($q, $status) => $q->where('payment_status', $status))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(config('shop.admin_per_page'))
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['items.product', 'user']),
            'allowedStatuses' => $order->status->allowedTransitions(),
            'paymentStatuses' => PaymentStatus::cases(),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        if ($request->has('payment_status')) {
            $this->orders->updatePaymentStatus($order, $request->enum('payment_status', PaymentStatus::class));
        }

        if ($request->has('status')) {
            $this->orders->updateStatus(
                $order,
                $request->enum('status', OrderStatus::class),
                $request->input('tracking_number'),
            );
        } elseif ($request->has('tracking_number')) {
            $order->update(['tracking_number' => $request->input('tracking_number')]);
        }

        return back()->with('success', 'The order has been updated.');
    }
}
