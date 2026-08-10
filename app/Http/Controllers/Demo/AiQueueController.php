<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeLeaveRequestJob;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * AiQueueController – demonstrates queued AI jobs with on-page status polling.
 *
 * Teaching point: Long AI tasks run in the background; UI polls for completion.
 */
class AiQueueController extends Controller
{
    public function show(): View
    {
        $leaveRequests = LeaveRequest::pending()
            ->with('employee')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn (LeaveRequest $lr) => [
                'id' => $lr->id,
                'label' => sprintf(
                    '%s — %s (%s to %s)',
                    $lr->employee?->full_name ?? 'Unknown',
                    $lr->leave_type->value,
                    $lr->start_date->format('M d'),
                    $lr->end_date->format('M d, Y'),
                ),
                'reason' => $lr->reason,
                'has_analysis' => $lr->ai_analysis !== null,
                'ai_analysis' => $lr->ai_analysis,
            ]);

        return view('demo.ai.queue', compact('leaveRequests'));
    }

    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leave_request_id' => ['required', 'string', 'exists:leave_requests,id'],
        ]);

        $leaveRequest = LeaveRequest::with('employee')->findOrFail($validated['leave_request_id']);

        $cacheKey = 'leave_analysis_'.$leaveRequest->id.'_'.time();

        Cache::put($cacheKey, [
            'status' => 'queued',
            'queued_at' => now()->toIso8601String(),
        ], 600);

        AnalyzeLeaveRequestJob::dispatch($leaveRequest, $cacheKey)->onConnection('sync');

        return response()->json([
            'cache_key' => $cacheKey,
            'message' => 'Analysis queued successfully.',
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cache_key' => ['required', 'string'],
        ]);

        $data = Cache::get($validated['cache_key']);

        if ($data === null) {
            return response()->json(['status' => 'not_found']);
        }

        return response()->json($data);
    }
}
