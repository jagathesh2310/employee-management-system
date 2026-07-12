<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * WHY THIS EXISTS:
 *   EmployeeFactory generates realistic employee data for seeding and tests.
 *   States allow tests to get specific scenarios (e.g., resigned employee).
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - Factory states (active, inactive, resigned, withManager)
 *   - Lazy evaluation via closures in definition()
 *   - Relationships in factories (belongsTo via recycle or for())
 *   - Using enum values as factory attributes
 *
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    private static int $employeeIdCounter = 1;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement(['male', 'female']);

        return [
            // Unique business identifier – padded to 3 digits (EMP-001 ... EMP-999)
            'employee_id'   => 'EMP-' . str_pad((string) static::$employeeIdCounter++, 3, '0', STR_PAD_LEFT),
            'first_name'    => $gender === 'male'
                ? $this->faker->firstNameMale()
                : $this->faker->firstNameFemale(),
            'last_name'     => $this->faker->lastName(),
            'email'         => $this->faker->unique()->safeEmail(),
            'phone'         => $this->faker->phoneNumber(),
            'gender'        => $gender,
            'date_of_birth' => $this->faker->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
            'joining_date'  => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'salary'        => $this->faker->randomFloat(2, 30000, 200000),
            'status'        => EmployeeStatus::Active,
            'department_id' => Department::factory(),
            'position_id'   => Position::factory(),
            'manager_id'    => null,
        ];
    }

    // -----------------------------------------------------------------------
    // States – enable scenario-based test data creation
    // -----------------------------------------------------------------------

    /**
     * Active employee (the default state).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatus::Active,
        ]);
    }

    /**
     * Inactive employee – on leave of absence, etc.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatus::Inactive,
        ]);
    }

    /**
     * Resigned employee.
     */
    public function resigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatus::Resigned,
        ]);
    }

    /**
     * Assign a manager to this employee.
     *
     * Usage: Employee::factory()->withManager($manager)->create()
     */
    public function withManager(Employee $manager): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id'    => $manager->id,
            'department_id' => $manager->department_id,
        ]);
    }

    /**
     * High-salary employee (useful for salary filter tests).
     */
    public function highSalary(): static
    {
        return $this->state(fn (array $attributes) => [
            'salary' => $this->faker->randomFloat(2, 150000, 500000),
        ]);
    }
}
