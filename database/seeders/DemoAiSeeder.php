<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Faq;
use App\Models\LeaveRequest;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DemoAiSeeder – idempotent seeder for AI SDK demo data.
 *
 * Safe to run multiple times. Checks for existing demo data before inserting.
 * Run once before a demo session: php artisan db:seed --class=DemoAiSeeder
 */
class DemoAiSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFaqs();
        $this->seedEmployeesAndLeaveRequests();

        $this->command->info('✓ DemoAiSeeder complete. Demo data is ready.');
    }

    private function seedFaqs(): void
    {
        // Check if demo FAQs already exist
        if (Faq::where('question', 'like', '%maternity leave%')->exists()) {
            $this->command->info('  Skipping FAQs – already seeded.');

            return;
        }

        $faqs = [
            [
                'question' => 'How many days of maternity leave are employees entitled to?',
                'answer' => 'Full-time female employees are entitled to 26 weeks (182 days) of paid maternity leave, provided they have completed at least 80 days of service in the 12 months preceding the expected date of delivery. Adoption leave of 12 weeks is also available for employees adopting a child under 3 months of age.',
                'is_active' => true,
            ],
            [
                'question' => 'What is the annual leave (paid time off) entitlement for employees?',
                'answer' => 'Employees are entitled to 18 working days of paid annual leave per year, which accrues monthly at 1.5 days per month. Employees who have completed 5 or more years of service receive 21 days. Leave must be approved by the direct manager at least 2 weeks in advance, except for emergencies.',
                'is_active' => true,
            ],
            [
                'question' => 'Can unused annual leave be carried over to the next year?',
                'answer' => 'Yes, up to a maximum of 5 unused annual leave days can be carried over to the following calendar year. Any carry-over days must be used by March 31st of the following year, after which they expire. Employees may not carry over more than 5 days without written approval from HR.',
                'is_active' => true,
            ],
            [
                'question' => 'How many sick leave days are employees allowed per year?',
                'answer' => 'Employees are entitled to 12 days of paid sick leave per year. A medical certificate is required for absences exceeding 3 consecutive days. Sick leave does not accrue and unused days do not carry over. Employees on probation are entitled to 6 days of sick leave during the probation period.',
                'is_active' => true,
            ],
            [
                'question' => 'What is the policy for working from home or remote work?',
                'answer' => 'Employees may work remotely up to 2 days per week subject to manager approval. Remote work eligibility requires 6 months of service and a satisfactory performance rating. A dedicated workspace with reliable internet is required. Remote work arrangements must be reviewed quarterly and can be revoked with 2 weeks notice.',
                'is_active' => true,
            ],
            [
                'question' => 'What happens if an employee is repeatedly late or absent without notice?',
                'answer' => 'Repeated lateness or unauthorized absences are subject to the progressive disciplinary procedure: first, an informal verbal warning; second, a formal written warning; third, a final written warning with a Performance Improvement Plan; fourth, termination if no improvement is observed. All incidents must be documented by the line manager.',
                'is_active' => true,
            ],
            [
                'question' => 'Are employees entitled to paternity leave when their child is born?',
                'answer' => 'Male employees are entitled to 5 days of paid paternity leave, which must be taken within 3 months of the birth or adoption. Paternity leave must be registered with HR at least 2 weeks before the expected date. An additional 5 days of unpaid leave may be requested with manager approval.',
                'is_active' => true,
            ],
            [
                'question' => 'What is the process for requesting a salary advance or loan?',
                'answer' => 'Employees who have completed 12 months of service may apply for a salary advance of up to 2 months\' gross salary. Applications must be submitted to HR with a reason statement. Repayment is deducted in equal monthly installments over a maximum of 6 months. Only one advance may be outstanding at a time.',
                'is_active' => true,
            ],
            [
                'question' => 'How are performance reviews conducted and how often?',
                'answer' => 'Performance reviews are conducted twice a year: mid-year in June and end-of-year in December. Reviews involve self-assessment, manager assessment, and a 1:1 discussion. Ratings range from 1 (below expectations) to 5 (outstanding). Reviews directly influence annual salary increments and promotion eligibility.',
                'is_active' => true,
            ],
            [
                'question' => 'What are the rules around overtime pay for extra hours worked?',
                'answer' => 'Overtime must be pre-approved by the line manager in writing. Employees in non-managerial roles receive 1.5x their hourly rate for overtime on weekdays and 2x on weekends and public holidays. Managerial employees are eligible for compensatory time off in lieu of overtime pay. Overtime cannot exceed 10 hours per week without HR approval.',
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }

        $this->command->info('  ✓ Seeded '.count($faqs).' demo FAQs (embeddings will be generated by queue).');
    }

    private function seedEmployeesAndLeaveRequests(): void
    {
        // Use existing department and position if available, or create minimal ones
        $department = Department::first();
        $position = Position::first();

        if (! $department || ! $position) {
            $this->command->info('  Skipping employees – no department/position found. Run main seeders first.');

            return;
        }

        // Check if demo employees already exist
        if (Employee::withoutGlobalScopes()->where('employee_id', 'DEMO-001')->exists()) {
            $this->command->info('  Skipping employees – already seeded.');

            return;
        }

        $employees = [
            [
                'employee_id' => 'DEMO-001',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'alice.johnson@demo.example.com',
                'joining_date' => now()->subYears(3),
                'salary' => 75000,
                'status' => 'active',
                'department_id' => $department->id,
                'position_id' => $position->id,
            ],
            [
                'employee_id' => 'DEMO-002',
                'first_name' => 'Bob',
                'last_name' => 'Martinez',
                'email' => 'bob.martinez@demo.example.com',
                'joining_date' => now()->subMonths(8),
                'salary' => 62000,
                'status' => 'active',
                'department_id' => $department->id,
                'position_id' => $position->id,
            ],
            [
                'employee_id' => 'DEMO-003',
                'first_name' => 'Clara',
                'last_name' => 'Patel',
                'email' => 'clara.patel@demo.example.com',
                'joining_date' => now()->subYears(5),
                'salary' => 90000,
                'status' => 'active',
                'department_id' => $department->id,
                'position_id' => $position->id,
            ],
        ];

        $leaveReasons = [
            'DEMO-001' => [
                'type' => LeaveType::Medical,
                'reason' => 'I have been diagnosed with a severe migraine disorder that is significantly impacting my daily functioning. My neurologist has prescribed complete rest away from screens and high-stress environments for at least 10 days. I have medical documentation available and have arranged for my colleague Sarah to cover my critical responsibilities during this period.',
                'days' => 10,
            ],
            'DEMO-002' => [
                'type' => LeaveType::Annual,
                'reason' => 'I would like to take my annual leave to attend my sister\'s wedding in Portugal. The wedding is a family milestone and requires international travel. I have completed all my current sprint deliverables ahead of schedule and have briefed the team on any ongoing tasks. My manager has informally agreed this is a good time for me to take leave.',
                'days' => 7,
            ],
            'DEMO-003' => [
                'type' => LeaveType::Maternity,
                'reason' => 'I am expecting my first child and my obstetrician has confirmed a due date 8 weeks from now. I am requesting to start my maternity leave 2 weeks before the due date to prepare, followed by the full 26-week entitlement. I have prepared a comprehensive handover document and have been training my replacement for the past month to ensure continuity.',
                'days' => 196,
            ],
        ];

        DB::transaction(function () use ($employees, $leaveReasons) {
            foreach ($employees as $empData) {
                $emp = Employee::create($empData);

                $lrData = $leaveReasons[$empData['employee_id']];
                LeaveRequest::create([
                    'employee_id' => $emp->id,
                    'leave_type' => $lrData['type'],
                    'start_date' => now()->addWeek(),
                    'end_date' => now()->addWeek()->addDays($lrData['days']),
                    'reason' => $lrData['reason'],
                    'status' => LeaveStatus::Pending,
                ]);
            }
        });

        $this->command->info('  ✓ Seeded 3 demo employees with pending leave requests.');
    }
}
