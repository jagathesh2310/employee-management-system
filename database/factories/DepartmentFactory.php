<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * WHY THIS EXISTS:
 *   Factories generate realistic fake data for testing and seeding.
 *   Laravel's Factory pattern allows creating model instances with
 *   sensible defaults that can be overridden per test case.
 *
 * LARAVEL FEATURE:
 *   Factory::definition() provides base attributes.
 *   States (e.g., ->forEngineering()) allow scenario-specific instances.
 *
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Using unique() ensures no two seeded departments share the same code.
        $name = $this->faker->unique()->randomElement([
            'Engineering', 'Human Resources', 'Finance', 'Marketing',
            'Operations', 'Sales', 'Legal', 'Product', 'Design', 'IT Support',
            'Customer Success', 'Data Analytics', 'Research', 'Compliance',
        ]);

        return [
            'name'        => $name,
            'code'        => strtoupper(substr(preg_replace('/[^A-Z]/', '', strtoupper($name)), 0, 4)),
            'description' => $this->faker->sentence(12),
        ];
    }
}
