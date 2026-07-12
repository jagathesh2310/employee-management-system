<?php

declare(strict_types=1);

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can fetch active employees', function () {
    // Arrange
    $repository = app(EmployeeRepository::class);

    // Create active and inactive employees
    Employee::factory()->count(2)->create(['status' => 'active']);
    Employee::factory()->count(1)->create(['status' => 'inactive']);

    // Act
    $employees = Employee::all();

    // Assert
    // Our active global scope should only return the 2 active employees
    expect($employees)->toHaveCount(2);
});
