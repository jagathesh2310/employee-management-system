<?php

declare(strict_types=1);

use App\Events\EmployeeCreated;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

it('dispatches EmployeeCreated event and creates employee', function () {
    // Arrange
    Event::fake(); // Prevent actual event listeners from running during this test
    
    $manager = User::factory()->create(['role' => 'manager']);
    $department = Department::factory()->create();
    $position = Position::factory()->create();

    $payload = [
        'employee_id'   => 'EMP-999',
        'first_name'    => 'John',
        'last_name'     => 'Doe',
        'email'         => 'john.doe@ems.local',
        'joining_date'  => '2023-01-01',
        'salary'        => 60000,
        'department_id' => $department->id,
        'position_id'   => $position->id,
        'status'        => 'active',
    ];

    // Act
    $response = actingAs($manager)->postJson('/api/v1/employees', $payload);

    // Assert
    $response->assertCreated();
    
    $this->assertDatabaseHas('employees', [
        'email' => 'john.doe@ems.local',
        'employee_id' => 'EMP-999',
    ]);

    // Assert the event was fired
    Event::assertDispatched(EmployeeCreated::class, function ($event) {
        return $event->employee->email === 'john.doe@ems.local';
    });
});
