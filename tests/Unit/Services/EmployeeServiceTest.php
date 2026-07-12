<?php

declare(strict_types=1);

use App\DTO\EmployeeData;
use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Services\EmployeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates an employee successfully', function () {
    // Arrange
    $service = app(EmployeeService::class);
    $department = Department::factory()->create();
    $position = Position::factory()->create();

    $dto = new EmployeeData(
        employeeId: 'EMP-999',
        firstName: 'Jane',
        lastName: 'Doe',
        email: 'jane.doe@example.com',
        phone: '1234567890',
        gender: 'female',
        dateOfBirth: '1990-01-01',
        joiningDate: '2023-01-01',
        salary: 60000.0,
        status: EmployeeStatus::Active,
        departmentId: $department->id,
        positionId: $position->id,
        managerId: null
    );

    // Act
    $employee = $service->create($dto);

    // Assert
    expect($employee)
        ->toBeInstanceOf(Employee::class)
        ->and($employee->first_name)->toBe('Jane')
        ->and($employee->email)->toBe('jane.doe@example.com');
});
