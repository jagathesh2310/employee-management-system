<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\EmployeeRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * GenerateEmployeeReportJob – generates a CSV report of all employees.
 *
 * WHY THIS EXISTS:
 *   Report generation over 200 employees can take seconds or minutes.
 *   Running it in a queue worker keeps the API responsive.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - chunkById() via EmployeeRepository::chunkForReport() for memory-safe iteration
 *   - LazyCollection via PHP Generator pattern
 *   - Storage facade for writing the report file
 *   - Service Container injection via constructor (EmployeeRepositoryInterface)
 *
 * BEST PRACTICE:
 *   Use chunkById() instead of get() to avoid loading 200+ employee models
 *   into memory at once. Each chunk releases memory after processing.
 */
class GenerateEmployeeReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1; // Reports shouldn't be retried – they're idempotent but expensive

    public int $timeout = 300; // 5 minutes max

    public function __construct()
    {
        $this->onQueue('default');
    }

    /**
     * Execute the report generation.
     *
     * WHY DEPENDENCY INJECTION IN HANDLE():
     *   Laravel's Job handle() method supports automatic dependency injection
     *   from the service container. We inject the repository here (not in the
     *   constructor) because the repository is resolved fresh in the worker context.
     */
    public function handle(EmployeeRepositoryInterface $employeeRepository): void
    {
        $filename = 'reports/employees_'.now()->format('Y-m-d_H-i-s').'.csv';
        $totalRows = 0;

        // Open a stream to write CSV directly to storage (avoids building a huge string)
        $stream = fopen('php://temp', 'r+');

        // Write CSV header
        fputcsv($stream, [
            'Employee ID', 'Name', 'Email', 'Department', 'Position', 'Joining Date', 'Salary', 'Status',
        ]);

        // chunkForReport() uses chunkById() internally – processes 100 employees at a time
        // WHY 100 chunks: balances query count (200 employees / 100 = 2 queries) vs memory
        $employeeRepository->chunkForReport(100, function ($employees) use ($stream, &$totalRows) {
            foreach ($employees as $employee) {
                fputcsv($stream, [
                    $employee->employee_id,
                    $employee->full_name,
                    $employee->email,
                    $employee->department?->name ?? 'N/A',
                    $employee->position?->name ?? 'N/A',
                    $employee->joining_date->format('Y-m-d'),
                    $employee->salary,
                    $employee->status->label(),
                ]);
                $totalRows++;
            }
        });

        // Write stream to storage
        rewind($stream);
        Storage::put($filename, stream_get_contents($stream));
        fclose($stream);

        Log::info("Employee report generated: {$filename} ({$totalRows} rows)");
    }
}
