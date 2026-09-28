<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $validOrders = fn () => Order::query()->where('status', '!=', OrderStatus::Cancelled);

        return view('admin.dashboard', [
            'stats' => [
                'orders_today' => $validOrders()->whereDate('created_at', today())->count(),
                'revenue_today' => (int) $validOrders()->whereDate('created_at', today())->sum('total'),
                'revenue_month' => (int) $validOrders()->where('created_at', '>=', now()->startOfMonth())->sum('total'),
                'pending_orders' => Order::query()->where('status', OrderStatus::Pending)->count(),
                'customers' => User::query()->where('role', UserRole::Customer)->count(),
                'products' => Product::query()->count(),
                'pending_reviews' => Review::query()->pending()->count(),
            ],
            'latestOrders' => Order::query()->latest()->limit(10)->get(),
            'lowStockProducts' => Product::query()
                ->where('is_active', true)
                ->where('stock', '<=', config('shop.low_stock_threshold'))
                ->orderBy('stock')
                ->limit(10)
                ->get(),
            'bestSellers' => Product::query()
                ->select('products.*')
                ->selectSub(
                    DB::table('order_items')
                        ->join('orders', 'orders.id', '=', 'order_items.order_id')
                        ->where('orders.status', '!=', OrderStatus::Cancelled->value)
                        ->whereColumn('order_items.product_id', 'products.id')
                        ->selectRaw('COALESCE(SUM(order_items.quantity), 0)'),
                    'sold_quantity',
                )
                ->orderByDesc('sold_quantity')
                ->limit(5)
                ->get(),
            'salesChart' => $this->salesLastDays(30),
        ]);
    }

    /**
     * Revenue (in cents) and number of orders, day by day.
     *
     * @return list<array{date: string, orders: int, revenue: int}>
     */
    private function salesLastDays(int $days): array
    {
        $from = today()->subDays($days - 1);

        $rows = Order::query()
            ->where('status', '!=', OrderStatus::Cancelled)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $result = [];

        for ($date = $from->copy(); $date->lte(today()); $date->addDay()) {
            $row = $rows->get($date->toDateString());

            $result[] = [
                'date' => $date->toDateString(),
                'orders' => (int) ($row->orders ?? 0),
                'revenue' => (int) ($row->revenue ?? 0),
            ];
        }

        return $result;
    }
}
