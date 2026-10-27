<?php

namespace App\Support;

use App\Mail\OrderUpdateMail;
use App\Models\Order;
use App\Services\SmsNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderEmailNotifier
{
    public static function send(
        Order $order,
        string $event,
        ?string $riderName = null
    ): void {
        // Diagnostic log: kumpirmahing natawag ang notification method.
        Log::info('[ORDER NOTIFIER] Notification method reached.', [
            'order_id' => $order->id,
            'event' => $event,
        ]);

        // SMS simulation lamang; walang totoong SMS na ipinapadala.
        try {
            SmsNotificationService::send(
                $order,
                $event,
                $riderName
            );
        } catch (Throwable $exception) {
            Log::error('SMS simulation failed.', [
                'order_id' => $order->id,
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);
        }

        // Panatilihin ang kasalukuyang email notification behavior.
        try {
            $order->loadMissing('customer');

            $email = $order->customer?->email;

            if (!filled($email)) {
                Log::warning(
                    'Order notification skipped: customer email is missing.',
                    [
                        'order_id' => $order->id,
                        'event' => $event,
                    ]
                );

                return;
            }

            Mail::to($email)->send(
                new OrderUpdateMail(
                    $order,
                    $event,
                    $riderName
                )
            );
        } catch (Throwable $exception) {
            Log::error('Order notification email failed.', [
                'order_id' => $order->id,
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
