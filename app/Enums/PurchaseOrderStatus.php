<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Confirmed = 'confirmed';
    case Dispatched = 'dispatched';
    case Received = 'received';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Issued',
            self::Confirmed => 'Confirmed',
            self::Dispatched => 'Dispatched',
            self::Received => 'Received',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::Confirmed => 'indigo',
            self::Dispatched => 'purple',
            self::Received => 'green',
            self::Rejected => 'red',
            self::Cancelled => 'red',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Received, self::Cancelled], true);
    }

    /** @return list<self> */
    public function allowedTransitionsForAdmin(): array
    {
        return match ($this) {
            self::Draft => [self::Submitted, self::Cancelled],
            self::Submitted => [self::Cancelled],
            self::Confirmed => [self::Cancelled],
            self::Dispatched => [self::Received],
            self::Rejected => [self::Submitted],
            default => [],
        };
    }

    /** @return list<self> */
    public function allowedTransitionsForSupplier(): array
    {
        return match ($this) {
            self::Submitted => [self::Confirmed, self::Rejected],
            self::Confirmed => [self::Dispatched],
            default => [],
        };
    }

    public function canTransitionTo(self $target, string $actorRole): bool
    {
        $allowed = $actorRole === 'admin'
            ? $this->allowedTransitionsForAdmin()
            : $this->allowedTransitionsForSupplier();

        return in_array($target, $allowed, true);
    }
}
