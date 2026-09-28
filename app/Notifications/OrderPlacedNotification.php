<?php

namespace App\Notifications;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
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
        $order = $this->order->loadMissing('items');

        $message = (new MailMessage)
            ->subject("Order confirmation {$order->number}")
            ->greeting('Thank you for your order!')
            ->line("We have received your order no. **{$order->number}**.");

        foreach ($order->items as $item) {
            $message->line("{$item->product_name} × {$item->quantity} — ".Money::format($item->total));
        }

        $message
            ->line('Subtotal: '.Money::format($order->subtotal))
            ->when($order->discount > 0, fn (MailMessage $m) => $m->line('Discount: -'.Money::format($order->discount)))
            ->line("Shipping ({$order->shipping_method->label()}): ".Money::format($order->shipping_cost))
            ->line('**Total due: '.Money::format($order->total).'**');

        if ($order->payment_method === PaymentMethod::BankTransfer) {
            $message
                ->line('Please make a bank transfer to:')
                ->line(config('shop.bank_account.name').', '.config('shop.bank_account.number'))
                ->line("Transfer reference: {$order->number}");
        }

        if ($order->user_id) {
            $message->action('View order', route('account.orders.show', $order));
        }

        return $message;
    }
}
