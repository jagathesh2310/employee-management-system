<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $positions = [
            ['name' => 'Junior Software Engineer',   'level' => 1],
            ['name' => 'Software Engineer',           'level' => 2],
            ['name' => 'Senior Software Engineer',    'level' => 3],
            ['name' => 'Lead Engineer',               'level' => 4],
            ['name' => 'Engineering Manager',         'level' => 5],
            ['name' => 'Junior HR Specialist',        'level' => 1],
            ['name' => 'HR Specialist',               'level' => 2],
            ['name' => 'Senior HR Specialist',        'level' => 3],
            ['name' => 'HR Manager',                  'level' => 4],
            ['name' => 'HR Director',                 'level' => 5],
            ['name' => 'Junior Financial Analyst',    'level' => 1],
            ['name' => 'Financial Analyst',           'level' => 2],
            ['name' => 'Senior Financial Analyst',    'level' => 3],
            ['name' => 'Finance Manager',             'level' => 4],
            ['name' => 'CFO',                         'level' => 5],
            ['name' => 'Marketing Coordinator',       'level' => 1],
            ['name' => 'Marketing Specialist',        'level' => 2],
            ['name' => 'Senior Marketing Specialist', 'level' => 3],
            ['name' => 'Marketing Manager',           'level' => 4],
            ['name' => 'CMO',                         'level' => 5],
        ];

        $pick = $this->faker->unique()->randomElement($positions);

        return [
            'name'        => $pick['name'],
            'level'       => $pick['level'],
            'description' => $this->faker->sentence(10),
        ];
    }

    /**
     * State: entry-level position (level 1).
     */
    public function entryLevel(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 1,
        ]);
    }

    /**
     * State: management-level position (level 4+).
     */
    public function management(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $this->faker->numberBetween(4, 5),
        ]);
    }
}
