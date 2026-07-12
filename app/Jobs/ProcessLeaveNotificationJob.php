<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\LeaveRequest;
use App\Notifications\LeaveApprovedNotification;
use App\Notifications\LeaveRejectedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProcessLeaveNotificationJob – sends approval/rejection notifications to employees.
 *
 * WHY THIS EXISTS:
 *   Centralises leave notification delivery. A single job handles both
 *   approved and rejected notifications, reducing code duplication.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - SerializesModels: LeaveRequest with all relations is serialised by ID
 *   - Named queues: uses 'notifications' for priority separation
 *   - Conditional notification class selection
 */
class ProcessLeaveNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly LeaveRequest $leaveRequest,
        public readonly string $status, // 'approved' | 'rejected'
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        // Load the employee and their email for notification routing
        $this->leaveRequest->loadMissing('employee');
        $employee = $this->leaveRequest->employee;

        if (! $employee) {
            Log::warning("Leave notification skipped: employee not found for leave {$this->leaveRequest->id}");

            return;
        }

        $notification = match ($this->status) {
            'approved' => new LeaveApprovedNotification($this->leaveRequest),
            'rejected' => new LeaveRejectedNotification($this->leaveRequest),
            default    => null,
        };

        if ($notification === null) {
            Log::warning("Unknown leave status: {$this->status}");

            return;
        }

        // Route to employee email address via anonymous notifiable
        \Notification::route('mail', $employee->email)
            ->notify($notification);

        Log::info("Leave notification sent to {$employee->email}", [
            'leave_id' => $this->leaveRequest->id,
            'status'   => $this->status,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send leave notification', [
            'leave_id' => $this->leaveRequest->id,
            'status'   => $this->status,
            'error'    => $exception->getMessage(),
        ]);
    }
}
