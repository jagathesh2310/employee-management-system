<?php

declare(strict_types=1);

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('prevents overlapping leave requests', function () {
    // Arrange
    $user = User::factory()->create();
    $employee = Employee::factory()->create();

    // Create an existing approved leave request: in 10 days, for 5 days
    $baseDate = now()->addDays(10);
    LeaveRequest::factory()->create([
        'employee_id' => $employee->id,
        'start_date' => $baseDate->format('Y-m-d'),
        'end_date' => (clone $baseDate)->addDays(5)->format('Y-m-d'),
        'status' => 'approved',
    ]);

    // Act: Try to request in 12 days, for 4 days (overlaps)
    $payload = [
        'employee_id' => $employee->id,
        'leave_type' => 'sick',
        'start_date' => (clone $baseDate)->addDays(2)->format('Y-m-d'),
        'end_date' => (clone $baseDate)->addDays(6)->format('Y-m-d'),
        'reason' => 'Feeling unwell',
    ];

    $response = actingAs($user)->postJson('/api/v1/leave-requests', $payload);

    // Assert
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date']);

    // Assert the error message comes from NoOverlappingLeave rule
    $this->assertStringContainsString(
        'overlapping',
        $response->json('errors.start_date.0')
    );
});
