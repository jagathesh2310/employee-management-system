<?php

declare(strict_types=1);

use App\Models\Position;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('lists positions for authenticated users', function () {
    // Arrange
    $user = User::factory()->create();
    Position::factory()->count(3)->create();

    // Act
    $response = actingAs($user)->getJson('/api/v1/positions');

    // Assert
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'level', 'description'],
            ],
            'meta',
        ]);
});

it('allows admin to create a position', function () {
    // Arrange
    $admin = User::factory()->create(['role' => 'admin']);
    $payload = [
        'name' => 'Senior Developer',
        'level' => 3,
        'description' => 'Senior Dev',
    ];

    // Act
    $response = actingAs($admin)->postJson('/api/v1/positions', $payload);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('name', 'Senior Developer');

    $this->assertDatabaseHas('positions', ['name' => 'Senior Developer']);
});

it('prevents regular users from creating a position', function () {
    // Arrange
    $user = User::factory()->create(['role' => 'employee']);

    // Act
    $response = actingAs($user)->postJson('/api/v1/positions', [
        'name' => 'Junior Developer',
        'level' => 1,
    ]);

    // Assert
    $response->assertForbidden();
});
