<?php

declare(strict_types=1);

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

it('prevents overlapping leave requests', function () {
    // Arrange
    $user = User::factory()->create();
    $employee = Employee::factory()->create();

    // Create an existing approved leave request: Jan 10 - Jan 15
    LeaveRequest::factory()->create([
        'employee_id' => $employee->id,
        'start_date'  => '2024-01-10',
        'end_date'    => '2024-01-15',
        'status'      => 'approved',
    ]);

    // Act: Try to request Jan 12 - Jan 16 (overlaps)
    $payload = [
        'employee_id' => $employee->id,
        'leave_type'  => 'sick',
        'start_date'  => '2024-01-12',
        'end_date'    => '2024-01-16',
        'reason'      => 'Feeling unwell',
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
