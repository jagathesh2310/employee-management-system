<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Models\AiApprovalProposal;
use App\Services\AiDemoService;
use App\Services\FaqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AiApprovalController – human-in-the-loop: AI proposes, human approves or rejects.
 *
 * Teaching point: AI decides what to do — a human decides whether it happens.
 */
class AiApprovalController extends Controller
{
    public function __construct(
        private readonly AiDemoService $aiService,
        private readonly FaqService $faqService,
    ) {}

    public function show(): View
    {
        return view('demo.ai.approval');
    }

    public function propose(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:200'],
        ]);

        try {
            $response = $this->aiService->proposeApproval($validated['topic']);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'AI proposal failed: '.$e->getMessage(),
            ], 422);
        }

        // Attempt to parse JSON from AI response
        $text = trim($response->text);
        $proposed = null;

        // Extract JSON block if it's wrapped in markdown
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) {
            $text = $m[1];
        }

        try {
            $proposed = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            // If JSON parse fails, structure it manually
            $proposed = [
                'question' => "HR Policy: {$validated['topic']}",
                'answer' => $text,
            ];
        }

        $proposal = AiApprovalProposal::create([
            'topic' => $validated['topic'],
            'payload' => $proposed,
            'status' => 'pending',
            'proposed_by' => $request->user()->id,
        ]);

        return response()->json([
            'proposal_id' => $proposal->id,
            'proposed' => $proposed,
        ]);
    }

    public function approve(Request $request, AiApprovalProposal $aiApprovalProposal): JsonResponse
    {
        if (! $aiApprovalProposal->status->isPending()) {
            return response()->json(['error' => 'Proposal already resolved.'], 422);
        }

        $faq = $this->faqService->create([
            'question' => $aiApprovalProposal->payload['question'],
            'answer' => $aiApprovalProposal->payload['answer'],
            'is_active' => true,
        ]);

        $aiApprovalProposal->update([
            'status' => 'approved',
            'result' => "FAQ #{$faq->id} created.",
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Approved! FAQ has been created.',
            'faq_id' => $faq->id,
            'faq_question' => $faq->question,
        ]);
    }

    public function reject(Request $request, AiApprovalProposal $aiApprovalProposal): JsonResponse
    {
        if (! $aiApprovalProposal->status->isPending()) {
            return response()->json(['error' => 'Proposal already resolved.'], 422);
        }

        $aiApprovalProposal->update([
            'status' => 'rejected',
            'result' => 'Rejected by user.',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Rejected. No FAQ was created.',
        ]);
    }
}
