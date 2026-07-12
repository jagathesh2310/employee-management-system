<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * LeaveApprovedNotification – notifies an employee their leave was approved.
 */
class LeaveApprovedNotification extends Notification
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
            ->subject('Your Leave Request Has Been Approved ✓')
            ->greeting("Hello, {$employee?->first_name}!")
            ->line('Great news! Your leave request has been **approved**.')
            ->line("**Leave Type:** {$this->leaveRequest->leave_type->label()}")
            ->line("**Period:** {$startDate} to {$endDate}")
            ->line("**Duration:** {$this->leaveRequest->duration_in_days} day(s)")
            ->line('Please ensure your work is handed over before you leave.')
            ->salutation('Best regards, HR Team');
    }
}
