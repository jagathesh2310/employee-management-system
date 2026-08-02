<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LeaveApproved;
use App\Events\LeaveRejected;
use App\Jobs\ProcessLeaveNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * SendLeaveStatusNotification – queued listener for leave approval/rejection.
 *
 * WHY THIS EXISTS:
 *   When a manager approves or rejects a leave request, the employee
 *   should be notified. This listener is queued to avoid blocking
 *   the approval API response with email-sending latency.
 *
 * LARAVEL FEATURE:
 *   Implements ShouldQueue – Laravel automatically pushes this listener
 *   onto the Redis queue when LeaveApproved or LeaveRejected is fired.
 */
class SendLeaveStatusNotification implements ShouldQueue
{
    /**
     * Number of retry attempts if the job fails.
     * WHY 3: Network/SMTP failures are transient; 3 attempts covers most cases.
     */
    public int $tries = 3;

    public function handleLeaveApproved(LeaveApproved $event): void
    {
        // ProcessLeaveNotificationJob::dispatch($event->leaveRequest, 'approved');
    }

    public function handleLeaveRejected(LeaveRejected $event): void
    {
        // ProcessLeaveNotificationJob::dispatch($event->leaveRequest, 'rejected');
    }
}
