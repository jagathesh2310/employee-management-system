<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * LeaveRejectedNotification – notifies an employee their leave was rejected.
 */
class LeaveRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly LeaveRequest $leaveRequest,
    ) {}

    /**
     * @param  mixed  $notifiable
     * @return array<string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $employee  = $this->leaveRequest->employee;
        $startDate = $this->leaveRequest->start_date->format('d M Y');
        $endDate   = $this->leaveRequest->end_date->format('d M Y');

        return (new MailMessage())
            ->subject('Your Leave Request Could Not Be Approved')
            ->greeting("Hello, {$employee?->first_name}!")
            ->line('We regret to inform you that your leave request has been **rejected**.')
            ->line("**Leave Type:** {$this->leaveRequest->leave_type->label()}")
            ->line("**Requested Period:** {$startDate} to {$endDate}")
            ->line('Please contact your manager or HR for further details.')
            ->salutation('Best regards, HR Team');
    }
}
