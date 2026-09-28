<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        $message = (new MailMessage)
            ->subject("Order {$order->number}: {$order->status->label()}")
            ->line("The status of your order no. **{$order->number}** has been changed to: **{$order->status->label()}**.");

        if ($order->status === OrderStatus::Shipped && $order->tracking_number) {
            $message->line("Tracking number: {$order->tracking_number}");
        }

        if ($order->user_id) {
            $message->action('View order', route('account.orders.show', $order));
        }

        return $message;
    }
}
