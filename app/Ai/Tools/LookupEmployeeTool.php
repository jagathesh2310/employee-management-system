<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Employee;
use App\Models\Scopes\ActiveEmployeeScope;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * LookupEmployeeTool – looks up an employee by email address.
 *
 * Demonstrates: Read-only tools that query the database.
 * Used in: /demo/ai/tools.
 */
class LookupEmployeeTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Look up an employee by their email address and return their profile details including name, department, position, salary, and status.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $email = strtolower(trim((string) $request['email']));

        $employee = Employee::withoutGlobalScope(ActiveEmployeeScope::class)
            ->where('email', $email)
            ->with(['department', 'position'])
            ->first();

        if ($employee === null) {
            return "No employee found with email: {$email}";
        }

        return sprintf(
            'Employee: %s | Email: %s | Department: %s | Position: %s | Status: %s | Joining Date: %s',
            $employee->full_name,
            $employee->email,
            $employee->department?->name ?? 'N/A',
            $employee->position?->title ?? 'N/A',
            $employee->status->label(),
            $employee->joining_date->format('M d, Y'),
        );
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'email' => $schema->string()
                ->description('The email address of the employee to look up.')
                ->required(),
        ];
    }
}
