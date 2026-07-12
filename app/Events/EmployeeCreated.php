<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Employee;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EmployeeCreated – fired when a new employee is successfully created.
 *
 * WHY THIS EXISTS:
 *   Events decouple the EmployeeService from the side effects of employee creation.
 *   Instead of the service directly sending emails, clearing cache, and logging,
 *   it fires one event and the listeners handle each concern independently.
 *
 * LARAVEL FEATURE:
 *   The Dispatchable trait adds the static ::dispatch($employee) helper.
 *   SerializesModels ensures the Employee is correctly serialised for queued listeners.
 *   InteractsWithSockets supports broadcasting (not used here but included for completeness).
 *
 * INTERACTION:
 *   Fired by: EmployeeService::create()
 *   Listeners:
 *     - ClearEmployeeCache (sync)
 *     - LogEmployeeActivity (sync)
 *     - SendWelcomeNotification (queued via SendWelcomeEmailJob)
 *
 * BEST PRACTICE:
 *   Events are simple value objects – they carry data, not behaviour.
 *   All logic lives in listeners, not events.
 */
class EmployeeCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Employee $employee,
    ) {}
}
