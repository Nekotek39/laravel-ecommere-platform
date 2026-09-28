<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\OrderPlaced;
use App\Models\User;
use App\Notifications\NewOrderForAdmin;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderPlacedNotifications
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        if ($order->user) {
            $order->user->notify(new OrderPlacedNotification($order));
        } else {
            Notification::route('mail', $order->email)->notify(new OrderPlacedNotification($order));
        }

        $admins = User::query()->where('role', UserRole::Admin)->get();

        Notification::send($admins, new NewOrderForAdmin($order));
    }
}
