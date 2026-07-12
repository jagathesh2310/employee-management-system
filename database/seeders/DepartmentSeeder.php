<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

/**
 * Seeds 10 realistic departments.
 *
 * LARAVEL FEATURE: Seeders call factories, keeping seed data
 * maintainable and realistic without hardcoded SQL inserts.
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Engineering',       'code' => 'ENG',  'description' => 'Software development, architecture, and infrastructure.'],
            ['name' => 'Human Resources',   'code' => 'HR',   'description' => 'Recruitment, onboarding, and employee welfare.'],
            ['name' => 'Finance',           'code' => 'FIN',  'description' => 'Accounting, budgeting, and financial planning.'],
            ['name' => 'Marketing',         'code' => 'MKT',  'description' => 'Brand management, campaigns, and growth.'],
            ['name' => 'Operations',        'code' => 'OPS',  'description' => 'Day-to-day business operations and logistics.'],
            ['name' => 'Sales',             'code' => 'SLS',  'description' => 'Revenue generation and client acquisition.'],
            ['name' => 'Legal',             'code' => 'LGL',  'description' => 'Contracts, compliance, and risk management.'],
            ['name' => 'Product',           'code' => 'PRD',  'description' => 'Product strategy, roadmap, and management.'],
            ['name' => 'Design',            'code' => 'DSN',  'description' => 'UI/UX design and brand identity.'],
            ['name' => 'Customer Success',  'code' => 'CS',   'description' => 'Customer onboarding, support, and retention.'],
        ];

        foreach ($departments as $data) {
            Department::create($data);
        }
    }
}
