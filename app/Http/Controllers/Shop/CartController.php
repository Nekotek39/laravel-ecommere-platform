<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\AddToCartRequest;
use App\Http\Requests\Shop\ApplyCouponRequest;
use App\Http\Requests\Shop\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Cart\CartSummary;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Cart-modifying actions respond with JSON when the request expects JSON
 * (e.g. fetch with the Accept: application/json header), and otherwise
 * redirect back with a flash message.
 */
class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        $warnings = $this->cart->sanitize();

        return view('shop.cart.index', [
            'summary' => $this->cart->summary(),
            'warnings' => $warnings,
        ]);
    }

    public function store(AddToCartRequest $request): RedirectResponse|JsonResponse
    {
        $product = $request->product();

        $this->cart->add($product, $request->quantity());

        return $this->respond($request, "{$product->name} has been added to your cart.");
    }

    public function update(UpdateCartItemRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $this->cart->update($product, $request->integer('quantity'));

        return $this->respond($request, 'Your cart has been updated.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $this->cart->remove($product);

        return $this->respond($request, "{$product->name} has been removed from your cart.");
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->cart->clear();

        return $this->respond($request, 'Your cart has been emptied.');
    }

    public function applyCoupon(ApplyCouponRequest $request): RedirectResponse|JsonResponse
    {
        $coupon = $this->cart->applyCoupon($request->input('code'));

        return $this->respond($request, "Discount code {$coupon->code} has been applied.");
    }

    public function removeCoupon(Request $request): RedirectResponse|JsonResponse
    {
        $this->cart->removeCoupon();

        return $this->respond($request, 'The discount code has been removed.');
    }

    private function respond(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'cart' => $this->summaryToArray($this->cart->summary()),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryToArray(CartSummary $summary): array
    {
        return [
            'count' => $summary->itemsCount(),
            'subtotal' => $summary->subtotal,
            'discount' => $summary->discount,
            'total' => $summary->total,
            'formatted' => [
                'subtotal' => Money::format($summary->subtotal),
                'discount' => Money::format($summary->discount),
                'total' => Money::format($summary->total),
            ],
            'coupon' => $summary->coupon?->code,
            'coupon_error' => $summary->couponError,
            'items' => $summary->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'quantity' => $item->quantity,
                'unit_price' => $item->product->price,
                'total' => $item->total(),
                'image' => $item->product->mainImage?->url,
            ])->all(),
        ];
    }
}
