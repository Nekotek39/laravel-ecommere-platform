<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'usersCount' => User::query()->count(),
            'productsCount' => Product::query()->count(),
            'ordersCount' => Order::query()->count(),
            'pendingOrdersCount' => Order::query()->where('status', OrderStatus::Pending)->count(),
            'latestOrders' => Order::query()->with('user')->latest()->limit(5)->get(),
        ]);
    }
}
