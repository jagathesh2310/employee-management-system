<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\SearchFaqTool;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * ApprovalAgent – proposes FAQ drafts for human approval.
 *
 * Demonstrates: Human-in-the-loop where AI proposes, human decides.
 * Used in: /demo/ai/approval.
 */
class ApprovalAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the agent's system instructions.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        You are an HR knowledge base specialist. When given a topic, draft a clear,
        professional FAQ entry with:
        - A precise question that employees would actually ask
        - A comprehensive but concise answer (3-5 sentences)
        Return ONLY valid JSON in this exact format, no other text:
        {"question": "...", "answer": "..."}
        The FAQ should be accurate, helpful, and aligned with standard HR policies.
        INSTRUCTIONS;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to this agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new SearchFaqTool,
        ];
    }
}
