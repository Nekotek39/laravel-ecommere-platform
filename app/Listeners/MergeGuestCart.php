<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCart
{
    public function __construct(private CartService $cart) {}

    public function handle(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->cart->mergeGuestCartInto($event->user);
        }
    }
}
