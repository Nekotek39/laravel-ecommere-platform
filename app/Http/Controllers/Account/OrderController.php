<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.orders.index', [
            'orders' => $request->user()->orders()
                ->withCount('items')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        return view('account.orders.show', [
            'order' => $order->load('items.product.mainImage'),
            'bankAccount' => config('shop.bank_account'),
        ]);
    }

    public function cancel(Order $order, OrderService $orders): RedirectResponse
    {
        Gate::authorize('cancel', $order);

        $orders->cancel($order);

        return back()->with('success', "Order {$order->number} has been cancelled.");
    }

    /**
     * Adds the products from a previous order to the cart again.
     */
    public function reorder(Order $order, CartService $cart): RedirectResponse
    {
        Gate::authorize('view', $order);

        $skipped = [];

        foreach ($order->load('items.product')->items as $item) {
            $product = $item->product;

            if (! $product || ! $product->isPurchasable()) {
                $skipped[] = $item->product_name;

                continue;
            }

            try {
                $cart->add($product, min($item->quantity, $product->stock));
            } catch (ValidationException) {
                $skipped[] = $item->product_name;
            }
        }

        $redirect = redirect()->route('cart.index')->with('success', 'The products have been added to your cart.');

        return $skipped === []
            ? $redirect
            : $redirect->with('warning', 'Some products are unavailable: '.implode(', ', $skipped).'.');
    }
}
