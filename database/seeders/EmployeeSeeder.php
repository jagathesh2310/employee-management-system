<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;

/**
 * Seeds 200 employees with realistic data and manager relationships.
 *
 * LARAVEL FEATURE:
 *   - Uses Model factories with state methods
 *   - Demonstrates seeding with relationships (managers assigned after initial batch)
 *   - Uses chunk() pattern to assign managers efficiently
 *
 * SEEDING STRATEGY:
 *   1. Create 10 senior managers (one per department)
 *   2. Create 190 regular employees assigned to those managers
 *   This ensures a realistic hierarchy without circular references.
 */
class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all();
        $positions   = Position::all();

        // -----------------------------------------------------------------
        // Step 1: Create senior managers (level 4+ positions) – 1 per dept
        // -----------------------------------------------------------------
        $managers = [];

        foreach ($departments as $department) {
            // Pick a management-level position
            $managerPosition = $positions->where('level', '>=', 4)->random();

            $manager = Employee::factory()->create([
                'department_id' => $department->id,
                'position_id'   => $managerPosition->id,
                'status'        => EmployeeStatus::Active,
                'manager_id'    => null, // Top-level managers have no manager
                'salary'        => fake()->randomFloat(2, 80000, 200000),
            ]);

            $managers[$department->id] = $manager;
        }

        // -----------------------------------------------------------------
        // Step 2: Create 190 regular employees distributed across departments
        // -----------------------------------------------------------------
        // Using chunk creation for better seeding performance
        $regularCount = 190;

        Employee::factory()
            ->count($regularCount)
            ->make()
            ->each(function (Employee $employee) use ($departments, $positions, $managers) {
                // Assign to a random department
                $department = $departments->random();
                $position   = $positions->where('level', '<=', 3)->random();
                $manager    = $managers[$department->id];

                $employee->department_id = $department->id;
                $employee->position_id   = $position->id;
                $employee->manager_id    = $manager->id;
                $employee->save();
            });
    }
}
