<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\ApprovalAgent;
use App\Ai\Agents\ChatAgent;
use App\Ai\Agents\HrAssistant;
use App\Ai\Agents\LeaveAnalysisAgent;
use App\Ai\Agents\SummarizeAgent;
use App\Models\LeaveRequest;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Laravel\Ai\Responses\StructuredAgentResponse;

/**
 * AiDemoService – orchestration layer for the AI SDK demo pages.
 *
 * Controllers delegate to this service; no AI logic lives in controllers.
 */
class AiDemoService
{
    /**
     * Prompt the HrAssistant with no tools (basic agent demo).
     */
    public function promptAgent(string $prompt): AgentResponse
    {
        return HrAssistant::make()->prompt($prompt, provider: Lab::Gemini);
    }

    /**
     * Prompt the HrAssistant with tools enabled (tool trace demo).
     */
    public function promptWithTools(string $prompt): AgentResponse
    {
        return HrAssistant::make()->prompt($prompt, provider: Lab::Gemini);
    }

    /**
     * Prompt LeaveAnalysisAgent for structured output.
     */
    public function promptStructured(string $prompt): StructuredAgentResponse
    {
        /** @var StructuredAgentResponse */
        return LeaveAnalysisAgent::make()->prompt($prompt, provider: Lab::Gemini);
    }

    /**
     * Prompt the HrAssistant and return a streamable SSE response.
     */
    public function promptStream(string $prompt): StreamableAgentResponse
    {
        return HrAssistant::make()->stream($prompt, provider: Lab::Gemini);
    }

    /**
     * Send a message in an ongoing chat conversation.
     *
     * @param  object  $user  The authenticated user
     */
    public function chatMessage(string $message, object $user, ?string $conversationId = null): AgentResponse
    {
        $agent = ChatAgent::make();

        if ($conversationId !== null) {
            $agent->continue($conversationId, $user);
        } else {
            $agent->forUser($user);
        }

        return $agent->prompt($message, provider: Lab::Gemini);
    }

    /**
     * Summarize a block of text.
     */
    public function summarize(string $text): AgentResponse
    {
        return SummarizeAgent::make()->prompt(
            "Please summarize the following text:\n\n{$text}",
            provider: Lab::Gemini
        );
    }

    /**
     * Have the ApprovalAgent propose a FAQ entry on a given topic.
     */
    public function proposeApproval(string $topic): AgentResponse
    {
        return ApprovalAgent::make()->prompt(
            "Draft a professional HR FAQ entry about: {$topic}",//[233,]
            provider: Lab::Gemini
        );
    }

    /**
     * Build a prompt for leave request analysis.
     */
    public function buildLeaveRequestPrompt(LeaveRequest $leaveRequest): string
    {
        return sprintf(
            "Analyze this leave request:\n\nEmployee: %s\nLeave Type: %s\nDates: %s to %s (%d days)\nReason: %s",
            $leaveRequest->employee?->full_name ?? 'Unknown',
            $leaveRequest->leave_type->value,
            $leaveRequest->start_date->format('M d, Y'),
            $leaveRequest->end_date->format('M d, Y'),
            $leaveRequest->duration_in_days,
            $leaveRequest->reason ?? 'No reason provided',
        );
    }
}
