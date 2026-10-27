<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Throwable;

class TestOrderEmail extends Command
{
    protected $signature = 'mail:test {email : Email address that will receive the test}';

    protected $description = 'Send a test email using the configured SMTP settings';

    public function handle(): int
    {
        $recipient = $this->argument('email');

        try {
            Mail::raw(
                'Congratulations! Your Laravel 12 Gmail SMTP configuration is working.',
                function (Message $message) use ($recipient) {
                    $message
                        ->to($recipient)
                        ->subject('H&K Consumer Goods Store - SMTP Test');
                }
            );

            $this->info('Test email sent successfully. Check the recipient inbox and spam folder.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->error('Failed to send the test email. Check your .env SMTP settings and Laravel logs.');

            return self::FAILURE;
        }
    }
}
