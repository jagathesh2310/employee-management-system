<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * EmployeeStatus – demonstrates PHP 8.1+ backed enums with Laravel casting.
 *
 * WHY THIS EXISTS:
 *   Instead of storing raw strings ('active', 'inactive') in the database,
 *   backed enums give us type safety, IDE autocomplete, and automatic validation.
 *
 * LARAVEL FEATURE:
 *   Models cast this enum via $casts = ['status' => EmployeeStatus::class].
 *   Laravel 10+ supports casting to/from backed enums automatically.
 *
 * INTERACTION:
 *   Used by Employee model, StoreEmployeeRequest, UpdateEmployeeRequest,
 *   EmployeeResource, and EmployeeRepository scopes.
 */
enum EmployeeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Resigned = 'resigned';

    /**
     * Get a human-readable label for display purposes.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
            self::Resigned => 'Resigned',
        };
    }

    /**
     * Return CSS badge colour class for API consumers / frontend.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active   => 'green',
            self::Inactive => 'yellow',
            self::Resigned => 'red',
        };
    }

    /**
     * Retrieve all enum values as a plain array – useful in validation rules.
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
