<?php

namespace App\Http\Controllers\Shop;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CheckoutRequest;
use App\Models\Order;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const LAST_ORDER_KEY = 'last_order_number';

    public function __construct(
        private CartService $cart,
        private OrderService $orders,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $warnings = $this->cart->sanitize();

        if ($warnings !== []) {
            return redirect()->route('cart.index')->with('warning', implode(' ', $warnings));
        }

        $shippingMethod = ShippingMethod::tryFrom((string) $request->old('shipping_method', $request->query('shipping_method')))
            ?? ShippingMethod::Courier;

        $summary = $this->cart->summary($shippingMethod);

        if ($summary->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty.');
        }

        $user = $request->user();

        return view('shop.checkout.create', [
            'summary' => $summary,
            'user' => $user,
            'addresses' => $user?->addresses()->orderByDesc('is_default')->get() ?? collect(),
            'shippingMethods' => collect(ShippingMethod::cases())->map(fn (ShippingMethod $method) => [
                'method' => $method,
                'cost' => $method->costFor($summary->subtotal - $summary->discount),
            ]),
            'paymentMethods' => PaymentMethod::cases(),
            'selectedShippingMethod' => $shippingMethod,
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $cart = $this->cart->find();

        if (! $cart) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty.');
        }

        $order = $this->orders->placeOrder($cart, $request->checkoutData(), $request->user());

        $request->session()->put(self::LAST_ORDER_KEY, $order->number);

        return redirect()->route('checkout.success', $order);
    }

    public function success(Request $request, Order $order): View
    {
        $isOwner = $request->user() && $order->user_id === $request->user()->id;
        $isGuestJustOrdered = $request->session()->get(self::LAST_ORDER_KEY) === $order->number;

        abort_unless($isOwner || $isGuestJustOrdered, 404);

        return view('shop.checkout.success', [
            'order' => $order->load('items'),
            'bankAccount' => config('shop.bank_account'),
        ]);
    }
}
