<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        return view('admin.reviews.index', [
            'reviews' => Review::query()
                ->with(['product', 'user'])
                ->when($status === 'pending', fn ($q) => $q->pending())
                ->when($status === 'approved', fn ($q) => $q->approved())
                ->latest()
                ->paginate(config('shop.admin_per_page'))
                ->withQueryString(),
            'status' => $status,
        ]);
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => true]);

        return back()->with('success', 'The review has been published.');
    }

    public function reject(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => false]);

        return back()->with('success', 'The review has been hidden.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'The review has been deleted.');
    }
}
