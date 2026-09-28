<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderStatusNotification
{
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $notification = new OrderStatusChangedNotification($order);

        if ($order->user) {
            $order->user->notify($notification);
        } else {
            Notification::route('mail', $order->email)->notify($notification);
        }
    }
}
