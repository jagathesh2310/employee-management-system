<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\LeaveRequestRepositoryInterface;
use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * LeaveRequestRepository – Eloquent implementation for leave request data access.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - whereRelation(): shorthand for whereHas with simple equality checks
 *   - Enum comparisons in where clauses
 *   - DB transactions wrapping approve/reject state transitions
 */
class LeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LeaveRequest::with([
            'employee:id,first_name,last_name,employee_id',
            'approver:id,name',
        ]);

        // whereRelation(): equivalent to whereHas('employee', fn ($q) => $q->where('id', $id))
        // but more concise for simple equality conditions.
        if (! empty($filters['employee_id'])) {
            $query->whereRelation('employee', 'id', $filters['employee_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['leave_type'])) {
            $query->where('leave_type', $filters['leave_type']);
        }

        // Date range filter
        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->dateRange($filters['date_from'], $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(string $id): LeaveRequest
    {
        return LeaveRequest::with([
            'employee:id,first_name,last_name,employee_id,department_id',
            'employee.department:id,name',
            'approver:id,name,email',
        ])->findOrFail($id);
    }

    public function create(array $data): LeaveRequest
    {
        return DB::transaction(fn () => LeaveRequest::create($data));
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $data) {
            $leaveRequest->update($data);

            return $leaveRequest->fresh(['employee', 'approver']);
        });
    }

    /**
     * Approve a leave request.
     *
     * WHY TRANSACTION:
     *   The status update and approved_at timestamp must be atomic.
     *   If the approval fires events that write to the DB, those writes
     *   are rolled back if anything fails.
     */
    public function approve(LeaveRequest $leaveRequest, User $approver): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $approver) {
            $leaveRequest->update([
                'status' => LeaveStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $leaveRequest->fresh(['employee', 'approver']);
        });
    }

    /**
     * Reject a leave request.
     */
    public function reject(LeaveRequest $leaveRequest, User $approver): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $approver) {
            $leaveRequest->update([
                'status' => LeaveStatus::Rejected,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $leaveRequest->fresh(['employee', 'approver']);
        });
    }

    public function delete(LeaveRequest $leaveRequest): bool
    {
        return DB::transaction(fn () => $leaveRequest->delete());
    }
}
