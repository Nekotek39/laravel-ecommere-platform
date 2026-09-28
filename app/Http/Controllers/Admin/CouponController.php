<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CouponType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::query()
                ->when($request->query('search'), fn ($q, $search) => $q->where('code', 'like', '%'.mb_strtoupper($search).'%'))
                ->latest()
                ->paginate(config('shop.admin_per_page'))
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create', [
            'coupon' => new Coupon(['type' => CouponType::Percent, 'is_active' => true]),
            'types' => CouponType::cases(),
        ]);
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        Coupon::query()->create($request->couponData());

        return redirect()->route('admin.coupons.index')->with('success', 'The discount code has been created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', [
            'coupon' => $coupon,
            'types' => CouponType::cases(),
        ]);
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($request->couponData());

        return redirect()->route('admin.coupons.index')->with('success', 'The discount code has been updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'The discount code has been deleted.');
    }
}
