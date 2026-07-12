<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * LeaveRequestPolicy – authorization for leave requests.
 *
 * DEMONSTRATES:
 *   - Action-specific authorization (approve) that checks model state
 *   - Role-based fallback (admins can do anything, managers have specific powers)
 */
class LeaveRequestPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true; // Everyone can view their own, managers can view all (filtered in controller)
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a leave request
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only pending requests can be updated
        return $leaveRequest->status === LeaveStatus::Pending;
    }

    /**
     * Authorization to approve or reject a leave request.
     */
    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        // Must be a manager, and the request must be pending
        if (! $user->isManager()) {
            return false;
        }

        return $leaveRequest->status === LeaveStatus::Pending;
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }
}
