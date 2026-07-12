<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * DepartmentPolicy – authorization rules for Department models.
 *
 * WHY POLICIES:
 *   Policies extract authorization logic out of controllers and form requests
 *   into dedicated, testable classes.
 *
 * LARAVEL FEATURE:
 *   - Registered automatically in Laravel 11+ if placed in app/Policies and named correctly.
 *   - before(): intercepts all checks. Returning true grants super-admin access.
 */
class DepartmentPolicy
{
    use HandlesAuthorization;

    /**
     * Intercept all checks. Admins can do everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true; // Anyone authenticated can view departments
    }

    public function view(User $user, Department $department): bool
    {
        return true; // Anyone authenticated can view a department
    }

    public function create(User $user): bool
    {
        return false; // Only admin (handled by before())
    }

    public function update(User $user, Department $department): bool
    {
        return false; // Only admin
    }

    public function delete(User $user, Department $department): bool
    {
        return false; // Only admin
    }
}
