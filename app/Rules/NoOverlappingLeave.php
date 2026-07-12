<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * NoOverlappingLeave – ensures an employee cannot have two approved/pending
 * leave requests that overlap in date range.
 *
 * WHY THIS CUSTOM RULE:
 *   Laravel's built-in validation rules don't cover complex business logic
 *   like "no overlapping date ranges for a given employee." Custom rules
 *   keep this logic out of controllers and services, centralising it in
 *   a single, testable class.
 *
 * LARAVEL FEATURE:
 *   Implements ValidationRule (Laravel 10+ interface).
 *   Receives $fail closure instead of returning bool, allowing rich error messages.
 *
 * SQL LOGIC:
 *   Two date ranges [A, B] and [C, D] overlap when A <= D AND B >= C.
 *   We query for any approved/pending leave for this employee where this is true.
 *
 * INTERACTION:
 *   Used in StoreLeaveRequestRequest::rules().
 *   Takes employee_id and the request's start/end dates as constructor parameters.
 */
class NoOverlappingLeave implements ValidationRule
{
    public function __construct(
        private readonly string $employeeId,
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly ?string $excludeLeaveId = null, // For update operations
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Query for overlapping leave requests
        $query = LeaveRequest::where('employee_id', $this->employeeId)
            ->whereIn('status', [LeaveStatus::Pending->value, LeaveStatus::Approved->value])
            // Overlap condition: existing.start <= new.end AND existing.end >= new.start
            ->where('start_date', '<=', $this->endDate)
            ->where('end_date', '>=', $this->startDate);

        // Exclude the current request when updating
        if ($this->excludeLeaveId !== null) {
            $query->where('id', '!=', $this->excludeLeaveId);
        }

        if ($query->exists()) {
            $fail('The employee already has a pending or approved leave request overlapping these dates.');
        }
    }
}
