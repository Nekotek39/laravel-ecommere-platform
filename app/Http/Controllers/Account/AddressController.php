<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\AddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.addresses.index', [
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('account.addresses.create', [
            'address' => new Address(['country' => 'PL']),
        ]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $address = $user->addresses()->create($request->validated());

        if ($address->is_default || $user->addresses()->count() === 1) {
            $address->makeDefault();
        }

        return redirect()->route('account.addresses.index')->with('success', 'The address has been added.');
    }

    public function edit(Address $address): View
    {
        Gate::authorize('update', $address);

        return view('account.addresses.edit', [
            'address' => $address,
        ]);
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        Gate::authorize('update', $address);

        $address->update($request->validated());

        if ($address->is_default) {
            $address->makeDefault();
        }

        return redirect()->route('account.addresses.index')->with('success', 'The address has been updated.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        Gate::authorize('delete', $address);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            Address::query()->where('user_id', $address->user_id)->latest()->first()?->makeDefault();
        }

        return redirect()->route('account.addresses.index')->with('success', 'The address has been deleted.');
    }
}
