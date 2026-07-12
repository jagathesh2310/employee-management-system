<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * LeaveStatus – tracks the lifecycle of a leave request.
 *
 * WHY THIS EXISTS:
 *   Leave requests move through states: Pending → Approved | Rejected.
 *   An enum enforces valid transitions and prevents invalid state strings.
 *
 * LARAVEL FEATURE:
 *   Cast on LeaveRequest model. Used in LeaveRequestPolicy to gate the
 *   approve/reject actions (only Pending requests can be actioned).
 *
 * INTERACTION:
 *   LeaveRequestService::approve() and reject() check the current status
 *   before applying the transition. LeaveRequestObserver reads this value.
 */
enum LeaveStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending  => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Returns true when a leave request can still be actioned.
     */
    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}
