<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class SmsNotificationService
{
    public static function send(
        Order $order,
        string $event,
        ?string $riderName = null
    ): void {
        $phone = trim((string) $order->phone);

        if ($phone === '') {
            Log::warning('SMS simulation skipped: order phone number is missing.', [
                'order_id' => $order->id,
                'event' => $event,
            ]);

            return;
        }

        $messages = [
            'placed' => 'We received your order.',
            'accepted' => 'Your order has been accepted.',
            'rider_assigned' => 'A rider has been assigned to your order.',
            'completed' => 'Your order has been completed. Thank you!',
            'cancelled' => 'Your order has been cancelled.',
        ];

        $message = $messages[$event]
            ?? 'There is an update on your H&K Consumer Goods Store order.';

        if ($event === 'rider_assigned' && filled($riderName)) {
            $message .= ' Rider: ' . $riderName . '.';
        }

        Log::info('[SMS SIMULATION] No real SMS was sent.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'recipient_phone' => $phone,
            'event' => $event,
            'message' => $message,
            'simulated_at' => now()->toDateTimeString(),
        ]);
    }
}
