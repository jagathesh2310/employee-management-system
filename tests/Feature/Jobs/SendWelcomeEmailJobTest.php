<?php

declare(strict_types=1);

use App\Jobs\SendWelcomeEmailJob;
use App\Models\Employee;
use App\Notifications\WelcomeEmployeeNotification;
use Illuminate\Support\Facades\Notification;

it('handles sending welcome email job', function () {
    // Arrange
    Notification::fake();
    $employee = Employee::factory()->create();
    $job = new SendWelcomeEmailJob($employee);

    // Act
    $job->handle();

    // Assert
    Notification::assertSentOnDemand(
        WelcomeEmployeeNotification::class,
        function (WelcomeEmployeeNotification $notification, array $channels, object $notifiable) use ($employee) {
            return $notifiable->routes['mail'] === $employee->email;
        }
    );
});
