<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EmployeeCreated;
use App\Events\EmployeeDeleted;
use App\Events\EmployeeUpdated;
use Illuminate\Support\Facades\Cache;

/**
 * ClearDepartmentCache – invalidates department listing cache when
 * employee data changes (employee count affects department stats).
 *
 * LARAVEL FEATURE:
 *   Demonstrates that multiple listeners can respond to the same event.
 *   ClearEmployeeCache and ClearDepartmentCache both listen to EmployeeCreated.
 */
class ClearDepartmentCache
{
    public function handleEmployeeCreated(EmployeeCreated $event): void
    {
        $this->clearCache();
    }

    public function handleEmployeeUpdated(EmployeeUpdated $event): void
    {
        // Only clear department cache if department changed
        if (array_key_exists('department_id', $event->changes)) {
            $this->clearCache();
        }
    }

    public function handleEmployeeDeleted(EmployeeDeleted $event): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        // Department cache uses the 'departments' tag
        Cache::tags(['departments'])->flush();
    }
}
