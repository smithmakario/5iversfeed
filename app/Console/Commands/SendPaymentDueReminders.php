<?php

namespace App\Console\Commands;

use App\Services\PaymentReminderService;
use Illuminate\Console\Command;

class SendPaymentDueReminders extends Command
{
    protected $signature = 'payments:send-reminders';

    protected $description = 'Send payment due and overdue reminder emails for purchase orders';

    public function handle(PaymentReminderService $reminderService): int
    {
        $sent = $reminderService->sendDueReminders();

        $this->info("Sent {$sent} payment reminder notification(s).");

        return self::SUCCESS;
    }
}
