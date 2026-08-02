<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\LeaveRequest;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * ListPendingLeaveRequestsTool – lists pending leave requests.
 *
 * Demonstrates: Read-only tools that query relational data.
 * Used in: /demo/ai/tools.
 */
class ListPendingLeaveRequestsTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List pending (not yet approved or rejected) leave requests. Returns employee name, leave type, dates, duration, and reason for each request.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $limit = min((int) ($request['limit'] ?? 5), 20);

        $requests = LeaveRequest::pending()
            ->with('employee')
            ->orderBy('start_date')
            ->limit($limit)
            ->get();

        if ($requests->isEmpty()) {
            return 'No pending leave requests found.';
        }

        $lines = $requests->map(function (LeaveRequest $lr) {
            return sprintf(
                '• %s — %s (%s to %s, %d day(s)): %s',
                $lr->employee?->full_name ?? 'Unknown',
                $lr->leave_type->value,
                $lr->start_date->format('M d'),
                $lr->end_date->format('M d, Y'),
                $lr->duration_in_days,
                $lr->reason ?? 'No reason provided',
            );
        });

        return "Pending leave requests ({$requests->count()}):\n".$lines->implode("\n");
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()
                ->min(1)
                ->max(20)
                ->default(5)
                ->description('Maximum number of pending leave requests to return.'),
        ];
    }
}
