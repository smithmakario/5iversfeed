<?php

namespace App\Enums;

enum PaymentOption: string
{
    case OneOff = 'one_off';
    case Partial = 'partial';
    case FullCredit = 'full_credit';
    case PartialCredit = 'partial_credit';

    public function label(): string
    {
        return match ($this) {
            self::OneOff => 'One-off Payment',
            self::Partial => 'Partial Payment',
            self::FullCredit => 'Full Credit',
            self::PartialCredit => 'Partial Credit',
        };
    }

    public function requiresCreditTimeline(): bool
    {
        return in_array($this, [self::FullCredit, self::PartialCredit], true);
    }

    public function requiresUpfrontAmount(): bool
    {
        return in_array($this, [self::Partial, self::PartialCredit], true);
    }
}
