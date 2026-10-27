<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $event,
        public ?string $riderName = null
    ) {
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'placed' => 'We Received Your Order',
            'accepted' => 'Your Order Has Been Accepted',
            'rider_assigned' => 'A Rider Has Been Assigned to Your Order',
            'completed' => 'Your Order Has Been Completed',
            'cancelled' => 'Your Order Has Been Cancelled',
        ];

        return new Envelope(
            subject: $subjects[$this->event]
                ?? 'Update on Your H&K Consumer Goods Store Order'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.update'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}