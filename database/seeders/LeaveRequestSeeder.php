<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds 500 leave requests spread across employees.
 *
 * LARAVEL FEATURE:
 *   Demonstrates using LazyCollection (via cursor()) when working with
 *   large employee datasets to avoid loading all 200 employees into memory at once.
 */
class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Load employees using cursor() – demonstrates LazyCollection usage.
        // WHY cursor(): When iterating 200 employees just to assign leave requests,
        // cursor() fetches one row at a time instead of loading all 200 at once.
        $admin = User::where('role', 'admin')->first();

        // Distribute 500 leave requests across all employees
        $targetCount = 500;
        $created = 0;

        // Use cursor() for memory-efficient iteration
        Employee::withoutGlobalScopes()->cursor()->each(function (Employee $employee) use ($admin, $targetCount, &$created) {
            if ($created >= $targetCount) {
                return false; // Stop iteration
            }

            // Each employee gets 2-3 leave requests
            $count = min(fake()->numberBetween(2, 3), $targetCount - $created);

            for ($i = 0; $i < $count; $i++) {
                $startDate = fake()->dateTimeBetween('-2 years', '+2 months');
                $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 10).' days');

                $status = fake()->randomElement([
                    LeaveStatus::Pending,
                    LeaveStatus::Approved,
                    LeaveStatus::Rejected,
                ]);

                LeaveRequest::create([
                    'employee_id' => $employee->id,
                    'leave_type' => fake()->randomElement(['casual', 'sick', 'earned']),
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'reason' => fake()->sentence(8),
                    'status' => $status,
                    'approved_by' => in_array($status, [LeaveStatus::Approved, LeaveStatus::Rejected]) ? $admin?->id : null,
                    'approved_at' => in_array($status, [LeaveStatus::Approved, LeaveStatus::Rejected]) ? now() : null,
                ]);

                $created++;
            }
        });

        $this->command->info("Seeded {$created} leave requests.");
    }
}
