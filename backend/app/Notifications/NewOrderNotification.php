<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\Orders;

class NewOrderNotification extends Notification
{
    protected $order;
    public function __construct(Orders $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Có đơn hàng mới',
            'order_id' => $this->order->id,
            'message' => 'Bạn có đơn hàng mới #' . $this->order->id,
            'user_id' => $this->order->user_id,
            'final_price' => $this->order->final_price,
            'created_at' => $this->order->created_at,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Có đơn hàng mới',
            'order_id' => 1234,
            'message' => 'Bạn có đơn hàng mới #1234',
        ]);
    }
}
