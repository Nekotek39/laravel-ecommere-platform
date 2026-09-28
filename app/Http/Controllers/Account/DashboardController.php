<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('account.dashboard', [
            'user' => $user,
            'recentOrders' => $user->orders()->withCount('items')->latest()->limit(5)->get(),
            'ordersCount' => $user->orders()->count(),
            'defaultAddress' => $user->defaultAddress,
        ]);
    }
}
