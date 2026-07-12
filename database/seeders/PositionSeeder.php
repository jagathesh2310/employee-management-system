<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

/**
 * Seeds 20 positions spanning 5 levels across multiple domains.
 */
class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            // Engineering track
            ['name' => 'Junior Software Engineer',   'level' => 1, 'description' => 'Entry-level software development role.'],
            ['name' => 'Software Engineer',           'level' => 2, 'description' => 'Mid-level development with independent ownership.'],
            ['name' => 'Senior Software Engineer',    'level' => 3, 'description' => 'Technical leadership and complex problem solving.'],
            ['name' => 'Engineering Lead',            'level' => 4, 'description' => 'Leads engineering squads and technical direction.'],
            ['name' => 'Engineering Manager',         'level' => 5, 'description' => 'Manages multiple engineering teams and roadmap.'],
            // HR track
            ['name' => 'HR Coordinator',             'level' => 1, 'description' => 'Administrative HR support and onboarding.'],
            ['name' => 'HR Specialist',              'level' => 2, 'description' => 'Employee relations and HR policy execution.'],
            ['name' => 'Senior HR Specialist',       'level' => 3, 'description' => 'Strategic HR initiatives and compliance.'],
            ['name' => 'HR Manager',                 'level' => 4, 'description' => 'Manages HR team and policies.'],
            ['name' => 'HR Director',                'level' => 5, 'description' => 'Overall HR strategy and C-level advisory.'],
            // Finance track
            ['name' => 'Junior Financial Analyst',   'level' => 1, 'description' => 'Data collection and financial modelling support.'],
            ['name' => 'Financial Analyst',          'level' => 2, 'description' => 'Independent financial analysis and reporting.'],
            ['name' => 'Senior Financial Analyst',   'level' => 3, 'description' => 'Complex financial modelling and forecasting.'],
            ['name' => 'Finance Manager',            'level' => 4, 'description' => 'Manages finance team and budgets.'],
            ['name' => 'Chief Financial Officer',    'level' => 5, 'description' => 'Executive financial strategy and oversight.'],
            // Product track
            ['name' => 'Associate Product Manager',  'level' => 1, 'description' => 'Supports product roadmap and feature delivery.'],
            ['name' => 'Product Manager',            'level' => 2, 'description' => 'Owns product features and backlog.'],
            ['name' => 'Senior Product Manager',     'level' => 3, 'description' => 'Leads cross-functional product initiatives.'],
            ['name' => 'Director of Product',        'level' => 4, 'description' => 'Product portfolio strategy.'],
            ['name' => 'Chief Product Officer',      'level' => 5, 'description' => 'Executive product vision and strategy.'],
        ];

        foreach ($positions as $data) {
            Position::create($data);
        }
    }
}
