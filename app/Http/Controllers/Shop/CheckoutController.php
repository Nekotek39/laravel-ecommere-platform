<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CheckoutRequest;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        return view('shop.checkout.create', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
            'user' => $request->user(),
        ]);
    }

    public function store(CheckoutRequest $request, OrderService $orders): RedirectResponse
    {
        $order = $orders->placeOrder($request->user(), $request->validated());

        return redirect()->route('account.orders.show', $order)
            ->with('success', "Thank you! Your order #{$order->id} has been placed.");
    }
}
