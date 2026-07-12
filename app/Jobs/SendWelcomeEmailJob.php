<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Employee;
use App\Notifications\WelcomeEmployeeNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendWelcomeEmailJob – sends the welcome email to a new employee.
 *
 * WHY THIS EXISTS:
 *   Email sending is I/O-bound and should never block the HTTP response.
 *   Queuing this job means:
 *     1. The API responds immediately (200ms vs 2000ms with SMTP)
 *     2. Email failures don't crash the employee creation request
 *     3. Failed emails are retried automatically
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - ShouldQueue: runs in a queue worker (Redis queue via Supervisor)
 *   - Queueable: provides onQueue(), onConnection(), delay() methods
 *   - SerializesModels: Employee model is serialised by ID, re-fetched in worker
 *   - $tries, $backoff: built-in retry configuration
 *   - failed(): hook called when all retries are exhausted
 *
 * QUEUE:
 *   Runs on the 'notifications' queue. Supervisor runs a dedicated worker
 *   for this queue to prevent email jobs from blocking main workers.
 *
 * INTERACTION:
 *   Dispatched by: SendWelcomeNotification listener
 *   Uses: WelcomeEmployeeNotification (the Mailable/Notification)
 */
class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Maximum retry attempts before the job is considered failed.
     * WHY 3: Enough to handle transient SMTP failures without infinite loops.
     */
    public int $tries = 3;

    /**
     * Backoff seconds between retry attempts.
     * WHY array: Exponential backoff – wait longer between each retry.
     *
     * @var array<int>
     */
    public array $backoff = [30, 60, 120];

    /**
     * Maximum seconds the job can run before it's considered "lost".
     * WHY 60: Email sending rarely takes more than 10s; 60s is safe.
     */
    public int $timeout = 60;

    public function __construct(
        public readonly Employee $employee,
    ) {
        // Send to the 'notifications' queue, not the default queue.
        // Supervisor runs a worker specifically for this queue.
        $this->onQueue('notifications');
    }

    /**
     * Execute the job – send the welcome notification.
     */
    public function handle(): void
    {
        // WHY notify() vs Mail::to():
        //   Notifications are more flexible – they can be sent via multiple
        //   channels (mail, SMS, database) with one call.
        //   WelcomeEmployeeNotification handles the mail channel.
        //
        // We create a temporary Notifiable model (User) since Employee doesn't
        // directly extend Authenticatable. In a real app, Employee would also
        // be notifiable.
        Log::info("Sending welcome email to employee {$this->employee->id}", [
            'email' => $this->employee->email,
            'name' => $this->employee->full_name,
        ]);

        // Sending notification directly to email address
        \Notification::route('mail', $this->employee->email)
            ->notify(new WelcomeEmployeeNotification($this->employee));
    }

    /**
     * Handle job failure after all retries are exhausted.
     *
     * LARAVEL FEATURE: failed() is called automatically by the queue system.
     * Use this to alert ops or write to a failed_jobs table.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send welcome email', [
            'employee_id' => $this->employee->id,
            'email' => $this->employee->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
