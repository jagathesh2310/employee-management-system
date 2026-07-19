<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Master seeder – orchestrates all seeders in dependency order.
 *
 * LARAVEL FEATURE:
 *   DatabaseSeeder::call() runs seeders in sequence.
 *   Order matters: Departments → Positions → Users → Employees → Leave Requests
 *   (each seeder depends on the previous one's data).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,      // 1. Users (needed by LeaveRequestSeeder for approver)
            DepartmentSeeder::class,     // 2. Departments
            PositionSeeder::class,       // 3. Positions
            EmployeeSeeder::class,       // 4. Employees (need departments & positions)
            LeaveRequestSeeder::class,   // 5. Leave Requests (need employees & users)
            FaqSeeder::class,
        ]);
    }
}
