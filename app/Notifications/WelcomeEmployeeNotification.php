<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * WelcomeEmployeeNotification – sent to a new employee after creation.
 *
 * WHY THIS EXISTS:
 *   Notifications in Laravel are channel-agnostic. This notification
 *   sends via the 'mail' channel but could easily add 'database' or 'slack'
 *   channels without changing the caller code.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - Notification class (extends Notification)
 *   - via(): declares which channels to use
 *   - toMail(): builds the MailMessage fluently
 *   - MailMessage: fluent builder for email content (sent via Mailpit in dev)
 *
 * MAIL IN DEV:
 *   With MAIL_HOST=mailpit, all emails are captured by Mailpit.
 *   View them at http://localhost:8025
 */
class WelcomeEmployeeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Employee $employee,
    ) {}

    /**
     * Declare delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail message.
     *
     * WHY MailMessage:
     *   Provides a clean, markdown-based email builder without needing
     *   a custom Blade template for simple notifications.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Welcome to the team, {$this->employee->first_name}!")
            ->greeting("Hello, {$this->employee->full_name}!")
            ->line('Welcome to the Employee Management System. Your account has been created.')
            ->line("Your Employee ID is: **{$this->employee->employee_id}**")
            ->line("Department: **{$this->employee->department?->name}**")
            ->line("Position: **{$this->employee->position?->name}**")
            ->action('View Your Profile', url('/api/v1/employees/' . $this->employee->id))
            ->line('If you have any questions, please contact HR.')
            ->salutation('Best regards, The HR Team');
    }
}
