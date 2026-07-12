<?php

declare(strict_types=1);

use App\Models\Department;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('lists departments for authenticated users', function () {
    // Arrange
    $user = User::factory()->create();
    Department::factory()->count(3)->create();

    // Act
    $response = actingAs($user)->getJson('/api/v1/departments');

    // Assert
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'code', 'description']
            ],
            'meta'
        ]);
});

it('allows admin to create a department', function () {
    // Arrange
    $admin = User::factory()->create(['role' => 'admin']);
    $payload = [
        'name' => 'Human Resources',
        'code' => 'HR',
        'description' => 'HR Department',
    ];

    // Act
    $response = actingAs($admin)->postJson('/api/v1/departments', $payload);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('name', 'Human Resources');
        
    $this->assertDatabaseHas('departments', ['code' => 'HR']);
});

it('prevents regular users from creating a department', function () {
    // Arrange
    $user = User::factory()->create(['role' => 'employee']);
    
    // Act
    $response = actingAs($user)->postJson('/api/v1/departments', [
        'name' => 'IT Department',
        'code' => 'IT',
    ]);

    // Assert
    $response->assertForbidden();
});
