<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds system users for API authentication via Sanctum.
 *
 * USERS CREATED:
 *   - admin@ems.local (role: admin) – full access
 *   - manager@ems.local (role: manager) – can approve/reject leave
 *   - employee@ems.local (role: employee) – limited access
 *
 * LARAVEL FEATURE:
 *   Demonstrates Sanctum token creation for test purposes.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user – full system access
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Manager user – can approve/reject leave requests
        User::firstOrCreate(
            ['email' => 'manager@ems.local'],
            [
                'name' => 'Department Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

        // Regular employee user
        User::firstOrCreate(
            ['email' => 'employee@ems.local'],
            [
                'name' => 'Regular Employee',
                'password' => Hash::make('password'),
                'role' => 'employee',
            ]
        );

        $this->command->info('System users seeded. Admin: admin@ems.local / password');
    }
}
