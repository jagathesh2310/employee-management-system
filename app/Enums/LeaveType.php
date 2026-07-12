<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * LeaveType – categorises the type of leave being requested.
 *
 * WHY THIS EXISTS:
 *   Different leave types may have different policies (e.g., max days).
 *   An enum prevents typos and enables exhaustive pattern matching.
 *
 * LARAVEL FEATURE:
 *   Cast on LeaveRequest model. Used in StoreLeaveRequestRequest for
 *   the 'in' validation rule: Rule::enum(LeaveType::class).
 *
 * INTERACTION:
 *   GenerateEmployeeReportJob groups leave statistics by type using this enum.
 */
enum LeaveType: string
{
    case Casual = 'casual';
    case Sick   = 'sick';
    case Earned = 'earned';

    public function label(): string
    {
        return match ($this) {
            self::Casual => 'Casual Leave',
            self::Sick   => 'Sick Leave',
            self::Earned => 'Earned Leave',
        };
    }

    /**
     * Maximum allowed days per type per year (business rule example).
     */
    public function maxDaysPerYear(): int
    {
        return match ($this) {
            self::Casual => 12,
            self::Sick   => 15,
            self::Earned => 21,
        };
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
