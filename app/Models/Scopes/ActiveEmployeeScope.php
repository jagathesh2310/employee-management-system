<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * ActiveEmployeeScope – Global Scope applied to the Employee model.
 *
 * WHY THIS EXISTS:
 *   In most contexts, "Employee" means an active employee.
 *   By applying this scope globally, all default Employee queries
 *   automatically filter to active employees without repeating
 *   ->where('status', 'active') in every controller/service.
 *
 * LARAVEL FEATURE:
 *   Global scopes implement the Scope interface and are applied to every
 *   Eloquent query for the model. They can be bypassed with:
 *     Employee::withoutGlobalScope(ActiveEmployeeScope::class)->get()
 *     Employee::withoutGlobalScopes()->get()
 *
 * INTERACTION:
 *   Applied via the #[ScopedBy] attribute on the Employee model (Laravel 11+).
 *   EmployeeRepository uses withoutGlobalScope() when fetching all employees
 *   regardless of status (e.g., admin dashboards).
 *
 * BEST PRACTICE:
 *   Always document that a global scope exists – developers calling
 *   Employee::all() must know it only returns active employees.
 */
class ActiveEmployeeScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder<Employee>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only return active employees by default.
        // Use Employee::withoutGlobalScope(ActiveEmployeeScope::class) to bypass.
        $builder->where('status', EmployeeStatus::Active);
    }
}
