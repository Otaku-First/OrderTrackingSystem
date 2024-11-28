<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;
    public $oldStatus;
    public $newStatus;

    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Статус вашого замовлення змінено')
            ->greeting('Вітаю' )
            ->line('Ваше замовлення #' . $this->order->id . ' змінило статус.')
            ->line('Старий статус: ' . $this->oldStatus)
            ->line('Новий статус: ' . $this->newStatus)
            ->action('Переглянути замовлення', url('/api/orders/' . $this->order->id))
            ->line('Дякуємо, що користуєтесь нашим сервісом!');

    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }

}
