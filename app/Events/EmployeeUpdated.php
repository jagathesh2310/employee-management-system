<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Employee;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EmployeeUpdated – fired after an employee record is updated.
 *
 * Carries both the updated employee and a list of changed fields
 * so listeners can take targeted action (e.g., only clear cache
 * if salary changed, only log if status changed).
 */
class EmployeeUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $changes  Keys that were changed in this update.
     */
    public function __construct(
        public readonly Employee $employee,
        public readonly array $changes = [],
    ) {}
}
