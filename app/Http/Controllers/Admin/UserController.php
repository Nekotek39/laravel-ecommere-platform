<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::enum(UserRole::class)],
        ]);

        return view('admin.users.index', [
            'users' => User::query()
                ->withCount('orders')
                ->withSum('orders as orders_total', 'total')
                ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
                ->when($filters['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
                ->latest()
                ->paginate(config('shop.admin_per_page'))
                ->withQueryString(),
            'filters' => $filters,
            'roles' => UserRole::cases(),
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user->load('addresses'),
            'orders' => $user->orders()->withCount('items')->latest()->paginate(10),
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->forceFill(['role' => $request->enum('role', UserRole::class)])->save();

        return back()->with('success', "The user's role has been changed.");
    }
}
