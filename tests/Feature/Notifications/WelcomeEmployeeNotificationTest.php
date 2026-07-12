<?php

declare(strict_types=1);

use App\Models\Employee;
use App\Notifications\WelcomeEmployeeNotification;
use Illuminate\Support\Facades\Notification;

it('sends welcome notification to employee', function () {
    // Arrange
    Notification::fake();
    $employee = Employee::factory()->create(['email' => 'john.doe@example.com']);

    // Act
    Notification::route('mail', $employee->email)->notify(new WelcomeEmployeeNotification($employee));

    // Assert
    Notification::assertSentOnDemand(
        WelcomeEmployeeNotification::class
    );
});
