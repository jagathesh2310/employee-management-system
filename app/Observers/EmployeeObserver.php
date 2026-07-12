<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\EmployeeCreated;
use App\Events\EmployeeDeleted;
use App\Events\EmployeeUpdated;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

/**
 * EmployeeObserver – reacts to Eloquent model lifecycle events.
 *
 * WHY THIS EXISTS:
 *   Observers are the Eloquent-native way to hook into model events
 *   (creating, created, updating, updated, deleting, deleted, etc.).
 *   They are ideal for cross-cutting concerns that apply to all
 *   operations on a model (logging, event firing, auto-generation of fields).
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - Model events: creating, created, updating, updated, deleting, deleted
 *   - Observer registration in AppServiceProvider::boot()
 *   - Firing domain Events from within model observers
 *   - The difference between creating/created:
 *     * creating: before INSERT – perfect for auto-setting fields
 *     * created: after INSERT – model now has an ID, safe to dispatch events
 *
 * INTERACTION:
 *   Registered in AppServiceProvider::boot() via Employee::observe(EmployeeObserver::class)
 *   Fires EmployeeCreated, EmployeeUpdated, EmployeeDeleted events
 *   which are then handled by the respective listeners.
 *
 * BEST PRACTICE:
 *   Keep observers thin. They should fire events and do minimal work.
 *   Heavy logic belongs in the Service layer, not the observer.
 */
class EmployeeObserver
{
    /**
     * Handle the Employee "creating" event (BEFORE INSERT).
     *
     * WHY: This is the correct place to auto-generate or validate
     * attributes before the record hits the database.
     */
    public function creating(Employee $employee): void
    {
        // Auto-set a default status if not provided.
        // (Normally handled by factory/request, but observer provides safety net)
        if (empty($employee->status)) {
            $employee->status = \App\Enums\EmployeeStatus::Active;
        }

        Log::debug("Employee record being created: {$employee->employee_id}");
    }

    /**
     * Handle the Employee "created" event (AFTER INSERT).
     *
     * WHY: Fire EmployeeCreated after the record exists in DB.
     * Firing before would mean the event listeners can't fetch the employee by ID.
     */
    public function created(Employee $employee): void
    {
        EmployeeCreated::dispatch($employee);
    }

    /**
     * Handle the Employee "updating" event (BEFORE UPDATE).
     *
     * WHY: getDirty() returns changed attributes BEFORE they are saved.
     * We capture the changes here to pass to the EmployeeUpdated event.
     */
    public function updating(Employee $employee): void
    {
        // Store dirty attributes for the updated() hook
        $employee->setAttribute('_dirty', $employee->getDirty());
    }

    /**
     * Handle the Employee "updated" event (AFTER UPDATE).
     */
    public function updated(Employee $employee): void
    {
        $changes = $employee->getAttribute('_dirty') ?? [];
        EmployeeUpdated::dispatch($employee, $changes);
    }

    /**
     * Handle the Employee "deleting" event (BEFORE SOFT-DELETE).
     */
    public function deleting(Employee $employee): void
    {
        Log::info("Employee being soft-deleted: {$employee->id} ({$employee->full_name})");
    }

    /**
     * Handle the Employee "deleted" event (AFTER SOFT-DELETE).
     */
    public function deleted(Employee $employee): void
    {
        EmployeeDeleted::dispatch($employee);
    }

    /**
     * Handle the Employee "restored" event (AFTER soft-delete restore).
     */
    public function restored(Employee $employee): void
    {
        Log::info("Employee restored from soft-delete: {$employee->id}");
        // Re-fire EmployeeCreated so caches are rebuilt
        EmployeeCreated::dispatch($employee);
    }
}
