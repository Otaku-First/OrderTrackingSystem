<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Events\OrderStatusChangedRealTime;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderStatusNotification implements ShouldQueue
{

    public function handle(OrderStatusChanged $event)
    {

        logger()->info('Статус замовлення змінено', [
            'order_id' => $event->order->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
        ]);

        $event->order->user->notify(new OrderStatusChangedNotification($event->order, $event->oldStatus, $event->newStatus));

    }
}
