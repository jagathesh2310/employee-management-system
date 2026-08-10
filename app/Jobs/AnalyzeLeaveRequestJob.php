<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Ai\Agents\LeaveAnalysisAgent;
use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\StructuredAgentResponse;

/**
 * AnalyzeLeaveRequestJob – queued AI analysis for the queue demo.
 *
 * Demonstrates: Running AI workloads in background queues.
 * Status is stored in Cache and polled by the UI.
 * Used in: /demo/ai/queue.
 */
class AnalyzeLeaveRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    public function __construct(
        public readonly LeaveRequest $leaveRequest,
        public readonly string $cacheKey,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Cache::put($this->cacheKey, [
            'status' => 'processing',
            'started_at' => now()->toIso8601String(),
        ], 600);

        $lr = LeaveRequest::with('employee')->find($this->leaveRequest->id);

        if ($lr === null) {
            Cache::put($this->cacheKey, [
                'status' => 'failed',
                'error' => 'Leave request not found.',
            ], 600);

            return;
        }

        $prompt = sprintf(
            "Analyze this leave request:\n\nEmployee: %s\nLeave Type: %s\nDates: %s to %s (%d days)\nReason: %s",
            $lr->employee?->full_name ?? 'Unknown',
            $lr->leave_type->value,
            $lr->start_date->format('M d, Y'),
            $lr->end_date->format('M d, Y'),
            $lr->duration_in_days,
            $lr->reason ?? 'No reason provided',
        );

        /** @var StructuredAgentResponse $response */
        $response = LeaveAnalysisAgent::make()->prompt($prompt, provider: Lab::Gemini);

        $structured = $response->toArray();

        $lr->update([
            'ai_analysis' => $structured,
            'ai_analysis_status' => 'completed',
        ]);

        Cache::put($this->cacheKey, [
            'status' => 'done',
            'result' => $structured,
            'completed_at' => now()->toIso8601String(),
        ], 600);

        Log::info('Leave request AI analysis completed', [
            'leave_request_id' => $lr->id,
            'recommended_action' => $structured['recommended_action'] ?? 'N/A',
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Cache::put($this->cacheKey, [
            'status' => 'failed',
            'error' => $exception->getMessage(),
        ], 600);

        Log::error('Leave request AI analysis failed', [
            'leave_request_id' => $this->leaveRequest->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
