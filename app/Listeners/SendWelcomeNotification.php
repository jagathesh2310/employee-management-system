<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EmployeeCreated;
use App\Jobs\SendWelcomeEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * SendWelcomeNotification – dispatches a queued job to send the welcome email.
 *
 * WHY THIS EXISTS:
 *   Sending email during a web request adds latency and failure risk.
 *   This listener implements ShouldQueue so it runs in a queue worker,
 *   not during the HTTP request cycle.
 *
 * LARAVEL FEATURES:
 *   - ShouldQueue interface: Laravel automatically queues this listener
 *     when the event is fired. No manual dispatch() needed.
 *   - The listener dispatches a Job (SendWelcomeEmailJob) which handles
 *     the actual email sending. This is a "listener → job" pattern.
 *
 * WHY LISTENER → JOB:
 *   The listener stays thin (just dispatches). The job handles retries,
 *   backoff, and failure logic. This separation makes email sending
 *   independently testable and retryable.
 *
 * INTERACTION:
 *   Triggered by: EmployeeCreated event (fired from EmployeeService::create())
 *   Delegates to: SendWelcomeEmailJob (processes on 'notifications' queue)
 */
class SendWelcomeNotification implements ShouldQueue
{
    /**
     * The queue this listener runs on.
     * WHY NAMED QUEUE: Separating notification jobs into their own queue
     * prevents email delays from blocking higher-priority jobs.
     */
    public string $queue = 'notifications';

    public function handle(EmployeeCreated $event): void
    {
        // Dispatch the job instead of sending directly.
        // The job handles retry logic, failure handling, etc.
        SendWelcomeEmailJob::dispatch($event->employee);
    }
}
