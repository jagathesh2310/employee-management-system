<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\LeaveStatus;
use App\Events\LeaveApproved;
use App\Events\LeaveRejected;
use App\Events\LeaveRequested;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;

/**
 * LeaveRequestObserver – handles leave request model lifecycle events.
 *
 * WHY THIS EXISTS:
 *   Like the EmployeeObserver, this centralises leave request lifecycle hooks.
 *   Specifically, it fires LeaveApproved/LeaveRejected events when the status
 *   changes, rather than relying on the service to know when to fire them.
 *
 * BEST PRACTICE:
 *   Using the observer for status-change detection is cleaner than checking
 *   wasChanged() in the service layer. The observer is closer to the model
 *   and has direct access to dirty/clean state.
 */
class LeaveRequestObserver
{
    public function created(LeaveRequest $leaveRequest): void
    {
        LeaveRequested::dispatch($leaveRequest);

        Log::info('Leave request submitted', [
            'leave_id' => $leaveRequest->id,
            'employee_id' => $leaveRequest->employee_id,
            'type' => $leaveRequest->leave_type->value,
        ]);
    }

    public function updated(LeaveRequest $leaveRequest): void
    {
        // Only fire approval/rejection events when the status column changes
        if (! $leaveRequest->wasChanged('status')) {
            return;
        }

        $approver = $leaveRequest->approver;

        if ($leaveRequest->status === LeaveStatus::Approved && $approver) {
            LeaveApproved::dispatch($leaveRequest, $approver);
        } elseif ($leaveRequest->status === LeaveStatus::Rejected && $approver) {
            LeaveRejected::dispatch($leaveRequest, $approver);
        }
    }

    public function deleted(LeaveRequest $leaveRequest): void
    {
        Log::info('Leave request deleted (soft)', [
            'leave_id' => $leaveRequest->id,
        ]);
    }
}
