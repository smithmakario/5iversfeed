<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\PaymentDueNotification;
use Illuminate\Support\Facades\Notification;

class PaymentReminderService
{
    public function __construct(
        private PaymentDashboardService $dashboardService,
        private PurchaseOrderPaymentService $paymentService,
        private PurchaseOrderActivityService $activityService,
    ) {}

    public function sendDueReminders(): int
    {
        $sent = 0;

        $orders = PurchaseOrder::query()
            ->with(['supplier.user', 'creator'])
            ->whereNotNull('payment_due_date')
            ->whereNotIn('status', [
                PurchaseOrderStatus::Draft->value,
                PurchaseOrderStatus::Cancelled->value,
                PurchaseOrderStatus::Rejected->value,
            ])
            ->get();

        foreach ($orders as $order) {
            $amountDue = $this->paymentService->amountOutstanding($order);

            if ($amountDue <= 0) {
                continue;
            }

            $daysToDue = $this->dashboardService->daysToDue($order);
            $reminderType = $this->determineReminderType($daysToDue, $order);

            if ($reminderType === null || $this->reminderAlreadySent($order, $reminderType)) {
                continue;
            }

            $sent += $this->notifyRecipients($order, $reminderType, $amountDue, $daysToDue);
            $this->logReminderSent($order, $reminderType, $amountDue, $daysToDue);
        }

        return $sent;
    }

    private function determineReminderType(?int $daysToDue, PurchaseOrder $order): ?string
    {
        if ($daysToDue === null) {
            return $this->dashboardService->isOverdue($order) ? 'overdue' : null;
        }

        if ($daysToDue === 7) {
            return 'due_soon';
        }

        if ($daysToDue === 0) {
            return 'due_today';
        }

        if ($daysToDue < 0) {
            return 'overdue';
        }

        return null;
    }

    private function reminderAlreadySent(PurchaseOrder $order, string $reminderType): bool
    {
        $activityType = "payment_reminder_{$reminderType}";

        $query = $order->activities()->where('type', $activityType);

        if ($reminderType === 'overdue') {
            return $query->where('created_at', '>=', now()->subDays(7))->exists();
        }

        return $query->whereDate('created_at', today())->exists();
    }

    private function notifyRecipients(
        PurchaseOrder $order,
        string $reminderType,
        float $amountDue,
        ?int $daysToDue,
    ): int {
        $sent = 0;

        $notification = fn (string $role) => new PaymentDueNotification(
            purchaseOrder: $order,
            reminderType: $reminderType,
            recipientRole: $role,
            amountDue: $amountDue,
            daysToDue: $daysToDue,
        );

        if ($order->creator) {
            $order->creator->notify($notification('admin'));
            $sent++;
        }

        $supplier = $order->supplier;

        if ($supplier->user) {
            $supplier->user->notify($notification('supplier'));
            $sent++;
        } elseif (filled($supplier->email)) {
            Notification::route('mail', $supplier->email)->notify($notification('supplier'));
            $sent++;
        }

        return $sent;
    }

    private function logReminderSent(
        PurchaseOrder $order,
        string $reminderType,
        float $amountDue,
        ?int $daysToDue,
    ): void {
        $description = match ($reminderType) {
            'due_soon' => 'Payment due soon reminder sent.',
            'due_today' => 'Payment due today reminder sent.',
            'overdue' => 'Payment overdue reminder sent.',
            default => 'Payment reminder sent.',
        };

        $this->activityService->log(
            $order,
            "payment_reminder_{$reminderType}",
            $description,
            null,
            [
                'reminder_type' => $reminderType,
                'amount_due' => $amountDue,
                'days_to_due' => $daysToDue,
            ],
        );
    }
}
