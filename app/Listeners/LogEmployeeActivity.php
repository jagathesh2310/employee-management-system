<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EmployeeCreated;
use App\Events\EmployeeDeleted;
use App\Events\EmployeeUpdated;
use Illuminate\Support\Facades\Log;

/**
 * LogEmployeeActivity – writes structured audit logs for employee lifecycle events.
 *
 * WHY THIS EXISTS:
 *   Audit trails are a compliance requirement. By centralising logging in a listener,
 *   the service layer remains clean and logging can be changed/disabled without
 *   touching business logic.
 *
 * LARAVEL FEATURE:
 *   Uses the Log facade with contextual arrays for structured logging.
 *   Structured logs can be parsed by log aggregators (e.g., Datadog, Sentry).
 *
 * BEST PRACTICE:
 *   Always log who performed the action, what changed, and when.
 *   Log::info() for normal operations; Log::warning() for suspicious actions.
 */
class LogEmployeeActivity
{
    public function handleEmployeeCreated(EmployeeCreated $event): void
    {
        Log::info('Employee created', [
            'employee_id' => $event->employee->id,
            'employee_code' => $event->employee->employee_id,
            'name' => $event->employee->full_name,
            'department_id' => $event->employee->department_id,
            'position_id' => $event->employee->position_id,
            'created_at' => now()->toIso8601String(),
        ]);
    }

    public function handleEmployeeUpdated(EmployeeUpdated $event): void
    {
        Log::info('Employee updated', [
            'employee_id' => $event->employee->id,
            'changes' => $event->changes,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function handleEmployeeDeleted(EmployeeDeleted $event): void
    {
        Log::warning('Employee deleted (soft)', [
            'employee_id' => $event->employee->id,
            'name' => $event->employee->full_name,
            'deleted_at' => now()->toIso8601String(),
        ]);
    }
}
